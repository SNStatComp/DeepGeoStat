from pydantic import (AnyHttpUrl, BaseSettings, validator, root_validator)
from pydantic.validators import str_validator


def empty_to_none(v: str) -> str | None:
    if v == '':
        return None
    return v


class EmptyStrToNone(str):
    @classmethod
    def __get_validators__(cls):
        yield str_validator
        yield empty_to_none


class Settings(BaseSettings):
    class Config:
        validate_assignment = True

    WEB_APP_ADDRESS: AnyHttpUrl
    API_REDIS_HOST: str
    API_REDIS_PORT: str
    API_REDIS_PASSWORD: None | EmptyStrToNone | str
    DOCKER_DEEPGEOSTAT_AI_IMAGE: None | EmptyStrToNone | str
    IMAGE_DIRECTORY: str
    CERTIFICATE_DIRECTORY: None | EmptyStrToNone | str
    GPU_IDS: None | EmptyStrToNone | list[str]
    EXPERIMENT_KWARGS: None | EmptyStrToNone | list[str]
    CONTAINER_PER_GPU: None | EmptyStrToNone | int
    CONTAINER_ON_CPU: None | EmptyStrToNone | int

    @root_validator()
    def get_cpu_amount(cls, values):
        if not values.get('GPU_IDS') and not values.get('CONTAINER_ON_CPU'):
            values['GPU_IDS'] = []
            values['CONTAINER_ON_CPU'] = 1
            return values

        elif values.get('GPU_IDS') and not values.get('CONTAINER_ON_CPU'):
            values['CONTAINER_ON_CPU'] = 0
            return values

        return values

    @validator('CONTAINER_PER_GPU')
    def set_number(cls, amount):
        return amount or 1

    @validator('DOCKER_DEEPGEOSTAT_AI_IMAGE')
    def set_name(cls, url):
        return url or "registry.gitlab.com/cbds/deepgeostat/deepgeostat-ai:latest"


settings = Settings()
