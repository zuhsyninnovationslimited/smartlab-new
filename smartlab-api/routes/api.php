<?php
use App\Http\Controllers\API\{AIController,AuthController,ContentController,DashboardController,ExperimentController,ProgressController,QuizController,ReportController,SimulationController,UserController};
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function(){
 Route::middleware('throttle:12,1')->group(function(){Route::post('register',[AuthController::class,'register']);Route::post('login',[AuthController::class,'login']);});
 Route::middleware('auth:sanctum')->group(function(){
  Route::get('me',[AuthController::class,'me']);Route::post('logout',[AuthController::class,'logout']);Route::get('dashboard',DashboardController::class);
  Route::get('experiments',[ExperimentController::class,'index']);Route::get('experiments/{experiment:slug}',[ExperimentController::class,'show']);
  Route::post('experiments/{experiment:slug}/progress',[ProgressController::class,'milestone']);Route::get('progress',[ProgressController::class,'index']);
  Route::post('quizzes/{quiz}/submit',[QuizController::class,'submit'])->middleware('throttle:20,1');
  Route::post('experiments/{experiment:slug}/simulate',[SimulationController::class,'run'])->middleware('throttle:30,1');Route::get('simulation-history',[SimulationController::class,'history']);
  Route::get('reports',[ReportController::class,'index']);Route::post('reports',[ReportController::class,'store']);
  Route::get('ai/status', [AIController::class, 'status']);
Route::get('ai/history', [AIController::class, 'history']);
Route::post('ai/ask', [AIController::class, 'ask'])->middleware('throttle:30,1');
  Route::middleware('role:instructor,admin')->group(function(){
   Route::post('experiments',[ExperimentController::class,'store']);Route::put('experiments/{experiment}',[ExperimentController::class,'update']);Route::delete('experiments/{experiment}',[ExperimentController::class,'destroy']);
   Route::post('manuals',[ContentController::class,'storeManual']);Route::post('manuals/{manual}',[ContentController::class,'updateManual']);Route::delete('manuals/{manual}',[ContentController::class,'deleteManual']);
   Route::post('videos',[ContentController::class,'storeVideo']);Route::post('videos/{video}',[ContentController::class,'updateVideo']);Route::delete('videos/{video}',[ContentController::class,'deleteVideo']);
   Route::post('quizzes/upsert',[QuizController::class,'upsert']);Route::post('reports/{report}/grade',[ReportController::class,'grade']);
   Route::get('users',[UserController::class,'index']);Route::post('users',[UserController::class,'store']);Route::put('users/{user}',[UserController::class,'update']);
  });
 });
});
