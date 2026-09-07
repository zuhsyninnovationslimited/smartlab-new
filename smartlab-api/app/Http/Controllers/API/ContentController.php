<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\InstructionalVideo;
use App\Models\Manual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ContentController extends Controller
{
    public function storeManual(Request $request){
        $d=$request->validate(['experiment_id'=>'required|exists:experiments,id','title'=>'required|string|max:160','content'=>'required_without:file|nullable|string','file'=>'nullable|file|mimes:pdf,doc,docx|max:15360','version'=>'nullable|string|max:30','is_required'=>'boolean','is_published'=>'boolean','sort_order'=>'nullable|integer']);
        $path=$request->file('file')?->store('manuals','public'); unset($d['file']);
        return response()->json(Manual::create($d+['file_path'=>$path,'uploaded_by'=>$request->user()->id]),201);
    }
    public function updateManual(Request $request, Manual $manual){
        $d=$request->validate(['title'=>'sometimes|string|max:160','content'=>'nullable|string','file'=>'nullable|file|mimes:pdf,doc,docx|max:15360','version'=>'nullable|string|max:30','is_required'=>'boolean','is_published'=>'boolean','sort_order'=>'nullable|integer']);
        if($request->file('file')){ if($manual->file_path)Storage::disk('public')->delete($manual->file_path); $d['file_path']=$request->file('file')->store('manuals','public'); }
        unset($d['file']); $manual->update($d); return $manual;
    }
    public function deleteManual(Manual $manual){ if($manual->file_path)Storage::disk('public')->delete($manual->file_path); $manual->delete(); return response()->noContent(); }
    public function storeVideo(Request $request){
        $d=$request->validate(['experiment_id'=>'required|exists:experiments,id','title'=>'required|string|max:160','description'=>'nullable|string','provider'=>'required|in:upload,youtube,external','url'=>'required_unless:provider,upload|nullable|url','video'=>'required_if:provider,upload|nullable|file|mimes:mp4,webm,mov|max:102400','thumbnail_url'=>'nullable|url','duration_seconds'=>'nullable|integer|min:1','is_required'=>'boolean','is_published'=>'boolean','sort_order'=>'nullable|integer']);
        $path=$request->file('video')?->store('videos','public'); unset($d['video']);
        return response()->json(InstructionalVideo::create($d+['file_path'=>$path,'uploaded_by'=>$request->user()->id]),201);
    }
    public function updateVideo(Request $request, InstructionalVideo $video){
        $d=$request->validate(['title'=>'sometimes|string|max:160','description'=>'nullable|string','provider'=>'sometimes|in:upload,youtube,external','url'=>'nullable|url','video'=>'nullable|file|mimes:mp4,webm,mov|max:102400','thumbnail_url'=>'nullable|url','duration_seconds'=>'nullable|integer|min:1','is_required'=>'boolean','is_published'=>'boolean','sort_order'=>'nullable|integer']);
        if($request->file('video')){if($video->file_path)Storage::disk('public')->delete($video->file_path);$d['file_path']=$request->file('video')->store('videos','public');} unset($d['video']);$video->update($d);return $video;
    }
    public function deleteVideo(InstructionalVideo $video){if($video->file_path)Storage::disk('public')->delete($video->file_path);$video->delete();return response()->noContent();}
}
