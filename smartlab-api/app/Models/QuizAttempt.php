<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuizAttempt extends Model
{
    protected $fillable = ['user_id','quiz_id','responses','score','earned_points','total_points','passed','started_at','submitted_at'];
    protected function casts(): array { return ['responses'=>'array','passed'=>'boolean','score'=>'float','started_at'=>'datetime','submitted_at'=>'datetime']; }
    public function quiz(){ return $this->belongsTo(Quiz::class); }
}
