<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\LabReport;
use App\Services\ProgressService;
use Illuminate\Http\Request;
class ReportController extends Controller
{
    public function index(Request $request){$q=LabReport::with('experiment:id,title,slug');if(!$request->user()->isInstructor())$q->where('user_id',$request->user()->id);return $q->latest()->get();}
    public function store(Request $request, ProgressService $progress){
        $d=$request->validate(['experiment_id'=>'required|exists:experiments,id','experiment_attempt_id'=>'nullable|exists:experiment_attempts,id','title'=>'required|string|max:180','abstract'=>'required|string','method'=>'required|string','results_discussion'=>'required|string','conclusion'=>'required|string','file'=>'nullable|file|mimes:pdf,doc,docx|max:15360']);
        if(!empty($d['experiment_attempt_id'])){ $valid=\App\Models\ExperimentAttempt::whereKey($d['experiment_attempt_id'])->where('user_id',$request->user()->id)->where('experiment_id',$d['experiment_id'])->exists(); abort_unless($valid,422,'The selected simulation attempt does not belong to this user and experiment.'); } $path=$request->file('file')?->store('reports','public');unset($d['file']);$r=LabReport::create($d+['user_id'=>$request->user()->id,'file_path'=>$path,'status'=>'submitted','submitted_at'=>now()]);$progress->record($request->user()->id,$r->experiment_id,'report');return response()->json($r,201);
    }
    public function grade(Request $request, LabReport $report){$d=$request->validate(['score'=>'required|numeric|min:0|max:100','feedback'=>'required|string']);$report->update($d+['status'=>'graded','graded_by'=>$request->user()->id,'graded_at'=>now()]);return $report;}
}
