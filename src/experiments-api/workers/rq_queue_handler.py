from rq.job import Job
from rq.serializers import JSONSerializer
from rq.exceptions import NoSuchJobError

from core.config import settings
from workers.post_request_handler import send_post_request


def get_finished_jobs(event, redis_client, redis_queue):
    failed_registry = redis_queue.failed_job_registry
    finished_registry = redis_queue.finished_job_registry
    while not event.is_set():
        for job_id in failed_registry.get_job_ids():
            try:
                job = Job.fetch(job_id, connection=redis_client, serializer=JSONSerializer)
            except NoSuchJobError as e:
                print(e)
                print(f"Job {job_id} already deleted")
                redis_client.zrem("rq:failed:default", job_id)
                continue

            print(f"Job {job_id} failed")

            post_address = (f"{settings.WEB_APP_ADDRESS}/api/{job.meta['endpoint']}"
                            f"/{job.meta['redis_id']}/error")
            send_post_request(post_address, redis_client, job=job, job_id=job_id)

        for job_id in finished_registry.get_job_ids():
            try:
                job = Job.fetch(job_id, connection=redis_client, serializer=JSONSerializer)
            except NoSuchJobError as e:
                print(e)
                print(f"Job {job_id} already deleted")
                redis_client.zrem("rq:finished:default", job_id)
                continue

            print(f"Job {job_id} finished")

            post_address = (f"{settings.WEB_APP_ADDRESS}/api/{job.meta['endpoint']}"
                            f"/{job.meta['redis_id']}/finish")
            if send_post_request(post_address, redis_client, job=job, job_id=job_id, data=job.result):
                redis_client.delete(f"rq:job:{job_id}")
