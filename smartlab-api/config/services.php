<?php
return [
    'huggingface' => [
    'token' => env('HF_TOKEN'),

    'base_url' => env(
        'HF_BASE_URL',
        'https://router.huggingface.co/v1'
    ),

    'model' => env(
        'HF_MODEL',
        'deepseek-ai/Deepseek-V4-Pro:together'
    ),

    'timeout' => env(
        'HF_TIMEOUT',
        45
    ),

    'max_tokens' => env(
        'HF_MAX_TOKENS',
        700
    ),
],
    ];
