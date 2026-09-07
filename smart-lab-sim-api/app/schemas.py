from typing import Literal
from pydantic import BaseModel, Field, model_validator
class CO2Input(BaseModel):
    caco3_mass_g: float=Field(5,gt=0,le=100)
    hcl_molarity: float=Field(1,gt=0,le=12)
    hcl_volume_ml: float=Field(100,gt=0,le=2000)
    temperature_c: float=Field(25,ge=0,le=100)
    pressure_kpa: float=Field(101.325,gt=0,le=500)
    yield_percent: float=Field(92,ge=0,le=100)
    duration_seconds: int=Field(120,ge=10,le=3600)
class HeatTransferInput(BaseModel):
    mode: Literal['conduction','convection','radiation']='conduction'
    conductivity_w_mk: float=Field(0.8,gt=0,le=500)
    area_m2: float=Field(0.25,gt=0,le=100)
    thickness_m: float=Field(0.02,gt=0,le=10)
    hot_temperature_c: float=Field(100,ge=-273,le=2000)
    cold_temperature_c: float=Field(25,ge=-273,le=2000)
    h_w_m2k: float=Field(12,gt=0,le=10000)
    emissivity: float=Field(0.85,ge=0,le=1)
    duration_seconds: int=Field(300,ge=1,le=86400)
    @model_validator(mode='after')
    def absolute_temperatures(self):
        if self.hot_temperature_c <= -273.15 or self.cold_temperature_c <= -273.15:
            raise ValueError('Temperatures must be above absolute zero.')
        return self
