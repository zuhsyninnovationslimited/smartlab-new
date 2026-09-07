<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Manual extends Model
{
    protected $fillable = ['experiment_id','title','content','file_path','version','is_required','is_published','sort_order','uploaded_by'];
    protected function casts(): array { return ['is_required'=>'boolean','is_published'=>'boolean']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
}
