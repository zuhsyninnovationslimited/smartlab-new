<?php
use App\Models\User;
it('registers only a student role',function(){$r=$this->postJson('/api/v1/register',['name'=>'Test Student','email'=>'test@example.com','student_id'=>'SL/1','password'=>'Secure123','password_confirmation'=>'Secure123']);$r->assertCreated()->assertJsonPath('user.role','student');});
it('protects the dashboard',function(){$this->getJson('/api/v1/dashboard')->assertUnauthorized();});
it('logs in a valid user',function(){$u=User::factory()->create(['password'=>'Secure123']);$this->postJson('/api/v1/login',['email'=>$u->email,'password'=>'Secure123'])->assertOk()->assertJsonStructure(['user','token']);});
