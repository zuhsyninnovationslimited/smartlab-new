<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Quiz extends Model
{
    protected $fillable = ['experiment_id','title','instructions','passing_score','attempts_allowed','duration_minutes','is_published'];
    protected function casts(): array { return ['is_published'=>'boolean']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
    public function questions(){ return $this->hasMany(QuizQuestion::class)->orderBy('sort_order'); }
}
