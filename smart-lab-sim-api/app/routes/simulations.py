import os,secrets
from fastapi import APIRouter,Depends,Header,HTTPException
from app.schemas import CO2Input,HeatTransferInput
from app.services import simulate_co2,simulate_heat
router=APIRouter(tags=['simulations'])
def verify(x_smartlab_key:str|None=Header(default=None)):
    expected=os.getenv('SMART_LAB_API_KEY','')
    if expected and (not x_smartlab_key or not secrets.compare_digest(x_smartlab_key,expected)):
        raise HTTPException(status_code=401,detail='Invalid SmartLab service key.')
@router.post('/simulate/co2',dependencies=[Depends(verify)])
def co2(payload:CO2Input):return simulate_co2(payload)
@router.post('/simulate/heat-transfer',dependencies=[Depends(verify)])
def heat(payload:HeatTransferInput):return simulate_heat(payload)
