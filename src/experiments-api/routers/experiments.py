import pathlib
import shutil
import uuid

import numpy as np
import pandas as pd
from fastapi import APIRouter
from fastapi.responses import FileResponse, HTMLResponse, JSONResponse
from jinja2 import Template
from starlette.requests import Request

from core.config import settings
from core.constants import constants
from dependencies import dict_key_exists, load_experiments_json, get_available_processing_units
from schemas.experiment_schema import ExperimentSchema
from schemas.path_params import LearningCurveType
from schemas.prediction_schema import PredictionSchema

router = APIRouter(
    prefix="/experiments",
    tags=["experiments"],
    responses={404: {"description": "Not found"}},
)


@router.post("/", status_code=201)
async def create_experiment(experiment_schema: ExperimentSchema):
    experiment_directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_schema.information.experiment['id']}"
    pathlib.Path(experiment_directory).mkdir(parents=True, exist_ok=True)

    template_file_name = None
    if experiment_schema.information.experiment["detection_type"] == 2:
        template_file_name = "change_detection_experiment"
    else:
        template_file_name = "classification_experiment"
    with open(f"{constants.PYTHON_DIRECTORY}/templates/{template_file_name}.py", "r") as file:
        python_experiment_template = Template(file.read())

    python_experiment = python_experiment_template.render(
        {
            "experiment_data": experiment_schema.data.dict(),
            "label_classes": experiment_schema.information.label_classes,
            "model": experiment_schema.options.model,
            "epochs": experiment_schema.options.epochs,
            "batch_size": experiment_schema.options.batch_size,
            "learning_rate": experiment_schema.options.learning_rate,
            "experiment_id": experiment_schema.information.experiment['id'],
            "early_stopping": experiment_schema.options.early_stopping
        }
    )

    with open(f"{experiment_directory}/experiment.py", "w+") as file:
        file.write(python_experiment)


@router.post("/{experiment_id}/start")
async def start_experiment(experiment_id: int, request: Request):
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment.py").exists():
        return HTMLResponse(status_code=404, content="Experiment not found")

    command = [
        "/bin/bash",
        "-c",
        f"update-ca-certificates && python3 /usr/deepgeostat-ai/experiments/{experiment_id}/experiment.py"
    ]

    request.app.lock.acquire()
    print("Experiment starter: lock acquired")

    output = get_available_processing_units(request.app.redis_client,
                                            "experiments",
                                            experiment_id,
                                            command,
                                            new_container=True)
    if not output:
        request.app.lock.release()
        print("Experiment starter: lock released")
        return

    if settings.EXPERIMENT_KWARGS:
        output[2] = output[2] + settings.EXPERIMENT_KWARGS

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
    if settings.CERTIFICATE_DIRECTORY:
        volume[settings.CERTIFICATE_DIRECTORY] = {
            "bind": "/usr/local/share/ca-certificates",
            "mode": "ro"
        }

    uuid_key = str(uuid.uuid4())
    container = request.app.docker_client.containers.run(
        image=settings.DOCKER_DEEPGEOSTAT_AI_IMAGE,
        command=command,
        detach=True,
        device_requests=output[0],
        environment=output[2],
        name=f"experiments_{experiment_id}_{uuid_key}",
        volumes=volume
    )

    data = {
        "container_id": container.id,
        "experiment_id": experiment_id,
        "type": "experiments",
        "gpu_id": output[3],
        "cpu_usage": output[1]
    }

    new_key = f"running_containers:experiments:{uuid_key}"
    request.app.redis_client.json().set(new_key, ".", data)
    request.app.redis_client.expire(new_key, (60 * 60 * 24 * 7))

    request.app.lock.release()
    print("Experiment starter: lock released")


@router.post("/{experiment_id}/stop")
async def stop_experiment(experiment_id: int, request: Request):
    request.app.docker_client.api.stop(f"experiment_{experiment_id}")


@router.delete("/{experiment_id}")
async def delete_experiment(experiment_id: int):
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment.py").exists():
        return HTMLResponse(status_code=404, content="Experiment not found")

    directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}"
    shutil.rmtree(directory)


@router.get("/{experiment_id}/learning/{variant}")
async def get_learning_curve(experiment_id: int, variant: LearningCurveType):
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/learning_history.csv").exists():
        return HTMLResponse(status_code=404, content="learning history not found")

    df = pd.read_csv(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/learning_history.csv")

    if variant == LearningCurveType.simple:
        df = df[df["epoch"] == np.floor(df["epoch"])]

    df = df.to_dict(orient="records")
    return JSONResponse(content=df)


@router.get("/{experiment_id}/confusion")
async def get_confusion_matrix(experiment_id: int):
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment_output.json").exists():
        return HTMLResponse(status_code=404, content="no learning output found")
    if not await dict_key_exists(experiment_id, "confusion_matrix"):
        return HTMLResponse(status_code=404, content="no confusion matrix data found")

    data = load_experiments_json(experiment_id)
    return JSONResponse(content=data["confusion_matrix"])


@router.get("/{experiment_id}/experiment")
async def download_experiment(experiment_id: int):
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment.py").exists():
        return HTMLResponse(status_code=404, content="Experiment not found")

    return FileResponse(
        f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/experiment.py",
        media_type="text/x-python",
        filename=f"Experiment #{experiment_id}.py",
    )


@router.post("/{experiment_id}/predictions")
async def create_prediction(experiment_id: int, prediction_schema: PredictionSchema, request: Request):
    experiment_directory = f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}"
    if not pathlib.Path(f"{constants.EXPERIMENTS_DIRECTORY}/{experiment_id}/model").exists():
        return HTMLResponse(status_code=404, content="Model not found")

    # Create prediction.py from template
    with open(f"{constants.PYTHON_DIRECTORY}/templates/prediction.py", "r") as file:
        python_experiment_template = Template(file.read())
    # "prediction_data": prediction_schema.dict()['data'],
    python_experiment = python_experiment_template.render(
        {
            "experiment_id": experiment_id,
            "experiment_type": "classification" if prediction_schema.information.experiment["detection_type"] == 1 else "change_detection",
            "label_classes": prediction_schema.information.label_classes,
            "prediction_data": prediction_schema.data,
        }
    )

    with open(f"{experiment_directory}/prediction.py", "w+") as file:
        file.write(python_experiment)

    # Setup Docker
    command = [
        "python3",
        f"/usr/deepgeostat-ai/experiments/{experiment_id}/prediction.py"
    ]

    request.app.lock.acquire()
    print("Prediction starter: lock acquired")

    output = get_available_processing_units(request.app.redis_client,
                                            "predictions",
                                            experiment_id,
                                            command,
                                            prediction_schema.id,
                                            new_container=True)
    if not output:
        request.app.lock.release()
        print("Prediction starter: lock released")
        return

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

    uuid_key = str(uuid.uuid4())
    container = request.app.docker_client.containers.run(
        image=settings.DOCKER_DEEPGEOSTAT_AI_IMAGE,
        command=command,
        detach=True,
        device_requests=output[0],
        environment=output[2] + [f"OUTPUT_FILE_PREFIX={uuid_key}"],
        name=f"predictions_{experiment_id}_{uuid_key}",
        volumes=volume
    )

    data = {
        "container_id": container.id,
        "experiment_id": experiment_id,
        "redis_id": prediction_schema.id,
        "type": "predictions",
        "gpu_id": output[3],
        "cpu_usage": output[1]
    }

    new_key = f"running_containers:predictions:{uuid_key}"
    request.app.redis_client.json().set(new_key, ".", data)
    request.app.redis_client.expire(new_key, (60 * 60 * 24 * 7))

    request.app.lock.release()
    print("Prediction starter: lock released")
