from __future__ import annotations

import json
import uuid

import docker

from core.config import settings
from core.constants import constants


async def dict_key_exists(experiment_id: int, key: str):
    directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment_output.json"
    with open(directory, "r") as file:
        data = json.load(file)
        return key in data


def load_experiments_json(experiment_id: int):
    directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment_output.json"
    with open(directory, "r") as file:
        return json.load(file)


def load_predictions_json(experiment_id: int, uuid_key: str):
    directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/prediction_output_{uuid_key}.json"
    with open(directory, "r") as file:
        return json.load(file)


def get_available_processing_units(redis_client,
                                   container_type: str | None = None,
                                   experiment_id: int | None = None,
                                   command: list | None = None,
                                   redis_id: str | None = None,
                                   new_container: bool = True):
    gpu_id = []
    device_request = []
    environment = []
    cpu_usage = 0
    for gpu_id in settings.GPU_IDS:
        search_id = gpu_id.replace("-", " ")
        containers_using_gpu_id = redis_client.ft().search(search_id).total
        if int(containers_using_gpu_id) < int(settings.CONTAINER_PER_GPU):
            device_request = [docker.types.DeviceRequest(device_ids=[gpu_id], capabilities=[["gpu"]])]
            environment = [f"CONTAINER_PER_GPU={settings.CONTAINER_PER_GPU}"]
            break

    containers_using_cpu = redis_client.ft().search("@cpu_usage:[1 1]").total
    if int(containers_using_cpu) >= int(settings.CONTAINER_ON_CPU) and not device_request:
        if new_container:
            data = {
                "experiment_id": experiment_id,
                "command": command,
                "type": container_type,
                "redis_id": redis_id,
            }
            new_key = f"queued_containers:experiments:{uuid.uuid4()}"
            redis_client.json().set(new_key, ".", data)
            redis_client.expire(new_key, (60 * 60 * 24 * 7))
            return
        else:
            return
    elif not device_request:
        gpu_id = []
        cpu_usage = 1

    return [device_request, cpu_usage, environment, gpu_id]
