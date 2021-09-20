<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\UserPay;
use App\Models\User;

class UserPayController extends Controller
{
    public function create(){
      return view("user_pays.create");
    }

    public function store(Request $request){
      if($request->input("pay_ids") != null){
        foreach($request->input("pay_ids") as $pay_id){
          if(is_null(UserPay::where("user_id", Auth::user()->id)->where("pay_id", $pay_id)->first())){
            $user_pay = new UserPay();
            $user_pay->user_id = Auth::user()->id;
            $user_pay->pay_id = $pay_id;
            $user_pay->save();
          }
        }
      }
      $user = Auth::user();
      return redirect("/user_papers/create");
    }

    public function edit(){
      return view("user_pays.edit");
    }

    public function update(Request $request){
      if($request->input("pay_ids") != null){
        foreach($request->input("pay_ids") as $pay_id){
          if(is_null(UserPay::where("user_id", Auth::user()->id)->where("pay_id", $pay_id)->first())){
            $user_pay = new UserPay();
            $user_pay->user_id = Auth::user()->id;
            $user_pay->pay_id = $pay_id;
            $user_pay->save();
          }
        }
      }
      // チェックの外れたuser_payを削除
      if(Auth::user()->user_pays != null && $request->input("pay_ids") != null){
        foreach(Auth::user()->user_pays as $user_pay){
          if(in_array($user_pay->pay_id, $request->input("pay_ids"))){
            
          } else {
            $user_pay->delete();
          }
        }
      } elseif (Auth::user()->user_pays != null){
        foreach(Auth::user()->user_pays as $user_pay){
          $user_pay->delete();
        }
      }
      $user = Auth::user();
      return redirect("users/{$user->id}");
    }
}
