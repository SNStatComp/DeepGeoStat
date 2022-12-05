import time

from dependencies import *
from workers.post_request_handler import send_post_request


def handle_exit_code(exit_code: int, oom_killed: bool):
    match exit_code:
        case 0:
            return "finish", None
        case 137:
            if oom_killed:
                return "error", {oom_killed: True}
            else:
                return "finish", None
        case _:
            return "error", None


def get_container_status(event, redis_client, docker_client):
    while not event.is_set():
        for key in redis_client.scan_iter("post_request_backlog:containers:*"):
            container = redis_client.json().get(key, ".")
            endpoint, error_data = handle_exit_code(container['exit_code'], container['oom_killed'])
            if container['type'] == "experiments":
                post_address = (f"{settings.WEB_APP_ADDRESS}/api/{container['type']}"
                                f"/{container['experiment_id']}/{endpoint}")
            elif container['type'] == "predictions":
                post_address = (f"{settings.WEB_APP_ADDRESS}/api/{container['type']}"
                                f"/{container['redis_id']}/{endpoint}")

            if endpoint == "finish":
                if container['type'] == "experiments":
                    try:
                        data = load_experiments_json(container['experiment_id'])['model_statistics']
                    except FileNotFoundError:
                        print(f"experiment_output.json for container {container['experiment_id']} was not found")
                        data = None
                        post_address = (f"{settings.WEB_APP_ADDRESS}/api/{container['type']}"
                                        f"/{container['experiment_id']}/error")

                elif container['type'] == "predictions":
                    try:
                        data = load_predictions_json(container['experiment_id'], key.decode("utf-8").split(":")[-1])
                    except FileNotFoundError:
                        print(f"prediction_output.json for container {container['experiment_id']}, \n"
                              f"ID:{container['redis_id']} was not found")
                        data = None
                        post_address = (f"{settings.WEB_APP_ADDRESS}/api/{container['type']}"
                                        f"/{container['redis_id']}/error")

                send_post_request(post_address, redis_client, key=key, container=container, data=data)

            elif endpoint == "error":
                send_post_request(post_address, redis_client, key=key, container=container, data=error_data)

        for key in redis_client.scan_iter("running_containers:*"):
            container = redis_client.json().get(key, ".")

            # If container is already deleted, then delete entry in redis and skip this iteration
            try:
                status = docker_client.api.inspect_container(container['container_id'])['State']
            except docker.errors.NotFound as e:
                print(f"Docker: {e}")
                redis_client.delete(key)
                continue
            if status['Status'] == "exited":
                # If the container crashed for some reason, save the container log.
                if (status['ExitCode'] != 0 and status['ExitCode'] !=137 and status['ExitCode'] != 43) or (status['ExitCode'] == 137 and status['OOMKilled']):
                    log_dir = f"{constants.EXPERIMENTS_DIRECTORY}/{container['experiment_id']}/{container['type']}_crash_log.txt"
                    with open(log_dir, "wb") as file:
                        for line in docker_client.api.logs(container['container_id'], stream=True):
                            file.write(line)

                container['exit_code'] = status['ExitCode']
                container['oom_killed'] = status['OOMKilled']
                container['attempts'] = 0
                key_suffix = key.decode("utf-8").split(":")[-1]
                new_key = f"post_request_backlog:containers:{container['type']}:{key_suffix}"
                redis_client.json().set(new_key, ".", container)
                redis_client.expire(new_key, (60 * 60 * 24 * 7))

                docker_client.api.remove_container(container['container_id'])
                print(f"Container name: {container['experiment_id']}; ID:{container['container_id']} removed")
                redis_client.delete(key)

        time.sleep(5)
