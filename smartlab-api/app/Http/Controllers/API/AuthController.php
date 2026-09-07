<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller
{
    public function register(Request $request){
        $data=$request->validate(['name'=>'required|string|max:120','email'=>'required|email|max:160|unique:users,email','student_id'=>'required|string|max:40|unique:users,student_id','department'=>'nullable|string|max:120','password'=>['required','confirmed',Password::min(8)->letters()->numbers()]]);
        $user=User::create([...$data,'role'=>'student','is_active'=>true]);
        return response()->json(['user'=>$user,'token'=>$user->createToken('smartlab-web')->plainTextToken],201);
    }
    public function login(Request $request){
        $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
        $user=User::where('email',$data['email'])->first();
        if(!$user || !$user->is_active || !Hash::check($data['password'],$user->password)) return response()->json(['message'=>'Invalid credentials or inactive account.'],422);
        $user->tokens()->delete();
        return ['user'=>$user,'token'=>$user->createToken('smartlab-web')->plainTextToken];
    }
    public function me(Request $request){ return ['user'=>$request->user()]; }
    public function logout(Request $request){ $request->user()->currentAccessToken()?->delete(); return ['message'=>'Logged out.']; }
}
