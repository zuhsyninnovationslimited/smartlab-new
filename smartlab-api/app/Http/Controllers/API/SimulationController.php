<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentAttempt;
use App\Models\QuizAttempt;
use App\Services\ProgressService;
use App\Services\SimulationService;
use Illuminate\Http\Request;
class SimulationController extends Controller
{
    public function run(Request $request, Experiment $experiment, SimulationService $service, ProgressService $progress){
        abort_unless($experiment->is_published || $request->user()->isInstructor(),404);
        if(!$request->user()->isInstructor() && $experiment->quiz){
            $passed=QuizAttempt::where('user_id',$request->user()->id)->where('quiz_id',$experiment->quiz->id)->where('passed',true)->exists();
            abort_unless($passed,403,'Pass the pre-lab quiz before running this simulation.');
        }
        $data=$request->validate(['parameters'=>'required|array','observations'=>'nullable|string|max:5000']);
        $results=$service->run($experiment,$data['parameters']);
        $attempt=ExperimentAttempt::create(['user_id'=>$request->user()->id,'experiment_id'=>$experiment->id,'parameters'=>$data['parameters'],'results'=>$results,'observations'=>$data['observations']??null,'status'=>'completed','started_at'=>now(),'completed_at'=>now()]);
        $progress->record($request->user()->id,$experiment->id,'experiment');
        return ['attempt'=>$attempt,'results'=>$results];
    }
    public function history(Request $request){
        $q=ExperimentAttempt::with('experiment:id,title,slug,accent')->latest();
        if(!$request->user()->isInstructor())$q->where('user_id',$request->user()->id);
        return $q->limit(50)->get();
    }
}
