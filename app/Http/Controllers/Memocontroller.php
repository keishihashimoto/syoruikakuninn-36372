<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\MemoLicense;
use App\Models\MemoPaper;
use App\Models\UserLicense;
use App\Models\MemoPay;
use App\Models\User;
use App\Models\UserPaper;
use App\Models\UserPay;
use DateTime;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Console\Input\Input;

class MemoController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function create(){
        return view("memos.create");
    }

    public function store(Request $request){
        # バリデーション
        $rules = [
            "procedure-select" => ["required", "integer"]
        ];
        $messages = [
            "procedure-select.integer" => ":attributeを選択してください"
        ];
        $translate = [
            "procedure-select" => "お手続き内容"
        ];
        $this->validate($request, $rules, $messages, $translate);
        # 条件分岐用、ユーザーの年齢を取得
        $today = new DateTime(date("Y-m-d"));
        $birthday = new DateTime(Auth::user()->birthday);
        $age = $today->diff($birthday)->format("%y");
        $age = (int)$age;
        # 手続きのパターンによって処理を分岐
        $id = $request->input("procedure-select");
        $main_pattern = Memo::$procedures[($id - 1)]["main_pattern"];
        if($main_pattern == 1){
            $memo = new Memo();
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if((Auth::user()->user_licenses->first() == null) || Auth::user()->user_pays->first() == null){
                $memo->notice = "ご本人様確認書類もしくはお支払い設定できるものをお持ちでない場合には、上記のお手続きをいただくことはできません。".PHP_EOL."他にお手続きいただける書類がないかどうか、ご自身以外の方の口座などでのお支払い設定が可能かどうかにつきましては、ドコモショップまたはドコモインフォメーションセンターにてお問い合わせください。";
                $memo->save();
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
            }elseif((UserLicense::where("user_id", Auth::user()->id)->where("license_id", 1)->first() != null) || (UserLicense::where("user_id", Auth::user()->id)->where("license_id", 2)->first() != null) || (UserLicense::where("user_id", Auth::user()->id)->where("license_id", 3)->first() != null)){
                // 免許証・経歴証・マイナンバーいずれかあり
                $memo->save();
                for($i = 1; $i <= 3; $i++){
                    if(UserLicense::where("user_id", Auth::user()->id)->where("license_id", $i)->first() != null){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $i;
                        $memo_license->save();
                    }
                }
                foreach(Auth::user()->user_pays as $user_pay){
                    $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = $user_pay->pay_id;
                        $memo_pay->save();
                }
            } elseif (UserLicense::where("user_id", Auth::user()->id)->where("license_id", 4)->first() != null){
                // 住基カードの場合は、支払い設定が通帳＋印鑑のみかどうかで処理を分岐
                $memo->save();
                if((UserPay::where("user_id", Auth::user()->id)->where("pay_id", 1)->first() != null) || (UserPay::where("user_id", Auth::user()->id)->where("pay_id", 2)->first() != null)){
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 4;
                    $memo_license->save();
                    for($i = 1; $i <= 2; $i++){
                        if(UserPay::where("user_id", Auth::user()->id)->where("pay_id", $i)->first() != null){
                            $memo_pay = new MemoPay();
                            $memo_pay->memo_id = $memo->id;
                            $memo_pay->pay_id = $i;
                            $memo_pay->save();
                        }
                    }
                }elseif(UserPay::where("user_id", Auth::user()->id)->where("pay_id", 3)->first() != null && (UserPaper::where("user_id", Auth::user()->id)->where("paper_id", 1)->first() != null || UserPaper::where("user_id", Auth::user()->id)->where("paper_id", 2)->first() != null)){
                    // 通帳＋印鑑で補助書類も既にある場合
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 4;
                    $memo_license->save();
                    for($j = 1; $j <=2; $j++){
                        $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = $j;
                        $memo_pay->save();
                    }
                }else{
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                    $memo->notice = "住基カードと通帳＋印鑑でのお手続きに関しては、お客様ご自身のお名前の入った補助書類が必要です。".PHP_EOL."住民票や公共料金の領収証などをご用意いただきますようお願いいたします。".PHP_EOL."他にお手続きが可能な方法があるかにつきましては、ドコモショップもしくはドコモインフォメーションセンターにてご確認ください。";
                    $memo->save();
                }
            } else {
                // 保険証のみの場合
                // クレジットあり、キャッシュカード + 補助書類あり、通帳＋印鑑で補助書類ありなどで分岐
                if(UserPay::where([
                    ["user_id", Auth::user()->id]
                ])->where("pay_id", 1)->first() != null){
                    # クレジットあり
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 5;
                    $memo_license->save();
                    $memo_pay = new MemoPay();
                    $memo_pay->memo_id = $memo->id;
                    $memo_pay->pay_id = 1;
                    $memo_pay->save();
                } elseif (UserPaper::where([
                    ["user_id", Auth::user()->id]
                ])->where("paper_id", "<=", 2)->first() != null){
                    # 保険証・クレジットなし、自分の補助書類あり。
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 5;
                    $memo_license->save();
                    for($k = 1; $k <= 2; $k++){
                        $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = $k;
                        $memo_pay->save();
                    }
                    for($l = 1; $l <= 2; $l++){
                        $memo_paper = new MemoPaper();
                        $memo_paper->memo_id = $memo->id;
                        $memo_paper->paper_id = $l;
                        $memo_paper->save();
                    }
                    if(UserPay::where([
                        ["user_id", Auth::user()->id]
                    ])->where("pay_id", 2)->first() != null && UserPaper::where([
                        ["user_id", Auth::user()->id]
                    ])->where("paper_id", 3)->first() != null){
                        $memo->notice = "お支払い方法がご自身名義のキャッシュカードの場合、同一住所のご家族のお名前の入った公共料金の領収証も補助書類としてご利用いただけます。";
                        $memo->save();
                    }
                } elseif(UserPay::where([
                    ["user_id", Auth::user()->id]
                ])->where("pay_id", 2)->first() != null && UserPaper::where([
                    ["user_id", Auth::user()->id]
                ])->where("paper_id", 3)->first() != null){
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 5;
                    $memo_license->save();
                    $memo_pay = new MemoPay();
                    $memo_pay->memo_id = $memo->id;
                    $memo_pay->pay_id = 2;
                    $memo_pay->save();
                    $memo_paper = new MemoPaper();
                    $memo_paper->memo_id = $memo->id;
                    $memo_paper->paper_id = 3;
                    $memo_paper->save();
                } else {
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            }
            # 最後に手続き固有のnoticeの追加を忘れない。
            $procedure = Memo::$procedures[($id - 1)];
            if($memo->memo_licenses[0]->license_id != 99){
                if($memo->notice == null){
                    if(isset($procedure['notice'])){
                        $memo->notice = $procedure['notice'];
                    }
                }else{
                    $memo->notice .= PHP_EOL.$procedure['notice'];
                }
            }
        } elseif($main_pattern == 2) {
            // 機種変更かそれ以外かで処理を分岐
            $memo = new Memo();
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if($id == 4 && $request->input("loan") == 1){
                if(count(Auth::user()->user_licenses) != 0){
                    $memo->notice = "分割のご希望：あり。";
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                } else {
                    $memo->notice = "分割のご希望：あり。".PHP_EOL."申し訳ございませんが、ご本人様確認書類をお持ちでない場合には、分割でのお手続きは致しかねます。".PHP_EOL."ただし、ご自身名義のクレジットカードかキャッシュカードをお持ちの場合には、ネットワーク暗証番号・御生年月日・ご住所のいずれかを御申告いただくことでご対応可能です。".PHP_EOL."詳しくは、ドコモショップまたはドコモインフォメーションセンターにお問い合わせください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            } else {
                if($id == 4){
                    $memo->notice = "分割のご希望：なし。";
                }
                if(UserLicense::where("user_id", Auth::user()->id)->first() != null){
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                } else {
                    if($memo->notice == null){
                        $memo->notice = "お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    } else {
                        $memo->notice .= PHP_EOL."お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    }
                    $memo->save();
                }
            }
        } elseif($main_pattern == 3) {
            // 本人確認書類であれば、どれでもOK
            $memo = new Memo();
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(isset(Memo::$procedures[($id - 1)]['notice'])){
                $memo->notice = Memo::$procedures[($id - 1)]['notice'];
            }
            $memo->save();
            if(is_null(UserLicense::where("user_id", Auth::user()->id)->first())){
                if($request->input("nwpw") == 1 && $id == 8){
                    $memo->notice = "現在のネットワーク暗証番号がお分かりでない場合にはお手続きをいただくことができません。あらかじめご了承ください。";
                    $memo->save();
                } else {
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                    $memo->notice = "現在のネットワーク暗証番号がお分かりでない場合にはお手続きをいただくことができません。あらかじめご了承ください。";
                    $memo->save();
                }
            } else {
                foreach(Auth::user()->user_licenses as $user_license){
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = $user_license->license_id;
                    $memo_license->save();
                }
            }
        } elseif($main_pattern == 4) {
            //
        } elseif($main_pattern == 5) {
            // 支払い用紙がある場合とない場合で処理を分岐
            if($request->input("payment-form") == 1){
                $memo = new Memo();
                $memo->user_id = Auth::user()->id;
                $memo->procedure_id = $id;
                $memo->notice = "お支払い用紙以外に、特にお持ちいただく書類はございません。ただし、お支払い用紙をお持ちではなかった場合にはお手続きができかねる場合がございます。あらかじめご了承ください。";
                $memo->save();
            } else {
                $memo = new Memo();
                $memo->user_id = Auth::user()->id;
                $memo->procedure_id = $id;
                if(count(Auth::user()->user_licenses) === 0){
                    $memo->notice = "お支払いのみであれば、何もお持ちいただかなくても原則としてお手続きは可能です。しかし、場合によってはお支払い用紙もしくはご本人様確認書類が必要となります。あらかじめご了承ください。";
                    $memo->save();
                } else {
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                }
            }
        } elseif($main_pattern == 6) {
            $memo = new Memo();
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(isset(Memo::$procedures[($id - 1)]['notice'])){
                $memo->notice = Memo::$procedures[($id - 1)]['notice'];
            }
            $memo->save();
        } else {
            //シルバーは18歳、Goldは20歳が処理の分岐ポイント
            $memo = new Memo();
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(is_null(Auth::user()->user_licenses->first())){
                $memo->notice = "申し訳ございませんが、dカード・dカードGoldのお申し込みにはご本人様確認書類が必ず必要になります。".PHP_EOL."詳しくはドコモショップもしくはドコモインフォメーションセンターでご確認ください。";
                $memo->save();
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
            } elseif($id == 21){
                // シルバー
                if($age < 18){
                    $memo->notice = "申し訳ございませんが、17歳以下のお客様はdカードをお申し込みいただくことができかねます。ご了承ください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                } else {
                    $memo->save();
                    // 免許証の有無で処理を分岐
                    if(is_null(UserLicense::where("user_id", Auth::user()->id)->where("license_id", 1)->first())){
                        foreach(Auth::user()->user_licenses as $user_license){
                            $memo_license = new MemoLicense();
                            $memo_license->memo_id = $memo->id;
                            $memo_license->license_id = $user_license->license_id;
                            $memo_license->save();
                        }
                    } else {
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = 1;
                        $memo_license->save();
                    }
                    // キャッシュカードを持っているかどうかで処理を分岐
                    if(is_null(UserPay::where("user_id", Auth::user()->id)->where("pay_id", 2)->first())){
                        $memo->notice = "後日ご自宅に送付される口座振替用紙にて、dカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
                        $memo->save();
                    } else {
                        $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = 2;
                        $memo_pay->save();
                    }
                    // 最後に、学生の場合の注意事項を追加して終了
                    if ($request->input("student") == 1){
                        if($memo->notice == null){
                            $memo->notice = "学生のお客様がdカードをお申し込みいただく場合、上記の書類に加えて学生証が必要となります。あらかじめご了承ください。";
                        } else {
                            $memo->notice = $memo->notice.PHP_EOL."学生のお客様がdカードをお申し込みいただく場合、上記の書類に加えて学生証が必要となります。あらかじめご了承ください。";
                        }
                        $memo->save();
                    }
                }
            } else {
                // Gold
                if ($request->input("student") == 1){
                    $memo->notice = "申し訳ございませんが、学生のお客様はdカードGoldをお申し込みいただくことができません。dカード(シルバー）であれば、18歳以上のお客様はお申し込みいただくことができます。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                } elseif ($age < 20){
                    $memo->notice = "申し訳ございませんが、20歳未満のお客様はdカードGoldをお申し込みいただくことができかねます。ご了承ください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                } else {
                    // シルバーと同じ処理
                    $memo->save();
                    // 免許証の有無で処理を分岐
                    if(is_null(UserLicense::where("user_id", Auth::user()->id)->where("license_id", 1)->first())){
                        foreach(Auth::user()->user_licenses as $user_license){
                            $memo_license = new MemoLicense();
                            $memo_license->memo_id = $memo->id;
                            $memo_license->license_id = $user_license->license_id;
                            $memo_license->save();
                        }
                    } else {
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = 1;
                        $memo_license->save();
                    }
                    // キャッシュカードを持っているかどうかで処理を分岐
                    if(is_null(UserPay::where("user_id", Auth::user()->id)->where("pay_id", 2)->first())){
                        if($memo->notice == null){
                            $memo->notice = "後日ご自宅に送付される口座振替用紙にて、dカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
                        } else {
                            $memo->notice = $memo->notice.PHP_EOL."後日ご自宅に送付される口座振替用紙にて、dカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
                        }
                        $memo->save();
                    } else {
                        $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = 2;
                        $memo_pay->save();
                    }
                }
            }
        }
        $user = Auth::user();
        return view("memos.store", compact("memo", "user"));
    }


    public function destroy(Memo $memo){
        if($memo->user_id == Auth::user()->id){
            $memo->delete();
        }
        $user = Auth::user();
        return redirect("users/{$user->id}");
    }

    public function show(Memo $memo){
        return view("memos.show", compact("memo"));
    }
}
