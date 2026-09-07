<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiMessage extends Model
{
    protected $fillable = ['user_id','experiment_id','role','content','provider','meta'];
    protected function casts(): array { return ['meta'=>'array']; }
}
