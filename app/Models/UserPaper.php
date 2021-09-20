<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaper extends Model
{
    use HasFactory;

    static $papers = [
        ["id" => 1, "name" => "住民票"],
        ["id" => 2, "name" => "公共料金領収証"],
        ["id" => 3, "name" => "公共料金領収証（同一住所のご家族名義のもの）"]
    ];

    public function user(){
        $this->belongsTo("App\Models\User");
    }
}
