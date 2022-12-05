import threading

import docker
import redis
import rq
from fastapi import FastAPI
from redis.commands.search.field import TextField, NumericField
from redis.commands.search.indexDefinition import IndexDefinition, IndexType
from rq.serializers import JSONSerializer
from starlette.requests import Request

from core.config import settings
from internal import health
from routers import consolidate
from routers import experiments
from workers.container_handler import get_container_status
from workers.queue_handler import handle_queued_containers
from workers.rq_queue_handler import get_finished_jobs

app = FastAPI()


@app.on_event("startup")
def startup_event():
    global container_status_event

    # Setup Docker client
    app.docker_client = docker.from_env()
    try:
        app.docker_client.images.get(settings.DOCKER_DEEPGEOSTAT_AI_IMAGE)
    except docker.errors.ImageNotFound:
        print(f"Image '{settings.DOCKER_DEEPGEOSTAT_AI_IMAGE}' not found locally, pulling from registry")
        app.docker_client.images.pull(settings.DOCKER_DEEPGEOSTAT_AI_IMAGE)
    else:
        print(f"Image '{settings.DOCKER_DEEPGEOSTAT_AI_IMAGE}' found locally")

    # Setup Redis client
    app.redis_client = redis.Redis(
        host=settings.API_REDIS_HOST,
        port=settings.API_REDIS_PORT,
        password=settings.API_REDIS_PASSWORD
    )

    # Setup RediSearch
    schema = (TextField("$.gpu_id", as_name="gpu_id"), NumericField("$.cpu_usage", as_name="cpu_usage"))

    try:
        app.redis_client.ft().create_index(
            schema,
            definition=IndexDefinition(
                prefix=["running_containers:"],
                index_type=IndexType.JSON
            )
        )
    except redis.exceptions.ResponseError as e:
        print(e)
        app.redis_client.ft().dropindex()
        app.redis_client.ft().create_index(
            schema,
            definition=IndexDefinition(
                prefix=["running_containers:"],
                index_type=IndexType.JSON
            )
        )
    app.redis_client.delete("running_containers")

    # Setup RQ
    app.redis_queue = rq.Queue(connection=app.redis_client, serializer=JSONSerializer)
    app.lock = app.redis_client.lock("running_containers")

    app.threads = []
    container_status_event = threading.Event()
    thread = threading.Thread(
        target=get_container_status,
        args=(
            container_status_event,
            app.redis_client,
            app.docker_client
        ),
        name="container_handler"
    )
    app.threads.append(thread)
    thread = threading.Thread(
        target=get_finished_jobs,
        args=(
            container_status_event,
            app.redis_client,
            app.redis_queue
        ),
        name="rq_queue_handler"
    )
    app.threads.append(thread)
    thread = threading.Thread(
        target=handle_queued_containers,
        args=(
            container_status_event,
            app.redis_client,
            app.docker_client,
            app.lock
        ),
        name="queued_containers_handler"
    )
    app.threads.append(thread)
    for thread in app.threads:
        thread.start()


@app.on_event("shutdown")
def shutdown_event():
    container_status_event.set()
    for thread in app.threads:
        thread.join()


app.include_router(experiments.router)
app.include_router(consolidate.router)
app.include_router(health.router)
