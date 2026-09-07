<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentAttempt;
use App\Models\LabReport;
use App\Models\ProgressRecord;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function __invoke(Request $request){
        $u=$request->user();
        if($u->isInstructor()) return [
            'role'=>$u->role,'stats'=>[
                ['label'=>'Published experiments','value'=>Experiment::where('is_published',true)->count(),'trend'=>'Ready for learners'],
                ['label'=>'Active students','value'=>User::where('role','student')->where('is_active',true)->count(),'trend'=>'Enrolled accounts'],
                ['label'=>'Simulation runs','value'=>ExperimentAttempt::count(),'trend'=>'All-time attempts'],
                ['label'=>'Reports awaiting review','value'=>LabReport::where('status','submitted')->count(),'trend'=>'Instructor action'],
            ],
            'recent_attempts'=>ExperimentAttempt::with('experiment:id,title,slug')->latest()->limit(8)->get(),
        ];
        $records=ProgressRecord::where('user_id',$u->id)->with('experiment:id,title,slug,accent')->get();
        return ['role'=>'student','stats'=>[
            ['label'=>'Overall progress','value'=>round($records->avg('overall_percent')??0).'%','trend'=>'Across all experiments'],
            ['label'=>'Quizzes passed','value'=>QuizAttempt::where('user_id',$u->id)->where('passed',true)->count(),'trend'=>'Pre-lab readiness'],
            ['label'=>'Simulations completed','value'=>ExperimentAttempt::where('user_id',$u->id)->where('status','completed')->count(),'trend'=>'Virtual lab work'],
            ['label'=>'Reports submitted','value'=>LabReport::where('user_id',$u->id)->count(),'trend'=>'Documented findings'],
        ],'progress'=>$records,'recommended'=>Experiment::where('is_published',true)->with(['manuals','videos','quiz'])->limit(4)->get()];
    }
}
