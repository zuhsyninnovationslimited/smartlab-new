<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProgressRecord extends Model
{
    protected $fillable = ['user_id','experiment_id','manual_completed_at','video_completed_at','quiz_passed_at','experiment_completed_at','report_submitted_at','overall_percent','last_activity_at'];
    protected function casts(): array { return ['manual_completed_at'=>'datetime','video_completed_at'=>'datetime','quiz_passed_at'=>'datetime','experiment_completed_at'=>'datetime','report_submitted_at'=>'datetime','last_activity_at'=>'datetime','overall_percent'=>'integer']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
}
