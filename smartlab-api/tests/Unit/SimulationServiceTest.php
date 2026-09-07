<?php
use App\Models\Experiment;use App\Services\SimulationService;use Illuminate\Support\Facades\Http;
it('calculates co2 yield with local fallback',function(){config(['services.simulation.url'=>'']);$e=new Experiment(['simulation_type'=>'co2']);$r=app(SimulationService::class)->run($e,['caco3_mass_g'=>5,'hcl_molarity'=>1,'hcl_volume_ml'=>100]);expect($r['summary']['co2_volume_l'])->toBeGreaterThan(0);});
