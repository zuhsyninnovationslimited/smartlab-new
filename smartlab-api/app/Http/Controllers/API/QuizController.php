<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\ProgressService;
use App\Services\QuizService;
use Illuminate\Http\Request;
class QuizController extends Controller
{
    public function upsert(Request $request){
        $d=$request->validate(['experiment_id'=>'required|exists:experiments,id','title'=>'required|string|max:160','instructions'=>'nullable|string','passing_score'=>'required|integer|min:1|max:100','attempts_allowed'=>'required|integer|min:1|max:20','duration_minutes'=>'required|integer|min:1|max:180','is_published'=>'boolean','questions'=>'required|array|min:1','questions.*.question'=>'required|string','questions.*.type'=>'required|in:single,multiple,true_false,numeric','questions.*.options'=>'nullable|array','questions.*.correct_answer'=>'required','questions.*.explanation'=>'nullable|string','questions.*.points'=>'required|integer|min:1|max:100']);
        $quiz=Quiz::updateOrCreate(['experiment_id'=>$d['experiment_id']],collect($d)->except('questions')->all());
        $quiz->questions()->delete(); foreach($d['questions'] as $i=>$q)$quiz->questions()->create($q+['sort_order'=>$i]);
        return $quiz->load('questions');
    }
    public function submit(Request $request, Quiz $quiz, QuizService $grader, ProgressService $progress){
        $data=$request->validate(['responses'=>'required|array']);
        $count=QuizAttempt::where('user_id',$request->user()->id)->where('quiz_id',$quiz->id)->count();
        abort_if($count >= $quiz->attempts_allowed,422,'Maximum attempts reached.');
        $result=$grader->grade($quiz,$data['responses']);
        $attempt=QuizAttempt::create(['user_id'=>$request->user()->id,'quiz_id'=>$quiz->id,'responses'=>$data['responses'],'score'=>$result['score'],'earned_points'=>$result['earned_points'],'total_points'=>$result['total_points'],'passed'=>$result['passed'],'started_at'=>now(),'submitted_at'=>now()]);
        if($result['passed'])$progress->record($request->user()->id,$quiz->experiment_id,'quiz');
        return ['attempt'=>$attempt,'review'=>$result['review'],'message'=>$result['passed']?'Quiz passed. Virtual laboratory unlocked.':'Review the explanations and try again.'];
    }
}
