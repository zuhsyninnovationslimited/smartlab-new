<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id','question','type','options','correct_answer','explanation','points','sort_order'];
    protected $hidden = ['correct_answer'];
    protected function casts(): array { return ['options'=>'array','correct_answer'=>'array']; }
    public function quiz(){ return $this->belongsTo(Quiz::class); }
}
