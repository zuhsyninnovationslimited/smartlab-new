from fastapi.testclient import TestClient
from app.main import app
client=TestClient(app)
def test_health():assert client.get('/health').json()['status']=='healthy'
def test_co2_calculation():
 r=client.post('/api/v1/simulate/co2',json={'caco3_mass_g':5,'hcl_molarity':1,'hcl_volume_ml':100})
 assert r.status_code==200;assert r.json()['summary']['co2_volume_l']>0;assert len(r.json()['series'])==21
def test_heat_conduction():
 r=client.post('/api/v1/simulate/heat-transfer',json={'mode':'conduction','conductivity_w_mk':0.8,'area_m2':0.25,'thickness_m':0.02,'hot_temperature_c':100,'cold_temperature_c':25})
 assert r.status_code==200;assert r.json()['summary']['heat_transfer_rate_w']==750.0
