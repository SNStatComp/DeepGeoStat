from pydantic import BaseModel


class PredictionInformation(BaseModel):
    experiment: dict
    label_classes: list


class PredictionSchema(BaseModel):
    id: str
    information: PredictionInformation
    data: list
