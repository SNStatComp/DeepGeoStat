from fastapi import APIRouter, HTTPException
from starlette.requests import Request

router = APIRouter(
    prefix="/health",
    tags=["health"]
)


@router.get("/")
async def health(request: Request):
    for thread in request.app.threads:
        if not thread.is_alive():
            raise HTTPException(status_code=500, detail="Thread is not alive")

