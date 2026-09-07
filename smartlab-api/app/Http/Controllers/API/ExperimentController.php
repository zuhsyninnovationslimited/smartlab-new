<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Experiment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ExperimentController extends Controller
{
    public function index(Request $request){
        $q=Experiment::query()->withCount(['manuals','videos','attempts'])->with('quiz:id,experiment_id,passing_score');
        if(!$request->user()->isInstructor()) $q->where('is_published',true);
        if($request->filled('category')) $q->where('category',$request->string('category'));
        return $q->orderBy('title')->get();
    }
    public function show(Request $request, Experiment $experiment){
        abort_if(!$experiment->is_published && !$request->user()->isInstructor(),404);
        $experiment->load(['manuals'=>fn($q)=>$q->where('is_published',true),'videos'=>fn($q)=>$q->where('is_published',true),'quiz.questions']);
        if($experiment->quiz) $experiment->quiz->questions->makeHidden(['correct_answer']);
        return $experiment;
    }
    public function store(Request $request){ return response()->json(Experiment::create($this->validated($request)+['created_by'=>$request->user()->id]),201); }
    public function update(Request $request, Experiment $experiment){ $experiment->update($this->validated($request,$experiment)); return $experiment->fresh(); }
    public function destroy(Experiment $experiment){ $experiment->delete(); return response()->noContent(); }
    private function validated(Request $request, ?Experiment $experiment=null): array{
        $data=$request->validate(['title'=>'required|string|max:160','slug'=>['nullable','string','max:180',Rule::unique('experiments','slug')->ignore($experiment?->id)],'category'=>'required|string|max:80','simulation_type'=>'required|in:co2,heat-transfer','summary'=>'required|string|max:800','theory'=>'required|string','objectives'=>'required|array|min:1','equipment'=>'required|array|min:1','safety_notes'=>'required|array|min:1','procedure_steps'=>'required|array|min:1','estimated_minutes'=>'required|integer|min:5|max:600','difficulty'=>'required|in:Beginner,Intermediate,Advanced','is_published'=>'boolean','accent'=>'nullable|string|max:30']);
        $data['slug']=$data['slug']??Str::slug($data['title']); return $data;
    }
}
