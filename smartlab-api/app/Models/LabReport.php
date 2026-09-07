<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LabReport extends Model
{
    protected $fillable = ['user_id','experiment_id','experiment_attempt_id','title','abstract','method','results_discussion','conclusion','file_path','status','score','feedback','graded_by','submitted_at','graded_at'];
    protected function casts(): array { return ['score'=>'float','submitted_at'=>'datetime','graded_at'=>'datetime']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
}
