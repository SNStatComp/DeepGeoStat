import time
import uuid

from core.config import settings
from dependencies import get_available_processing_units


def handle_queued_containers(event, redis_client, docker_client, lock):
    while not event.is_set():
        for key in redis_client.scan_iter("queued_containers:*"):
            lock.acquire()
            print("Queue handler: lock acquired")
            container = redis_client.json().get(key, ".")

            output = get_available_processing_units(redis_client, new_container=False)
            if not output:
                lock.release()
                print("Queue handler: lock released")
                time.sleep(15)
                continue

            uuid_key = str(uuid.uuid4())
            if settings.EXPERIMENT_KWARGS and container['type'] == "experiments":
                output[2] = output[2] + settings.EXPERIMENT_KWARGS
            elif container['type'] == "predictions":
                output[2] = output[2] + [f"OUTPUT_FILE_PREFIX={uuid_key}"]

            volume = {
                "experiments": {
                    "bind": "/usr/deepgeostat-ai/experiments",
                    "mode": "rw"
                },
                f"{settings.IMAGE_DIRECTORY}": {
                    "bind": "/usr/deepgeostat-ai/images",
                    "mode": "rw"
                }
            }
            if settings.CERTIFICATE_DIRECTORY and container['type'] == "experiments":
                volume[settings.CERTIFICATE_DIRECTORY] = {
                    "bind": "/usr/local/share/ca-certificates",
                    "mode": "ro"
                }

            new_container = docker_client.containers.run(
                image=settings.DOCKER_DEEPGEOSTAT_AI_IMAGE,
                command=container['command'],
                detach=True,
                device_requests=output[0],
                environment=output[2],
                name=f"{container['type']}_{container['experiment_id']}_{uuid_key}",
                volumes=volume
            )

            data = {
                "container_id": new_container.id,
                "experiment_id": container['experiment_id'],
                "redis_id": container['redis_id'],
                "type": container['type'],
                "gpu_id": output[3],
                "cpu_usage": output[1]
            }

            new_key = f"running_containers:experiments:{uuid_key}"
            redis_client.json().set(new_key, ".", data)
            redis_client.expire(new_key, (60 * 60 * 24 * 7))
            redis_client.delete(key)

            lock.release()
            print("Queue handler: lock released")
            time.sleep(15)
