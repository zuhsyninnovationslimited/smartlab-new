<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class SpaController extends Controller
{
    public function __invoke(): BinaryFileResponse|Response
    {
        if (! file_exists(public_path('index.html'))) {
            return response('Smart Lab API is running.', 200);
        }

        return response()->file(public_path('index.html'));
    }
}
