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
        if(Auth::user()->id != $user->id){
            return redirect()->route("home");
        }
        return view("users.show", compact("user"));
    }

    public function edit(User $user){
        if(Auth::user()->id != $user->id){
            return redirect()->route("home");
        }
        return view("users.edit", compact("user"));
    }

    public function update(User $user, Request $request){
        if(Auth::user()->id != $user->id){
            return redirect()->route("home");
        }
        # バリデーション
        $rules = [
            "name" => ["required", "string", "max:255"],
            "year" => ["required", "integer", "min:1800"],
            "month" => ["required", "integer", "min:1", "max:12"],
            "date" => ["required", "integer", "min:1", "max:31"]
        ];
        $messages = [
            "name.required" => "お名前を入力してください",
            "name.string" => "お名前に不適切な文字が使用されています",
            "name.max" => "お名前は255文字以下で入力してください",
            "year.required" => "お生まれになった年が入力されていません",
            "year.integer" => "お生まれになった年は数字で入力してください",
            "year.min" => "お生まれになった年は4桁の数字で入力してください",
            "month.required" => "お生まれになった月が入力されていません",
            "month.integer" => "お生まれになった月は数字で入力してください",
            "month.min" => "お生まれになった月は1 ~ 12で入力してください",
            "month.max" => "お生まれになった月は1 ~ 12で入力してください",
            " date.required" => "お生まれになった日が入力されていません",
            "date.integer" => "お生まれになった日は数字で入力してください",
            "date.min" => "お生まれになった日は1 ~ 31で入力してください",
            "date.max" => "お生まれになった日は1 ~ 31で入力してください"
        ];
        
        $this->validate($request, $rules, $messages);
        // ユーザー基本情報の更新部分
        $user->name = $request->input("name");
        $user->birthday = $request->input("year")."-".$request->input("month")."-".$request->input("date");
        $user->save();

        return redirect()->route("users.show", $user);
    }

    public function destroy(User $user){
        if(Auth::user()->id != $user->id){
            return redirect()->route("home");
        }
        $user->delete();
        return view("users.destroy");
    }

    private function identifyUser(User $user){
        if ($user->id != Auth::user()->id){
            return redirect("/home");
        }
    }
}
