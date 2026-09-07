<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InstructionalVideo extends Model
{
    protected $fillable = ['experiment_id','title','description','provider','url','file_path','thumbnail_url','duration_seconds','is_required','is_published','sort_order','uploaded_by'];
    protected function casts(): array { return ['is_required'=>'boolean','is_published'=>'boolean']; }
    public function experiment(){ return $this->belongsTo(Experiment::class); }
}
