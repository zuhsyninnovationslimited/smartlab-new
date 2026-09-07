<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by', 'title', 'slug', 'category', 'simulation_type', 'summary', 'theory',
        'objectives', 'equipment', 'safety_notes', 'procedure_steps', 'estimated_minutes',
        'difficulty', 'is_published', 'accent',
    ];

    protected function casts(): array
    {
        return [
            'objectives' => 'array', 'equipment' => 'array', 'safety_notes' => 'array',
            'procedure_steps' => 'array', 'is_published' => 'boolean',
        ];
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function manuals() { return $this->hasMany(Manual::class)->orderBy('sort_order'); }
    public function videos() { return $this->hasMany(InstructionalVideo::class)->orderBy('sort_order'); }
    public function quiz() { return $this->hasOne(Quiz::class); }
    public function attempts() { return $this->hasMany(ExperimentAttempt::class); }
}
