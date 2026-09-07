<?php
use Illuminate\Support\Facades\Route;
Route::get('/{any?}', fn()=>file_exists(public_path('index.html')) ? response()->file(public_path('index.html')) : response('SmartLab frontend has not been built.',503))->where('any','^(?!api|storage).*$');
