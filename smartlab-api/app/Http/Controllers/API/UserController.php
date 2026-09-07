<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
class UserController extends Controller
{
    public function index(){return User::orderBy('name')->paginate(50);}
    public function store(Request $request){$d=$request->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users,email','student_id'=>'nullable|string|max:40|unique:users,student_id','department'=>'nullable|string|max:120','role'=>'required|in:student,instructor,admin','password'=>['required',Password::min(8)->letters()->numbers()]]);return response()->json(User::create($d+['is_active'=>true]),201);}
    public function update(Request $request, User $user){$d=$request->validate(['name'=>'sometimes|string|max:120','department'=>'nullable|string|max:120','role'=>'sometimes|in:student,instructor,admin','is_active'=>'sometimes|boolean']);$user->update($d);return $user;}
}
