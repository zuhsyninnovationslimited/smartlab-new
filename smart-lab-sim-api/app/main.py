from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.routes.simulations import router
app=FastAPI(title="SmartLab Scientific Simulation API",version="2.0.0",description="Validated calculation engine for SmartLab virtual experiments.")
app.add_middleware(CORSMiddleware,allow_origins=[],allow_credentials=False,allow_methods=["POST","GET"],allow_headers=["*"])
@app.get("/health")
def health(): return {"status":"healthy","service":"smartlab-simulation","version":"2.0.0"}
app.include_router(router,prefix="/api/v1")
