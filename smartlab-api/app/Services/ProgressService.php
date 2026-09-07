<?php
namespace App\Services;
use App\Models\ProgressRecord;
class ProgressService
{
    public function record(int $userId, int $experimentId, string $milestone): ProgressRecord
    {
        $record = ProgressRecord::firstOrCreate(['user_id'=>$userId,'experiment_id'=>$experimentId]);
        $field = match($milestone) {
            'manual' => 'manual_completed_at', 'video' => 'video_completed_at',
            'quiz' => 'quiz_passed_at', 'experiment' => 'experiment_completed_at',
            'report' => 'report_submitted_at', default => null,
        };
        abort_unless($field, 422, 'Unknown progress milestone.');
        $record->{$field} = now();
        $record->last_activity_at = now();
        $completed = collect(['manual_completed_at','video_completed_at','quiz_passed_at','experiment_completed_at','report_submitted_at'])
            ->filter(fn($key) => filled($record->{$key}))->count();
        $record->overall_percent = $completed * 20;
        $record->save();
        return $record->fresh('experiment:id,title,slug,accent');
    }
}
