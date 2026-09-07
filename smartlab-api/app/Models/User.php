<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'student_id', 'role', 'department', 'password', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function createdExperiments() { return $this->hasMany(Experiment::class, 'created_by'); }
    public function progressRecords() { return $this->hasMany(ProgressRecord::class); }
    public function quizAttempts() { return $this->hasMany(QuizAttempt::class); }
    public function experimentAttempts() { return $this->hasMany(ExperimentAttempt::class); }
    public function reports() { return $this->hasMany(LabReport::class); }

    public function isInstructor(): bool { return in_array($this->role, ['instructor', 'admin'], true); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
