<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPay extends Model
{
    use HasFactory;

    static $pays = [
        ["id" => 1, "name" => "クレジットカード"],
        ["id" => 2, "name" => "キャッシュカード"],
        ["id" => 3, "name" => "通帳 + 口座の届出印"]
    ];

    public function user(){
        return $this->belongsTo("App\Models\User");
    }
}
