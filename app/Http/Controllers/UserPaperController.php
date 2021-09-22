<?php

namespace App\Http\Controllers;

use App\Models\UserPaper;
use App\Models\UserPay;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class UserPaperController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create(){
        return view("user_papers.create");
    }

    public function store(Request $request){
        if(($request->input("paper_ids")) != null){
            foreach($request->input("paper_ids") as $paper_id){
                if(is_null(UserPaper::where("user_id", Auth::user()->id)->where("paper_id", $paper_id)->first())){
                    $user_paper = new UserPaper();
                    $user_paper->user_id = Auth::user()->id;
                    $user_paper->paper_id = $paper_id;
                    $user_paper->save();
                }
            }
        }
        $user = Auth::user();
        return redirect("/users/{$user->id}");
    }

    public function edit(){
        return view("user_papers.edit");
    }

    public function update(Request $request){
        if($request->input("paper_ids") != null){
            foreach($request->input("paper_ids") as $paper_id){
                if(is_null(UserPaper::where("user_id", Auth::user()->id)->where("paper_id", $paper_id)->first())){
                    $user_paper = new UserPaper();
                    $user_paper->user_id = Auth::user()->id;
                    $user_paper->paper_id = $paper_id;
                    $user_paper->save();
                }
            }
        }
        if(Auth::user()->user_papers != null && $request->input("paper_ids") != null){
            foreach(Auth::user()->user_papers as $user_paper){
                if(in_array($user_paper->paper_id, $request->input("paper_ids"))){
                    //
                } else {
                    $user_paper->delete();
                }
            }
        } elseif (Auth::user()->user_papers != null && $request->input("paper_ids") == null){
            foreach(Auth::user()->user_papers as $user_paper){
                $user_paper->delete();
            }
        }
        $user = Auth::user();
        return redirect("/users/{$user->id}");
    }
}
