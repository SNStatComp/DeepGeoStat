from pydantic import BaseModel


class ConsolidateInformation(BaseModel):
    default_label_class: int | None
    label_classes: list
    grids: list


class ConsolidateData(BaseModel):
    registers: list
    other: list


class ConsolidateSchema(BaseModel):
    id: str
    information: ConsolidateInformation
    data: ConsolidateData
