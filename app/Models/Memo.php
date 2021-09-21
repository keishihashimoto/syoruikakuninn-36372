<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Error\Notice;

class Memo extends Model
{
    use HasFactory;
    # commit用
    public function memo_licenses(){
        return $this->hasMany("App\Models\MemoLicense");
    }

    public function memo_pays(){
        return $this->hasMany("App\Models\MemoPay");
    }

    public function memo_papers(){
        return $this->hasMany("App\Models\MemoPaper");
    }

    static $procedures = [
        ["id" => 1, "name" => "他社からのお乗り換え", "main_pattern" => 1, "notice" => "お手続きの際には、別途NMP予約番号が必要になります。"],
        ["id" => 2, "name" => "新規契約", "main_pattern" => 1],
        ["id" => 3, "name" => "新規契約（キッズケータイ）", "main_pattern" => 1, "notice" => "お手続きの際には、別途ご利用になるお子様（小学6年生以下）の証明証（健康保険証・学生証・パスポートなど）が1点必要になります。"],
        ["id" => 4, "name" => "機種変更", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 5, "name" => "故障", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 6, "name" => "料金プランの見直し", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 7, "name" => "オプションサービスのご加入・ご解約", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 8, "name" => "ネットワーク暗証番号の変更", "main_pattern" => 3, "sub_pattern" => "1", "notice" => "現在のネットワーク暗証番号がお分かりであれば、上記の書類をお持ちいただかなくてもお手続きが可能です。しかし、万が一入力誤りなどでロックがかかってしまった時に備えて、念のためにお持ちいただくことをお勧めしております。"],
        ["id" => 9, "name" => "spモードパスワードの変更", "main_pattern" => 3, "sub_pattern" => "1"],
        ["id" => 10, "name" => "紛失", "main_pattern" => 2, "sub_pattern" => "2", "notice" =>"ケータイ補償サービスのご利用をご希望の場合には、事前に警察署で受理番号を取得いただく必要がございます。"],
        ["id" => 11, "name" => "解約", "main_pattern" => 2, "sub_pattern" => "3"],
        ["id" => 12, "name" => "dポイントカード発行", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 13, "name" => "料金のお支払い", "main_pattern" => 5],
        ["id" => 14, "name" => "付属品のご購入", "main_pattern" => 6, "notice" => "契約者ご本人様以外の方がご来店された場合、dポイントカードのご提示がなければdポイントの付与ができかねる場合がございます。"],
        ["id" => 15, "name" => "操作のご案内", "main_pattern" => 6],
        ["id" => 16, "name" => "修理品のお受け取り", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 17, "name" => "ケータイ補償サービスのお受け取り", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 18, "name" => "ドコモ光のお申し込み", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 19, "name" => "ドコモ光のご解約", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 20, "name" => "ドコモ光のご契約内容の変更", "main_pattern" => 2, "sub_pattern" => "1"],
        ["id" => 21, "name" => "dカードのお申し込み", "main_pattern" => 7],
        ["id" => 22, "name" => "dカードGoldのお申し込み", "main_pattern" => 7],
        ["id" => 23, "name" => "dカードのアップグレード", "main_pattern" => 2, "sub_pattern" => "1"],
    ];

    public function user(){
        return $this->belongsTo("App\Models\User");
    }
}
