<?php
namespace App\Services;
use App\Models\Quiz;
class QuizService
{
    public function grade(Quiz $quiz, array $responses): array
    {
        $quiz->load('questions');
        $earned = 0; $total = 0; $review = [];
        foreach ($quiz->questions as $question) {
            $total += $question->points;
            $given = $responses[(string)$question->id] ?? $responses[$question->id] ?? null;
            $correct = $this->normalise($question->correct_answer);
            $actual = $this->normalise($given);
            $isCorrect = $actual === $correct;
            if ($isCorrect) $earned += $question->points;
            $review[] = [
                'question_id'=>$question->id, 'correct'=>$isCorrect,
                'given'=>$given, 'correct_answer'=>$question->correct_answer,
                'explanation'=>$question->explanation,
            ];
        }
        $score = $total > 0 ? round(($earned/$total)*100, 2) : 0;
        return ['score'=>$score,'earned_points'=>$earned,'total_points'=>$total,'passed'=>$score >= $quiz->passing_score,'review'=>$review];
    }
    private function normalise(mixed $value): string
    {
        if (is_array($value)) { sort($value); return json_encode(array_values($value)); }
        return strtolower(trim((string)$value));
    }
}
