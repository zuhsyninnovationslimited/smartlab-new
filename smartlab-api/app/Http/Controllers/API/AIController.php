<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\AiMessage;
use App\Models\Experiment;
use App\Services\AiLabAssistantService;
use Illuminate\Http\Request;
class AIController extends Controller
{
    public function status()
{
    $configured = filled(
        config('services.huggingface.token')
    );

    return response()->json([
        'available' => true,
        'configured' => $configured,
        'mode' => $configured
            ? 'huggingface'
            : 'offline',

        'provider' => $configured
            ? 'Hugging Face'
            : 'SmartLab Offline Assistant',

        'model' => $configured
            ? config('services.huggingface.model')
            : null,
    ]);
}
    public function ask(Request $request, AiLabAssistantService $assistant){
        $d=$request->validate(['message'=>'required|string|max:4000','experiment_id'=>'nullable|exists:experiments,id']);$experiment=isset($d['experiment_id'])?Experiment::find($d['experiment_id']):null;
        AiMessage::create(['user_id'=>$request->user()->id,'experiment_id'=>$experiment?->id,'role'=>'user','content'=>$d['message'],'provider'=>'user']);$answer=$assistant->answer($d['message'],$experiment);
        AiMessage::create(['user_id'=>$request->user()->id,'experiment_id'=>$experiment?->id,'role'=>'assistant','content'=>$answer['answer'],'provider'=>$answer['provider']]);return $answer;
    }
    public function history(Request $request){return AiMessage::where('user_id',$request->user()->id)->latest()->limit(50)->get()->reverse()->values();}
}
