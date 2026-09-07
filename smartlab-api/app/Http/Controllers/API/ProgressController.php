<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ProgressRecord;
use App\Services\ProgressService;
use Illuminate\Http\Request;
class ProgressController extends Controller
{
    public function index(Request $request){ return ProgressRecord::where('user_id',$request->user()->id)->with('experiment:id,title,slug,accent,category')->get(); }
    public function milestone(Request $request, Experiment $experiment, ProgressService $service){
        $d=$request->validate(['milestone'=>'required|in:manual,video']); return $service->record($request->user()->id,$experiment->id,$d['milestone']);
    }
}
