<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Models\UserLicense;

use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
        # ログインしていない時にはログイン画面に戻される
    }
    
    public function index(){
        return view("users.index");
    }

    public function show(User $user){
        $this->identifyUser($user);
        return view("users.show", compact("user"));
    }

    public function edit(User $user){
        $this->identifyUser($user);
        return view("users.edit", compact("user"));
    }

    public function update(User $user, Request $request){
        $this->identifyUser($user);
        // ユーザー基本情報の更新部分
        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->birthday = $request->input("year")."-".$request->input("month")."-".$request->input("date");
        $user->save();

        return redirect()->route("users.show", $user);
    }

    public function destroy(User $user){
        $this->identifyUser($user);
        $user->delete();
        return view("users.destroy");
    }

    private function identifyUser(User $user){
        if ($user->id != Auth::user()->id){
            return redirect()->route("users.index");
        }
    }
}
