<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;
use App\Models\User;
use App\Models\UserLicense;
use Illuminate\Support\Facades\Auth;

class UserLicenseController extends Controller
{
    public function create(){
        return view ("user_licenses.create");
    }
    public function store(Request $request){
        if ($request->input("licenses") != null){
            foreach ($request->input("licenses") as $license){
                if(is_null(UserLicense::where("user_id", Auth::user()->id)->where("license_id", $license)->first())){
                    $user_license = new UserLicense();
                    $user_license->user_id = Auth::user()->id;
                    $user_license->license_id = $license;
                    $user_license->save();
                }
            }
        }
        return view("user_pays.create");
    }

    public function edit(){
        return view("user_licenses.edit");
    }

    public function update(Request $request){
        // 追加されたuser_licenseを保存する処理
        if($request->input("license_ids") != null){
            foreach ( $request->input("license_ids") as $license ) {
                if(is_null(UserLicense::where("user_id", Auth::user()->id)->where("license_id", $license)->first())){
                    $user_license = new UserLicense();
                    $user_license->user_id = Auth::user()->id;
                    $user_license->license_id = $license;
                    $user_license->save();
                }   
            }
        }
        // チェックが外れたuser_licenseを削除する処理
        $checklist = $request->input("license_ids");
        if ($checklist == null){
            $checklist = [];
        }
        $array = UserLicense::where("user_id", Auth::user()->id)->get();
        $list = [];
        foreach($array as $item){
            $list[] = $item["license_id"];
        }
        foreach($list as $listItem){
            if(in_array($listItem, $checklist)){
                //何もしない
            } else {
                $targets = UserLicense::where("user_id", Auth::user()->id)->where("license_id", $listItem)->get();
                foreach($targets as $target){
                    $target->delete();
                }
            }
        }
        // 最後に修正完了のページに遷移する;
        $user = Auth::user();
        return redirect("users/{$user->id}");
    }
}
