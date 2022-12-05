from fastapi import APIRouter
from fastapi.responses import JSONResponse
from rq.serializers import JSONSerializer
from rq.job import Job
from starlette.requests import Request

from consolidate.dataset_consolidation import consolidate_dataset
from schemas.consolidate_schema import ConsolidateSchema

router = APIRouter(
    prefix="/consolidate",
    tags=["consolidation"],
    responses={404: {"description": "Not found"}},
)


@router.post("/")
async def consolidate(consolidate_schema: ConsolidateSchema, request: Request):
    job = Job.create(consolidate_dataset,
                     args=(
                         consolidate_schema.information.default_label_class,
                         consolidate_schema.information.label_classes,
                         consolidate_schema.information.grids,
                         consolidate_schema.data.dict(),
                     ),
                     timeout="1h",
                     result_ttl=(60 * 10),
                     failure_ttl=(60 * 60 * 24 * 7),
                     connection=request.app.redis_client,
                     serializer=JSONSerializer
                     )
    job.meta['endpoint'] = "inspect"
    job.meta['redis_id'] = consolidate_schema.id
    request.app.redis_queue.enqueue_job(job)

    return JSONResponse(status_code=201, content={"job_id": job.id})
