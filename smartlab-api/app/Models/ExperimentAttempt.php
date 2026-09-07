<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExperimentAttempt extends Model
{
    protected $fillable = ['user_id','experiment_id','parameters','results','observations','score','status','started_at','completed_at'];
    protected function casts(): array { return ['parameters'=>'array','results'=>'array','score'=>'float','started_at'=>'datetime','completed_at'=>'datetime']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
}
