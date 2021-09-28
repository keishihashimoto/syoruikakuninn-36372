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
        # 条件分岐用、ユーザーの年齢を取得
        $today = new DateTime(date("Y-m-d"));
        $birthday = new DateTime(Auth::user()->birthday);
        $age = $today->diff($birthday)->format("%y");
        $age = (int)$age;
        return view("memos.create", compact("age"));
    }

    public function store(Request $request){
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
        # 個人名義の場合は、メモの備考欄冒頭に成人かどうかと学生かどうかを明記。
        # 法人名義の場合は、法人名義である旨を明記
        $memo = new Memo();
        if(Auth::user()->is_corporation == 1){
            $memo->notice = "お客様の属性：法人";
        }elseif($age >= 20){
            $memo->notice = "お客様の属性：個人名義(成人）";
        }else{
            $memo->notice = "お客様の属性：個人名義(未成年）";
        }
        if($request->input("student") == 1){
            $memo->notice .= PHP_EOL."学生のお客様です";
        }elseif(Auth::user()->is_corporation !== 1){
            $memo->notice .= PHP_EOL."学生のお客様ではありません";
        }
        # 一部の手続きに関しては、未成年限定で注意事項を追加
        # 最初に、保護者の同時来店の有無を記載
        # 対象の手続きは、都度同意かどうかによらないものが1 ~ 4, 18, 21
        # 都度同意の場合だと同意書などが必要になるのが5 ~ 11, 16, 17, 19, 20
        # 最初に、保護者の同時来店の有無をメモに追加
        $id = $request->input("procedure-select");
        if(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 1 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->notice .= PHP_EOL."保護者の方の来店：あり。";
        }elseif(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 2 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->notice .= PHP_EOL."保護者の方の来店：なし。";
        }
        # 同意書などについての諸注意は、memoの最後に追加

        # 紛失・解約の場合はSIMカードの持参可否をメモの最初に追加
        if(($id == 10 || $id == 11) && $request->input("sim") == 1){
            $memo->notice .= PHP_EOL."SIMカードのご持参：可能";
        }elseif($id == 10 || $id == 11){
            $memo->notice .= PHP_EOL."SIMカードのご持参：不可能";
        }

        # nwpwがわかるかどうかもメモに追加
        if($request->input("nwpw") != null && $request->input("nwpw") == 1){
            $memo->notice .= PHP_EOL."ネットワーク暗証番号：分かる";
        }elseif($request->input("nwpw") != null){
            $memo->notice .= PHP_EOL."ネットワーク暗証番号：分からない・不安である";
        }

        # 手続きのパターンによって処理を分岐
        $main_pattern = Memo::$procedures[($id - 1)]["main_pattern"];
        if($main_pattern == 1){
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if((Auth::user()->user_licenses->first() == null) || Auth::user()->user_pays->first() == null){
                $memo->notice .= PHP_EOL."ご本人様確認書類もしくはお支払い設定できるものをお持ちでない場合には、上記のお手続きをいただくことはできません。".PHP_EOL."他にお手続きいただける書類がないかどうか、ご自身以外の方の口座などでのお支払い設定が可能かどうかにつきましては、ショップまたはインフォメーションセンターにてお問い合わせください。";
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
                    $memo->notice .= PHP_EOL."住基カードと通帳＋印鑑でのお手続きに関しては、お客様ご自身のお名前の入った補助書類が必要です。".PHP_EOL."住民票や公共料金の領収証などをご用意いただきますようお願いいたします。".PHP_EOL."他にお手続きが可能な方法があるかにつきましては、ショップもしくはインフォメーションセンターにてご確認ください。";
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
                    foreach(Auth::user()->user_pays as $user_pay){
                        $memo_pay = new MemoPay();
                        $memo_pay->memo_id = $memo->id;
                        $memo_pay->pay_id = $user_pay->pay_id;
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
                        $memo->notice .= PHP_EOL."お支払い方法がご自身名義のキャッシュカードの場合、同一住所のご家族のお名前の入った公共料金の領収証も補助書類としてご利用いただけます。";
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
                    if($memo->notice == null){
                        $memo->notice .= PHP_EOL."以下の場合には、ご本人様確認書類とお支払い設定の書類に加えて補助書類（住民票・公共料金の領収証など）が必要になります。".PHP_EOL."・ご本人様確認書類が住民基本台帳カードで、お支払い方法の設定を通帳＋金融機関届出印で行う場合。".PHP_EOL."・ご本人様確認書類が健康保険証で、お支払い方法の設定をキャッシュカードもしくは「通帳＋金融機関届出印」で行う場合。";
                    } else {
                        $memo->notice .= PHP_EOL."以下の場合には、ご本人様確認書類とお支払い設定の書類に加えて補助書類（住民票・公共料金の領収証など）が必要になります。".PHP_EOL."・ご本人様確認書類が住民基本台帳カードで、お支払い方法の設定を通帳＋金融機関届出印で行う場合。".PHP_EOL."・ご本人様確認書類が健康保険証で、お支払い方法の設定をキャッシュカードもしくは「通帳＋金融機関届出印」で行う場合。";
                    }
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            }
            # 最後に手続き固有のnoticeの追加を忘れない。
            $procedure = Memo::$procedures[($id - 1)];
            if($memo->memo_licenses[0]->license_id != 99 || $id == 3 || $id == 1){
                if($memo->notice == null){
                    if(isset($procedure['notice'])){
                        $memo->notice .= PHP_EOL.$procedure['notice'];
                        $memo->save();
                    }
                }else{
                    $memo->notice .= PHP_EOL.$procedure['notice'];
                    $memo->save();
                }
            }
        } elseif($main_pattern == 2 && $id != 23) {
            // 機種変更かそれ以外かで処理を分岐
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if($id == 4 && $request->input("loan") == 1){
                if(count(Auth::user()->user_licenses) != 0){
                    $memo->notice .= PHP_EOL."分割のご希望：あり。";
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                } else {
                    $memo->notice .= PHP_EOL."分割のご希望：あり。".PHP_EOL."申し訳ございませんが、ご本人様確認書類をお持ちでない場合には、分割でのお手続きは致しかねます。".PHP_EOL."ただし、ご自身名義のクレジットカードかキャッシュカードをお持ちの場合には、ネットワーク暗証番号・御生年月日・ご住所のいずれかを御申告いただくことでご対応可能です。".PHP_EOL."詳しくは、ショップまたインフォメーションセンターにお問い合わせください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            } elseif($id == 10 || $id == 11){
                // 紛失・解約は処理を分岐
                // SIMありの場合は必要書類なし
                // nwpwか生年月日を確認
                if($request->input("sim") == 1){
                    $memo->notice .= PHP_EOL."お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    if(isset(Memo::$procedures[($id - 1)]['notice'])){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];   
                    }
                    $memo->save();
                }elseif($request->input("nwpw") == 1){
                    # simなしでもnwpwがわかれば必要書類なし
                    $memo->notice .= PHP_EOL."お手続きの際にお伺いするネットワーク暗証番号に誤りがあった場合にはお手続きができかねます。".PHP_EOL."ご不安な場合には、念のため運転免許証・健康保険証・個人番号カードなどのご本人様確認できるものをお持ちいただくようにお願いいたします。";
                    if(isset(Memo::$procedures[($id - 1)]['notice'])){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];   
                    }
                    $memo->save();
                }elseif(Auth::user()->user_licenses->first() != null){
                    # simもnwpwもngの場合には書類が必要
                    if(isset(Memo::$procedures[($id - 1)]['notice'])){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];   
                    }
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                }else{
                    
                    $memo->notice .= PHP_EOL."上記のお手続きには、以下のいずれかが必ず必要になります。".PHP_EOL."・お手続きされる回線のSIMカード".PHP_EOL."・ご契約者ご本人様の本人確認書類（運転免許証や健康保険証、個人番号カードなど）".PHP_EOL."・ネットワーク暗証番号".PHP_EOL."ご了承ください。";
                    if(isset(Memo::$procedures[($id - 1)]['notice'])){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];   
                    }
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            } else {
                if($id == 4){
                    $memo->notice .= PHP_EOL."分割のご希望：なし。";
                }
                if(isset(Memo::$procedures[($id - 1)]['notice'])){
                    if($memo->notice == null){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];
                    } else {
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];
                    }   
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
                        $memo->notice .= PHP_EOL."お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    } else {
                        $memo->notice .= PHP_EOL."お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    }
                    $memo->save();
                }
            }
        } elseif($main_pattern == 3) {
            // 本人確認書類であれば、どれでもOK
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(isset(Memo::$procedures[($id - 1)]['notice'])){
                $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];
            }
            $memo->save();
            if(is_null(UserLicense::where("user_id", Auth::user()->id)->first())){
                if($request->input("nwpw") == 1 && $id == 8){
                    $memo->notice .= PHP_EOL."現在のネットワーク暗証番号がお分かりでない場合にはお手続きをいただくことができません。あらかじめご了承ください。";
                    $memo->save();
                } else {
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                    $memo->notice .= PHP_EOL."現在のネットワーク暗証番号がお分かりでない場合にはお手続きをいただくことができません。あらかじめご了承ください。";
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
                $memo->user_id = Auth::user()->id;
                $memo->procedure_id = $id;
                $memo->notice .= PHP_EOL."お支払い用紙以外に、特にお持ちいただく書類はございません。ただし、お支払い用紙をお持ちではなかった場合にはお手続きができかねる場合がございます。あらかじめご了承ください。";
                $memo->save();
            } else {
                $memo->user_id = Auth::user()->id;
                $memo->procedure_id = $id;
                if(count(Auth::user()->user_licenses) === 0){
                    $memo->notice .= PHP_EOL."お支払いのみであれば、何もお持ちいただかなくても原則としてお手続きは可能です。しかし、場合によってはお支払い用紙もしくはご本人様確認書類が必要となります。あらかじめご了承ください。";
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
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(isset(Memo::$procedures[($id - 1)]['notice'])){
                $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];
            }
            $memo->save();
        } elseif($id < 23) {
            //シルバーは18歳、Goldは20歳が処理の分岐ポイント
            $memo->user_id = Auth::user()->id;
            $memo->procedure_id = $id;
            if(is_null(Auth::user()->user_licenses->first())){
                $memo->notice .= PHP_EOL."申し訳ございませんが、クレジットカード・クレジットカード(Gold)のお申し込みにはご本人様確認書類が必ず必要になります。".PHP_EOL."詳しくはショップもしくはインフォメーションセンターでご確認ください。";
                $memo->save();
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
            } elseif($id == 21){
                // シルバー
                if($age < 18){
                    $memo->notice .= PHP_EOL."申し訳ございませんが、17歳以下のお客様はクレジットカードをお申し込みいただくことができかねます。ご了承ください。";
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
                        $memo->notice .= PHP_EOL."後日ご自宅に送付される口座振替用紙にて、クレジットカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
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
                            $memo->notice .= PHP_EOL."学生のお客様がクレジットカードをお申し込みいただく場合、上記の書類に加えて学生証が必要となります。あらかじめご了承ください。";
                        } else {
                            $memo->notice .= PHP_EOL."学生のお客様がクレジットカードをお申し込みいただく場合、上記の書類に加えて学生証が必要となります。あらかじめご了承ください。";
                        }
                        $memo->save();
                    }
                }
            } else {
                // Gold
                if ($request->input("student") == 1){
                    $memo->notice .= PHP_EOL."申し訳ございませんが、学生のお客様はクレジットカード(Gold)をお申し込みいただくことができません。クレジットカード(シルバー）であれば、18歳以上のお客様はお申し込みいただくことができます。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                } elseif ($age < 20){
                    $memo->notice .= PHP_EOL."申し訳ございませんが、20歳未満のお客様はクレジットカード(Gold)をお申し込みいただくことができかねます。ご了承ください。";
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
                            $memo->notice .= PHP_EOL."後日ご自宅に送付される口座振替用紙にて、クレジットカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
                        } else {
                            $memo->notice .= PHP_EOL."後日ご自宅に送付される口座振替用紙にて、クレジットカードの口座設定をしていただく必要がございます。当日口座設定をされたい場合は、ご自身名義の銀行口座のキャッシュカードをご用意ください。";
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
        } else {
            # dカードアップグレード
            if(Auth::user()->is_corporation == false){
                $memo->user_id = Auth::user()->id;
                $memo->procedure_id = $id;
                if($age < 20){
                    $memo->notice .= PHP_EOL."申し訳ございませんが、19歳以下のお客様はクレジットカード(Gold)へのアップグレードをお申し込みいただくことができかねます。ご了承ください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }elseif($request->input("student") == 1){
                    $memo->notice .= PHP_EOL."申し訳ございませんが、学生のお客様はクレジットカード(Gold)へのアップグレードをお申し込みいただくことができかねます。ご了承ください。";
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }elseif(Auth::user()->user_licenses != null){
                    $memo->save();
                    foreach(Auth::user()->user_licenses as $user_license){
                        $memo_license = new MemoLicense();
                        $memo_license->memo_id = $memo->id;
                        $memo_license->license_id = $user_license->license_id;
                        $memo_license->save();
                    }
                }else{
                    $memo->notice .= PHP_EOL."お手続きの際に、ネットワーク暗証番号・ご住所・ご連絡先番号のいずれかをお伺いします。あらかじめご了承ください。";
                    $memo->save();
                }
            }
        }
        # 最後に未成年契約者に対する同意書などについての注意を追加して終了
        if(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 1 && ($id <= 4 || $id == 18 || $id == 21)){
            $memo->notice .= PHP_EOL."未成年契約者の方が上記のお手続きをご希望の場合には、保護者の方がご来店されていても追加で以下の書類が必要になります。".PHP_EOL."・免許証や健康保険証などの、保護者の方のご本人様確認書類(原本）".PHP_EOL."・保護者の方にご記入いただいた同意書（ご記入から3ヶ月後の月末まで有効。店頭でご記入いただくことも可能です）。";
        }elseif(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 2 && ($id <= 4 || $id == 18 || $id == 21)){
            $memo->notice .= PHP_EOL."未成年契約者の方が上記のお手続きをご希望の場合で、保護者の方がご来店されない場合には追加で以下の書類が必要になります。".PHP_EOL."・免許証や健康保険証などの、保護者の方のご本人様確認書類(コピー可）".PHP_EOL."・保護者の方にご記入いただいた同意書（ご記入から3ヶ月後の月末が有効期限です）。";
        }elseif(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 1 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->notice .= PHP_EOL."お客様のご契約に関して「都度同意」が設定されていた場合、保護者の方がご来店されていても追加で以下の書類が必要になります。".PHP_EOL."・免許証や健康保険証などの、保護者の方のご本人様確認書類(原本）".PHP_EOL."・保護者の方にご記入いただいた同意書（ご記入から3ヶ月後の月末まで有効。店頭でご記入いただくことも可能です）。";
        }elseif(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 2 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->notice .= PHP_EOL."お客様のご契約に関して「都度同意」が設定されており保護者の方がご来店されない場合、追加で以下の書類が必要になります。".PHP_EOL."・免許証や健康保険証などの、保護者の方のご本人様確認書類(コピー可）".PHP_EOL."・保護者の方にご記入いただいた同意書（ご記入から3ヶ月後の月末まで有効です）。";
        }
        $memo->save();
        $user = Auth::user();
        return view("memos.store", compact("memo", "user"));
    }


    public function destroy(Memo $memo){
        if(Auth::user()->id != $memo->user_id){
            return redirect ()->route("home");
        }
        if($memo->user_id == Auth::user()->id){
            $memo->delete();
        }
        $user = Auth::user();
        return redirect("users/{$user->id}");
    }

    public function show(Memo $memo){
        if(Auth::user()->id != $memo->user_id){
            return redirect ()->route("home");
        }
        return view("memos.show", compact("memo"));
    }

    private function checkUser(Memo $memo){
        if(Auth::user()->id != $memo->user_id){
            return redirect ()->route("home");
        }
    }
}
