import os
import time

import httpx

from core.constants import constants


def handle_status_code_container(status, redis_client, key, container, data: dict | None):
    match status:
        case 422:
            redis_client.json().numincrby(key, ".attempts", number=1)
            if redis_client.json().get(key, ".attempts") == 3:
                print("Httpx: Error 422 Unprocessable Entity")
                print(f"Httpx: Data from {container['type']} process ID: {container['experiment_id']} \n"
                      f"was unsuccessfully sent to the web app: {data}")
                redis_client.delete(key)

            time.sleep(30)
        case 200 | 201 | 202:
            if container['type'] == "predictions":
                key_suffix = key.decode("utf-8").split(":")[-1]
                file = f"{constants.EXPERIMENTS_DIRECTORY}/{container['experiment_id']}/" \
                       f"prediction_output_{key_suffix}.json"
                os.remove(file)
                print(f"removed file: {file}")
            redis_client.delete(key)
        case _:
            print(f"Httpx: unhandled error {status} occurred, when sending data from {container['type']} \n"
                  f"process ID: {container['experiment_id']} to the web app: {data}")
            redis_client.delete(key)


def handle_status_code_queue(status, redis_client, job, job_id):
    match status:
        case 422:
            print("Httpx: Error 422 Unprocessable Entity")
            print(f"Httpx: Data from {job.meta['endpoint']} process ID: {job_id} \n"
                  f"was unsuccessfully sent to the web app")
            print(f"rq:{job.get_status()}:default")
            redis_client.zrem(f"rq:{job.get_status()}:default", job_id)
        case 200 | 201 | 202:
            redis_client.zrem(f"rq:{job.get_status()}:default", job_id)
        case _:
            print(f"Httpx: unhandled error {status} occurred, when sending data from {job.meta['endpoint']} \n"
                  f"process ID: {job_id} to the web app")
            redis_client.zrem(f"rq:{job.get_status()}:default", job_id)


def send_post_request(post_address,
                      redis_client,
                      key: str | None = None,
                      container: dict | None = None,
                      job: classmethod | None = None,
                      job_id: str | None = None,
                      data: dict | None = None):
    headers = None
    if data:
        headers = {
            "Accept": "application/json",
        }

    try:
        r = httpx.post(
            post_address,
            json=data,
            headers=headers
        )
    except (httpx.ConnectTimeout, httpx.WriteTimeout) as e:
        print(f"Httpx: {e}")
        time.sleep(5)
        return False
    except httpx.ConnectError as e:
        print(f"Httpx: {e}")
        print("Destination host will not accept connection")
        time.sleep(5)
        return False
    else:
        if key:
            handle_status_code_container(r.status_code, redis_client, key, container, data)
        elif job_id:
            handle_status_code_queue(r.status_code, redis_client, job, job_id)
