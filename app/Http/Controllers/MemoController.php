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
        #dd($request);
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
            $memo->condition = "お客様の属性：法人";
        }elseif($age >= 20){
            $memo->condition = "お客様の属性：個人名義(成人）";
        }else{
            $memo->condition = "お客様の属性：個人名義(未成年）";
        }
        if($request->input("student") == 1){
            $memo->condition .= PHP_EOL."学生のお客様です";
        }elseif(Auth::user()->is_corporation !== 1){
            $memo->condition .= PHP_EOL."学生のお客様ではありません";
        }
        $id = $request->input("procedure-select");
        # 来店者の情報をメモの追加
        if($id < 13 || $id > 15){
            if($request->input("comer") == 1){
                $memo->condition .= PHP_EOL."来店者：契約者ご本人様";
            }elseif($request->input("relation") == 1 && ($request->input("agent") == 1 || $request->input("agent") == 2)){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（同一住所・ドコモ回線あり）";
            }elseif($request->input("relation") == 1){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（同一住所・ドコモ回線なし）";
            }elseif(($request->input("relation") == 2 || $request->input("relation") == 3) && ($request->input("agent") == 1 || $request->input("agent") == 2)){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（同一住所ではないご家族の方・ドコモ回線あり）";
            }elseif($request->input("relation") == 2 || $request->input("relation") == 3){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（同一住所ではないご家族の方・ドコモ回線なし）";
            }elseif($request->input("relation") == 4 && ($request->input("agent") == 1 || $request->input("agent") == 2)){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（ご家族の方以外・ドコモ回線あり）";
            }elseif($request->input("relation") == 4){
                $memo->condition .= PHP_EOL."来店者：契約者以外の方（ご家族の方以外・ドコモ回線なし）";
            }
        }
        # ポイントカード発行の場合は、契約者のドコモ契約の有無と利用者の情報を追加
        if($id == 12){
            if($request->input("ownDocomo") == 1){
                $memo->condition .= PHP_EOL."ご契約者様の回線・クレジットカード・インターネットのご契約の有無：いずれかのご契約あり";
            }elseif($request->input("ownDocomo") == 2){
                $memo->condition .= PHP_EOL."ご契約者様の回線・クレジットカード・インターネットのご契約の有無：いずれのご契約もなし";
            }
            if($request->input("pointCardUser") == 1){
                $memo->condition .= PHP_EOL."ポイントカードご利用者様：契約者様と同一";
            }elseif($request->input("pointCardUser") == 2){
                $memo->condition .= PHP_EOL."ポイントカードご利用者様：契約者様と同一ではないが、ご来店者様とは同一";
            }elseif($request->input("pointCardUser") == 3){
                $memo->condition .= PHP_EOL."ポイントカードご利用者様：ご契約者様・ご来店者様のいずれとも同一ではない。携帯回線・クレジットカード・インターネットのいずれかのご契約をお持ち。";
            }elseif($request->input("pointCardUser") == 4){
                $memo->condition .= PHP_EOL."ポイントカードご利用者様：ご契約者様・ご来店者様のいずれとも同一ではない。携帯回線・クレジットカード・インターネットのいずれかのご契約をお持ちでない。";
            }
        }
        # MNPの場合は、移転元の契約者に関する情報を追加
        if($id == 1){
            if($request->input("previousContractor") == 1){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様：お乗り換え後の契約者様と同一";
            }elseif($request->input("previousContractor") == 2){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様：お乗り換え後の契約者様とは異なるが、ご住所は同一";
            }elseif($request->input("previousContractor") == 3){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様：お乗り換え後の契約者様のご家族だが、ご住所は異なる";
            }elseif($request->input("previousContractor") == 4){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様：お乗り換え後の契約者様のご家族ではなく、ご住所も異なる";
            }
            if($request->input("previousContractorCome") == 1){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様のご来店：ご来店予定あり";
            }elseif($request->input("previousContractorCome") == 2){
                $memo->condition .= PHP_EOL."お乗り換え前のご利用者様のご来店：ご来店予定なし";
            }
        }
        # 一部の手続きに関しては、未成年限定で注意事項を追加
        # 最初に、保護者の同時来店の有無を記載
        # 対象の手続きは、都度同意かどうかによらないものが1 ~ 4, 18, 21
        # 都度同意の場合だと同意書などが必要になるのが5 ~ 11, 16, 17, 19, 20
        # 最初に、保護者の同時来店の有無をメモに追加
        if(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 1 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->condition .= PHP_EOL."保護者の方の来店：あり。";
        }elseif(Auth::user()->is_corporation != true && $age < 20 && $request->input("parent") == 2 && ($id <= 11 || ($id >= 16 && $id <= 21))){
            $memo->condition .= PHP_EOL."保護者の方の来店：なし。";
        }
        # 同意書などについての諸注意は、memoの最後に追加

        # 紛失・解約の場合はSIMカードの持参可否をメモの最初に追加
        if(($id == 10 || $id == 11) && $request->input("sim") == 1){
            $memo->condition .= PHP_EOL."SIMカードのご持参：可能";
        }elseif($id == 10 || $id == 11){
            $memo->condition .= PHP_EOL."SIMカードのご持参：不可能";
        }

        # nwpwがわかるかどうかもメモに追加
        if($request->input("nwpw") != null && $request->input("nwpw") == 1){
            $memo->condition .= PHP_EOL."ネットワーク暗証番号：分かる";
        }elseif($request->input("nwpw") != null){
            $memo->condition .= PHP_EOL."ネットワーク暗証番号：分からない・不安である（契約者ご本人様のご来店がない場合もこちらになります）";
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
                    $memo->notice .= PHP_EOL."お手続きのされる回線のSIMカードをお持ちいただくようにお願いいたします。".PHP_EOL."また、お手続きの際に、ご契約者様のネットワーク暗証番号・ご住所・ご連絡先番号・ご生年月日のいずれかをお伺いします（契約者ご本人様以外がご来店されている場合には、ネットワーク暗証番号はお伺いしません）。あらかじめご了承ください。";
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
                    
                    $memo->notice .= PHP_EOL."上記のお手続きには、以下のいずれかが必ず必要になります。".PHP_EOL."・お手続きされる回線のSIMカード".PHP_EOL."・ご契約者ご本人様の本人確認書類（運転免許証や健康保険証、個人番号カードなど）".PHP_EOL."・ネットワーク暗証番号（契約者ご本人様のご来店時のみ有効）".PHP_EOL."ご了承ください。";
                    if(isset(Memo::$procedures[($id - 1)]['notice'])){
                        $memo->notice .= PHP_EOL.Memo::$procedures[($id - 1)]['notice'];   
                    }
                    $memo->save();
                    $memo_license = new MemoLicense();
                    $memo_license->memo_id = $memo->id;
                    $memo_license->license_id = 99;
                    $memo_license->save();
                }
            }elseif($id == 12){
                # dポイントカード発行は処理を分岐
                if($request->input("ownDocomo") == 1){
                    $memo->notice .= PHP_EOL."お手続きの際に、ご契約者様のネットワーク暗証番号・ご住所・ご連絡先番号・ご生年月日のいずれかをお伺いします（契約者ご本人様以外がご来店されている場合には、ネットワーク暗証番号はお伺いしません）。".PHP_EOL."あらかじめご了承ください。";
                    $memo->save();
                }elseif($request->input("ownDocomo") == 2){
                    if(Auth::user()->user_licenses->first() != null){
                        $memo->save();
                        foreach(Auth::user()->user_licenses as $user_license){
                            $memo_license = new MemoLicense();
                            $memo_license->memo_id = $memo->id;
                            $memo_license->license_id = $user_license->license_id;
                            $memo_license->save();
                        }
                    }else{
                        $memo->notice .=PHP_EOL."回線・クレジットカード・インターネットいずれのご契約もお持ちでないお客様は上記のお手続きにあたり以下の書類が必ず必要になります。".PHP_EOL."・運転免許証、健康保険証、個人番号カードなどのご本人様確認書類".PHP_EOL."あらかじめご了承ください。";
                        $memo_license = new MemoLicense();
                            $memo_license->memo_id = $memo->id;
                            $memo_license->license_id = 99;
                            $memo_license->save();
                    }
                }
            } else {
                if($id == 4){
                    $memo->condition .= PHP_EOL."分割のご希望：なし。";
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
                        $memo->notice .= PHP_EOL."お手続きの際に、ご契約者様のネットワーク暗証番号・ご住所・ご連絡先番号・ご生年月日のいずれかをお伺いします（契約者ご本人様以外がご来店されている場合には、ネットワーク暗証番号はお伺いしません）。あらかじめご了承ください。";
                    } else {
                        $memo->notice .= PHP_EOL."お手続きの際に、ご契約者様のネットワーク暗証番号・ご住所・ご連絡先番号・ご生年月日のいずれかをお伺いします（契約者ご本人様以外がご来店されている場合には、ネットワーク暗証番号はお伺いしません）。あらかじめご了承ください。";
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
                    if($id == 8 && $request->input("nwpw") == 2){
                        $memo->notice .= PHP_EOL."現在のネットワーク暗証番号がお分かりでない場合にはお手続きをいただくことができません。あらかじめご了承ください。";
                    }else{
                        $memo->notice .= PHP_EOL."ご本人様確認書類をお持ちでない場合には、上記のお手続きを承ることはできかねます。申し訳ありませんがご了承ください。";
                    }
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
                    $memo->notice .= PHP_EOL."お手続きの際に、ご契約者様のネットワーク暗証番号・ご住所・ご連絡先番号・ご生年月日のいずれかをお伺いします。あらかじめご了承ください。";
                    $memo->save();
                }
            }
        }
        # 未成年契約者に対する同意書などについての注意を追加
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

        # 契約者本人が来店しない場合の注意事項の追加・レコードの上書きなど
        if($request->input("comer") == 2){
            if(($id == 4 && $request->input("loan") == 2) || $id == 5 || $id == 7|| ($id >= 18 && $id <= 20)){
                # 家族受付の場合の注意事項
                if($request->input("relation") == 1 && ($request->input("agent") == 1 || $request->input("agent") == 2)){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、追加で必要な書類は特にございません。".PHP_EOL."しかし、ご契約者様とご来店者様のご登録住所が異なる場合にはお手続きができなくなる可能性があります。".PHP_EOL."ご不安であればご契約者様とご来店者様双方のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）をお持ちいただくようにお願いいたします。";
                }elseif($request->input("relation") == 1){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）が必要になります。".PHP_EOL."また、ご契約者様のご登録住所、ご来店者様のご本人様確認書類のものと異なる場合にはお手続きができなくなる可能性があります。".PHP_EOL."ご不安であればご契約者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）を追加でお持ちいただくようにお願いいたします。";
                }elseif(($request->input("relation") == 2 || $request->input("relation") == 3) && ($request->input("agent") == 1 || $request->input("agent") == 2)){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のいずれかが必要になります。".PHP_EOL."・ご来店者様とご契約者様の続柄記載のある住民票や戸籍謄本など".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。";
                }elseif($request->input("relation") == 2 || $request->input("relation") == 3){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の書類が必ず必要になります".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."また、追加で以下のいずれかが必要になります。".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・ご来店者様とご契約者様の続柄記載のある住民票や戸籍謄本など".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。";
                }else{
                    # 通常の代理人受付になる場合
                    if($request->input("agent") <= 2){
                        $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のいずれかが必要になります。".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                    }else{
                        $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                    }
                }
                $memo->save();
            }elseif(($id <= 3 || ($id == 4 && $request->input("loan") == 1)) && $request->input("relation") <= 3 && $request->input("previousContractor") && ($request->input("previousContractor") == null || $request->input("previousContractor") == 1)){
                # 家族限定で通常の代理人受付が可能な場合
                # MNPで契約者が変わらない場合はここに含めるが、変更ありの場合は後に回す。
                if($id <= 3 && $request->input("relation") == 1){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、追加で以下の書類が全て必要になります。".PHP_EOL."・ご来店者様の本人確認書類（運転免許証または個人番号カードまたは健康保険証など。ご契約者様とご来店者様のご住所が同一であることを確認させていただきます）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・ご来店者様の住民票・公共料金領収書など（ご来店者様の運転免許証か個人番号カードをお持ちいただいている場合には不要です。詳しくはショップもしくはインフォメーションセンターにお問い合わせください）".PHP_EOL."上記いずれかをご準備いただくようにお願いいたします。";
                }elseif($id <= 3 && ($request->input("relation") == 2 || $request->input("relation") == 3)){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、追加で以下の書類が全て必要になります。".PHP_EOL."・ご来店者様の本人確認書類（運転免許証または個人番号カードまたは健康保険証など。）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・ご来店者様の住民票・公共料金領収書など（ご来店者様の運転免許証か個人番号カードをお持ちいただいている場合には不要です。詳しくはショップもしくはインフォメーションセンターにお問い合わせください）".PHP_EOL."・住民票・戸籍謄本など、ご契約者様とご来店者様の続柄確認が可能なもの".PHP_EOL."上記いずれかをご準備いただくようにお願いいたします。";
                }elseif(($id == 4 && $request->input("loan") == 1) && $request->input("relation") == 1){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）が必要になります。また、以下のいずれかが必要になります。".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。";
                }elseif(($id == 4 && $request->input("loan") == 1) && ($request->input("relation") == 2 || $request->input("relation") == 3)){
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の2点が必ず必要になります".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・ご来店者様とご契約者様の続柄記載のある住民票や戸籍謄本など".PHP_EOL."また、追加で以下のいずれかが必要になります。".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。";
                }
                $memo->save();
            }elseif($id == 2 || $id == 3 ){
                # 新規系統は家族でないと代理人受付不可なので、既存のレコードを受付不可用のものに切り替える
                if($memo->memo_licenses->first != null){
                    foreach($memo->memo_licenses as $memo_license){
                        $memo_license->delete();
                    }
                }
                if($memo->memo_pays->first != null){
                    foreach($memo->memo_pays as $memo_pay){
                        $memo_pay->delete();
                    }
                }
                if($memo->memo_papers->first != null){
                    foreach($memo->memo_papers as $memo_paper){
                        $memo_paper->delete();
                    }
                }
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
                # メモの中身を上書き
                $memo->notice = PHP_EOL."申し訳ございませんが、上記のお手続きが可能なのは以下の場合に限られます。".PHP_EOL."・契約者ご本人様にご来店いただいた場合".PHP_EOL."・契約者ご本人様とご家族の方にご来店いただいた場合".PHP_EOL."詳しくはショップもしくはインフォメーションセンターにお問い合わせください。";
            }elseif($id == 4 && $request->input("loan") == 1){
                # 分割機種変更は家族受付以外できないので、メモの内容を変更する
                if($memo->memo_licenses->first != null){
                    foreach($memo->memo_licenses as $memo_license){
                        $memo_license->delete();
                    }
                }
                if($memo->memo_pays->first != null){
                    foreach($memo->memo_pays as $memo_pay){
                        $memo_pay->delete();
                    }
                }
                if($memo->memo_papers->first != null){
                    foreach($memo->memo_papers as $memo_paper){
                        $memo_paper->delete();
                    }
                }
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
                # メモの中身を上書き
                $memo->notice = PHP_EOL."申し訳ございませんが、機種変更（分割）のお手続きが可能なのは以下の場合に限られます。".PHP_EOL."・契約者ご本人様にご来店いただいた場合".PHP_EOL."・契約者ご本人様とご家族の方にご来店いただいた場合".PHP_EOL."機種変更（ご一括）であれば、お受付可能な場合もございます。詳しくはショップもしくはインフォメーションセンターにお問い合わせください。";
            }elseif($id == 21 || $id == 22 || $id == 23){
                # dカード系は契約者本人以外の受付不可
                if($memo->memo_licenses->first != null){
                    foreach($memo->memo_licenses as $memo_license){
                        $memo_license->delete();
                    }
                }
                if($memo->memo_pays->first != null){
                    foreach($memo->memo_pays as $memo_pay){
                        $memo_pay->delete();
                    }
                }
                if($memo->memo_papers->first != null){
                    foreach($memo->memo_papers as $memo_paper){
                        $memo_paper->delete();
                    }
                }
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
                # メモの中身を上書き
                $memo->notice = PHP_EOL."申し訳ございませんが、クレジットカードのお手続きが可能なのは以下の場合に限られます。".PHP_EOL."・契約者ご本人様にご来店いただいた場合".PHP_EOL."あらかじめご了承ください。";
            }elseif($id == 6){
                $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の2点が必ず必要になります".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."あらかじめご了承ください。";
            }elseif($id == 8 || $id == 9){
                # nwpwリセットに元々設定されている注意事項は代理人受付時には不要なので追加ではなく上書き
                $memo->notice = PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の書類が必ず必要になります".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."また、追加で以下のいずれかが必要になります。".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。";
            }elseif($id == 17 && $request->input("compensation") == 1){
                # ケー補求償
                if($request->input("agent") == 3){
                    # ドコモ回線なしなら本人確認書類＋委任状
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の2点が必ず必要になります".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・ご契約様にご記入いただいたケータイ補償サービス専用委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."あらかじめご了承ください。";
                }else{
                    # ドコモ回線ありなら委任状のみ
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下の書類が必ず必要になります".PHP_EOL."・ご契約様にご記入いただいたケータイ補償サービス専用委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."あらかじめご了承ください。";
                }
            }elseif($id == 17 && $request->input("compensation") == 2){
                if($request->input("agent") == 3){
                    # ドコモ回線なしなら本人確認書類＋委任状または電話確認
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }else{
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }
            }elseif($id == 11 && $request->input("sim") == 1){
                # 解約・代理人・SIMあり
                if($request->input("agent") == 3){
                    # 代理人がドコモ回線ありなら本体＋委任確認
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }else{
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }
            }elseif($id == 10 && $request->input("sim") == 2){
                # 解約・代理人・SIMなし
                if($memo->memo_licenses->first()->license_id == 99){
                    # 契約者の本人確認書類なしの場合はそもそも受付不可
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になる場合がございます。".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・お手続きされる回線のSIMカード".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."ご来店前にショップまたはインフォメーションセンターにお問い合わせいただくようにお願いいたします。";
                }else{
                    # 契約者の本人確認書類ありの場合は来店者の本人確認書類と委任確認で受付
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【いずれか1点必要になるもの（以下の中から1点お選びください）】".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }
            }elseif($id == 11 && $request->input("sim") == 1){
                # 紛失・代理人・SIMあり
                if($request->input("agent") == 3){
                    # 代理人がドコモ回線ありなら本体＋委任確認
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【ケータイ補償サービスの求償に必要になるもの】".PHP_EOL."・ご契約様にご記入いただいたケータイ補償サービス専用委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."【SIMカードの再発行手続きに必要になるもの】（以下のいずれかをご準備ください）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."また、ご来店者様ご自身の名義のドコモ回線のお電話番号・契約者名・ネットワーク暗証番号・契約者生年月日などをお伺いする場合がございます。あらかじめご了承ください。";
                }else{
                    # 代理人がドコモ回線なしなら本人確認書類＋委任確認
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【ケータイ補償サービスの求償に必要になるもの】".PHP_EOL."・ご契約様にご記入いただいたケータイ補償サービス専用委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."【SIMカードの再発行手続きに必要になるもの】（以下のいずれかをご準備ください）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."あらかじめご了承ください。";
                }
            }elseif($id == 10 && $request->input("sim") == 2){
                # 紛失・代理人・SIMなし
                if($memo->memo_licenses->first()->license_id == 99){
                    # 契約者の本人確認書類なしの場合はそもそも受付不可
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になる場合がございます。".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・お手続きされる回線のSIMカード".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効。通常のものとケータイ補償サービス専用のものがあります。）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."ご来店前にショップまたはインフォメーションセンターにお問い合わせいただくようにお願いいたします。";
                }else{
                    # 契約者の本人確認書類ありの場合は来店者の本人確認書類と委任確認で受付
                    $memo->notice .= PHP_EOL."契約者以外の方がご来店の上で上記のお手続きをいただくにあたり、以下のものが必要になります。".PHP_EOL."【必ず必要になるもの】".PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."【ケータイ補償サービスの求償に必要になるもの】".PHP_EOL."・ご契約様にご記入いただいたケータイ補償サービス専用委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."【SIMカードの再発行手続きに必要になるもの】（以下のいずれかをご準備ください）".PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です）".PHP_EOL."・お手続きの際にショップからご契約者様にお電話確認をさせていただく（お出にならなかった場合にはお手続きをすることはできません）".PHP_EOL."上記いずれかをご準備、もしくは可能な状態にしていただくようにお願いいたします。".PHP_EOL."あらかじめご了承ください。";
                }
            }
            # 家族受付可能な状況で、選択した手続きが故障の場合の注意事項
            if($id == 5 && $request->input("relation") <= 3){
                $memo->notice .= PHP_EOL."ーーご注意！！ーー".PHP_EOL."上記の書類でお受付可能なのは、あくまでも故障（の可能性のある）端末の状態を見させていただくという部分になります。".PHP_EOL."お預かり修理などのお手続きになる場合には委任状などが追加で必要となります。".PHP_EOL."ご不安であればショップまたはインフォメーションセンターにお問い合わせください。";
            }
        }
        # 乗り換えで契約者が変わる場合は、来店者が誰かに関わらず処理を独立させる
        if($id == 1){
            #来店者が家族でない場合は手続きできない
            if($request->input("relation") == 4){
                if($memo->memo_licenses->first != null){
                    foreach($memo->memo_licenses as $memo_license){
                        $memo_license->delete();
                    }
                }
                if($memo->memo_pays->first != null){
                    foreach($memo->memo_pays as $memo_pay){
                        $memo_pay->delete();
                    }
                }
                if($memo->memo_papers->first != null){
                    foreach($memo->memo_papers as $memo_paper){
                        $memo_paper->delete();
                    }
                }
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
                # メモの中身を上書き
                $memo->notice = PHP_EOL."申し訳ございませんが、上記のお手続きが可能なのは以下の場合に限られます。".PHP_EOL."・契約者ご本人様にご来店いただいた場合".PHP_EOL."・契約者ご本人様とご家族の方にご来店いただいた場合".PHP_EOL."詳しくはショップもしくはインフォメーションセンターにお問い合わせください。";
            }
            #移転元契約者が家族でない場合は手続きできない
            if($request->input("previousContractor") == 4){
                if($memo->memo_licenses->first != null){
                    foreach($memo->memo_licenses as $memo_license){
                        $memo_license->delete();
                    }
                }
                if($memo->memo_pays->first != null){
                    foreach($memo->memo_pays as $memo_pay){
                        $memo_pay->delete();
                    }
                }
                if($memo->memo_papers->first != null){
                    foreach($memo->memo_papers as $memo_paper){
                        $memo_paper->delete();
                    }
                }
                $memo_license = new MemoLicense();
                $memo_license->memo_id = $memo->id;
                $memo_license->license_id = 99;
                $memo_license->save();
                # メモの中身を上書き
                $memo->notice = PHP_EOL."申し訳ございませんが、上記のお手続きが可能なのは以下の場合に限られます。".PHP_EOL."・お乗り換えの前後で契約者が同一の場合".PHP_EOL."・お乗り換え前のご契約者様とお乗り換え後のご契約者様がご家族の場合".PHP_EOL."詳しくはショップもしくはインフォメーションセンターにお問い合わせください。";
            }
            # MNPに伴って名義変更する場合の注意事項
            if(($request->input("comer") == 2 && $request->input("relation") <= 3)|| $request->input("previousContractor") >= 2){
                $memo->notice .= PHP_EOL."今回のお手続きにあたり、追加で以下のものが必要になります。";
                if($request->input("comer") == 2){
                    # 契約者が来店しない場合は来店者の本人確認書類と委任状が必要
                    $memo->notice .= PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）".PHP_EOL."・ご来店者様とご契約者様の続柄記載のある住民票や戸籍謄本など".PHP_EOL."・・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です。お手続きをご来店者様に委任する旨をご記入ください）。";
                    # 来店者と契約者が別住所の場合は住民票か戸籍謄本が必要
                    if($request->input("relation") == 2 || $request->input("relation") == 3){
                        $memo->notice .= PHP_EOL."・住民票や戸籍謄本などの、ご来店者様とご契約者様の続柄確認が可能なもの";
                    }
                }
                if($request->input("previousContractor") == 2 || $request->input("previousContractor") == 3 ){
                    $memo->notice .= PHP_EOL."・お乗り換え前のご契約者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）";
                }
                if($request->input("previousContractorCome") == 2){
                    # 譲渡者が来店しない場合は委任状が必要
                    $memo->notice .= PHP_EOL."・お乗り換え前のご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効です。お乗り換えと同時にご名義が変わることにご同意いただける旨をご記入ください）。";
                }
                # 住所が異なる場合には、住民票などで続柄確認が必要
                if($request->input("previousContractor") == 3){
                    $memo->notice .= PHP_EOL."・住民票や戸籍謄本などの、移転元ご契約者様ととお乗り換え後のご契約者様の続柄確認が可能なもの";
                }
                $memo->notice .= PHP_EOL."ご不明点は、ショップもしくはインフォメーションセンターにお問い合わせください。";
            }
        }
        # ポイントカード発行で契約者もしくは利用者が来店できない場合には、注意事項を追加
        if($id == 12){
            if($request->input("comer") == 2 || $request->input("agent") == 3 || $request->input("pointCardUser") >= 2 ){
                $memo->notice .= PHP_EOL."今回のお手続きでは、追加で以下のものが必要になります";
                if($request->input("comer") == 2 || $request->input("pointCardUser") >= 3){
                    $memo->notice .= PHP_EOL."・dポイントクラブ入会／dポイント利用者情報登録に関する同意書（ご記入から3ヶ月後の月末まで有効。ご契約者様・ご利用者様双方のご記入蘭がございます。）";
                }
                if($request->input("comer") == 2){
                    $memo->notice .= PHP_EOL."・ご契約様にご記入いただいた委任状（ご記入から3ヶ月後の月末まで有効。）";
                }
                if($request->input("agent") == 3){
                    $memo->notice .= PHP_EOL."・ご来店者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）";
                }
                if($request->input("pointCardUser") == 4){
                    $memo->notice .= PHP_EOL."・ポイントカードご利用者様のご本人様確認書類（運転免許証・健康保険証・個人番号カードなど）";
                }
            }
            if($request->input("ownDocomo") == 1 || $request->input("agent") <= 2 || $request->input("pointCardUser") <= 2){
                $memo->notice .= PHP_EOL."また、ご契約者様・ご来店者様・ご利用者様の中で携帯電話・クレジットカード・インターネットのいずれかのご契約をお持ちの方について、ご生年月日・ご住所・ご連絡先お電話番号などをお伺いする場合がございます。";
            }
            $memo->notice .= PHP_EOL."あらかじめご了承ください。";
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
