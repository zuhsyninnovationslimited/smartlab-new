<?php
namespace App\Services;
use App\Models\Experiment;
use Illuminate\Support\Facades\Http;
class SimulationService
{
    public function run(Experiment $experiment, array $parameters): array
    {
        $url = rtrim((string)config('services.simulation.url'), '/');
        if ($url !== '') {
            try {
                $response = Http::timeout((int)config('services.simulation.timeout', 20))
                    ->withHeaders(['X-SmartLab-Key'=>(string)config('services.simulation.key')])
                    ->post("{$url}/api/v1/simulate/{$experiment->simulation_type}", $parameters);
                if ($response->successful()) return $response->json();
                report(new \RuntimeException('Simulation service error: '.$response->body()));
            } catch (\Throwable $e) { report($e); }
        }
        return match($experiment->simulation_type) {
            'co2' => $this->co2($parameters),
            'heat-transfer' => $this->heatTransfer($parameters),
            default => throw new \InvalidArgumentException('Unsupported simulation type.'),
        };
    }
    private function co2(array $p): array
    {
        $mass=max(0.01,(float)($p['caco3_mass_g']??5)); $molarity=max(0.01,(float)($p['hcl_molarity']??1));
        $volume=max(0.1,(float)($p['hcl_volume_ml']??100))/1000; $temp=(float)($p['temperature_c']??25)+273.15;
        $pressure=max(1,(float)($p['pressure_kpa']??101.325)); $eff=min(100,max(0,(float)($p['yield_percent']??92)))/100;
        $nCaco3=$mass/100.0869; $nHcl=$molarity*$volume; $n=min($nCaco3,$nHcl/2); $actual=$n*$eff;
        $litres=$actual*8.314462618*$temp/$pressure;
        $duration=max(10,(int)($p['duration_seconds']??120)); $series=[];
        for($i=0;$i<=10;$i++){ $t=$duration*$i/10; $fraction=1-exp(-4*$t/$duration); $series[]=['time_s'=>round($t,1),'co2_volume_l'=>round($litres*$fraction,5)]; }
        return ['simulation_type'=>'co2','summary'=>[
            'limiting_reagent'=>$nCaco3 <= $nHcl/2?'Calcium carbonate':'Hydrochloric acid',
            'theoretical_moles_co2'=>round($n,6),'actual_moles_co2'=>round($actual,6),
            'co2_volume_l'=>round($litres,5),'co2_mass_g'=>round($actual*44.0095,5),'yield_percent'=>round($eff*100,2),
        ],'series'=>$series,'warnings'=>$pressure<80?['Low pressure produces a larger calculated gas volume.']:[],'source'=>'laravel-fallback'];
    }
    private function heatTransfer(array $p): array
    {
        $mode=$p['mode']??'conduction'; $area=max(0.0001,(float)($p['area_m2']??0.25)); $duration=max(1,(float)($p['duration_seconds']??300));
        $hot=(float)($p['hot_temperature_c']??100); $cold=(float)($p['cold_temperature_c']??25); $delta=$hot-$cold;
        if($mode==='convection') $rate=max(0,(float)($p['h_w_m2k']??12))*$area*$delta;
        elseif($mode==='radiation'){ $eps=min(1,max(0,(float)($p['emissivity']??0.85))); $rate=$eps*5.670374419e-8*$area*((($hot+273.15)**4)-(($cold+273.15)**4)); }
        else { $k=max(0.001,(float)($p['conductivity_w_mk']??0.8)); $length=max(0.0001,(float)($p['thickness_m']??0.02)); $rate=$k*$area*$delta/$length; }
        $series=[]; for($i=0;$i<=10;$i++){ $t=$duration*$i/10; $series[]=['time_s'=>round($t,1),'energy_j'=>round($rate*$t,3)]; }
        return ['simulation_type'=>'heat-transfer','summary'=>['mode'=>$mode,'temperature_difference_k'=>round($delta,3),'heat_transfer_rate_w'=>round($rate,3),'energy_transferred_j'=>round($rate*$duration,3)],'series'=>$series,'warnings'=>$delta<0?['Hot-side temperature is below cold-side temperature; heat-flow direction is reversed.']:[],'source'=>'laravel-fallback'];
    }
}
