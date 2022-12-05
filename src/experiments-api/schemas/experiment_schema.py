from pydantic import BaseModel


class ExperimentInformation(BaseModel):
    experiment: dict
    label_classes: list


class ExperimentOptions(BaseModel):
    model: str
    epochs: int
    batch_size: int
    learning_rate: float
    early_stopping: int


class ExperimentData(BaseModel):
    train: list
    test: list | None


class ExperimentSchema(BaseModel):
    information: ExperimentInformation
    options: ExperimentOptions
    data: ExperimentData
