<?php

namespace App\Services;

use App\Models\Experiment;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiLabAssistantService
{
    public function answer(string $message, ?Experiment $experiment): array
    {
        $token = trim((string) config('services.huggingface.token'));

        if ($token === '') {
            return [
                'answer' => $this->offline($message, $experiment),
                'provider' => 'smartlab-offline',
            ];
        }

        $baseUrl = rtrim(
            (string) config(
                'services.huggingface.base_url',
                'https://router.huggingface.co/v1'
            ),
            '/'
        );

        $model = (string) config(
            'services.huggingface.model',
            'Qwen/Qwen2.5-7B-Instruct-1M'
        );

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->timeout(
                    (int) config('services.huggingface.timeout', 45)
                )
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt($experiment),
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],

                    'temperature' => 0.2,

                    'max_tokens' => (int) config(
                        'services.huggingface.max_tokens',
                        700
                    ),

                    'stream' => false,
                ]);

            $answer = $this->extractAnswer($response);

            if ($response->successful() && $answer !== null) {
                return [
                    'answer' => $answer,
                    'provider' => 'huggingface',
                    'model' => $model,
                    'usage' => $response->json('usage'),
                ];
            }

            Log::warning('Hugging Face AI request failed.', [
                'status' => $response->status(),
                'model' => $model,
                'response' => $response->json() ?? $response->body(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [
            'answer' => $this->offline($message, $experiment),
            'provider' => 'smartlab-offline',
        ];
    }

    private function systemPrompt(?Experiment $experiment): string
    {
        $context = $experiment
            ? implode("\n", [
                "Experiment: {$experiment->title}",
                "Category: {$experiment->category}",
                "Summary: {$experiment->summary}",
                "Theory: {$experiment->theory}",

                'Objectives: '.implode(
                    '; ',
                    $experiment->objectives ?? []
                ),

                'Equipment: '.implode(
                    '; ',
                    $experiment->equipment ?? []
                ),

                'Safety notes: '.implode(
                    '; ',
                    $experiment->safety_notes ?? []
                ),

                'Procedure: '.implode(
                    ' | ',
                    $experiment->procedure_steps ?? []
                ),
            ])
            : 'General laboratory learning support.';

        return <<<PROMPT
You are SmartLab AI, a concise and safety-conscious virtual laboratory tutor.

Your responsibilities:

- Explain laboratory concepts, equations, procedures, graphs, and simulation results clearly.
- Use SI units and show calculation steps when calculations are requested.
- Never reveal the correct answer to an active graded quiz.
- Give hints and explain principles instead of completing graded work for the student.
- Clearly distinguish simulated results from physical laboratory measurements.
- Do not invent readings, experimental results, citations, or safety instructions.
- When information is insufficient, state what additional information is required.
- Keep answers focused and suitable for undergraduate students.

Current laboratory context:

{$context}
PROMPT;
    }

    private function extractAnswer(Response $response): ?string
    {
        $content = $response->json(
            'choices.0.message.content'
        );

        if (! is_string($content)) {
            return null;
        }

        $content = trim($content);

        return $content !== '' ? $content : null;
    }

    private function offline(
        string $message,
        ?Experiment $experiment
    ): string {
        $message = strtolower($message);

        if (str_contains($message, 'safety')) {
            return 'Wear appropriate PPE, inspect the apparatus before use, keep the workspace dry and uncluttered, and follow the experiment-specific safety notes. Stop immediately if a physical setup behaves unexpectedly.';
        }

        if (
            str_contains($message, 'co2') ||
            $experiment?->simulation_type === 'co2'
        ) {
            return 'In the CO₂ experiment, calcium carbonate reacts with hydrochloric acid in a 1:2 mole ratio. Calculate the available moles of both reactants first. The smaller stoichiometric amount determines the limiting reagent, after which the ideal-gas relation can estimate the CO₂ volume.';
        }

        if (
            str_contains($message, 'heat') ||
            $experiment?->simulation_type === 'heat-transfer'
        ) {
            return 'Heat moves from a region of higher temperature to a region of lower temperature. For steady one-dimensional conduction, use Q̇ = kAΔT/L. Increasing conductivity, area, or temperature difference increases the heat-transfer rate, while increasing thickness reduces it.';
        }

        if (
            str_contains($message, 'quiz') ||
            str_contains($message, 'answer')
        ) {
            return 'I can explain the principle and demonstrate a similar worked example, but I will not reveal the correct answer to an active graded quiz.';
        }

        $name = $experiment?->title
            ?? 'this laboratory activity';

        return "For {$name}, begin with the objective, identify the controlled and measured variables, check the units, follow the procedure in order, and compare the result with the governing equation. Ask about a specific parameter, formula, graph, or unexpected result for a focused explanation.";
    }
}
