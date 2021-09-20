<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        "birthday",
        "is_corporation"
    ];

    static $licenses = [
        ["id" => 1, "name" => "運転免許証"],
        ["id" => 2, "name" => "運転経歴証明書"],
        ["id" => 3, "name" => "個人番号カード"],
        ["id" => 4, "name" => "住民基本台帳カード"],
        ["id" => 5, "name" => "健康保険証"]
    ];

    public function user_licenses(){
        return $this->hasMany("App\Models\UserLicense");
    }

    public function user_pays(){
        return $this->hasMany("App\Models\UserPay");
    }

    public function user_papers(){
        return $this->hasMany("App\Models\UserPaper");
    }

    public function memos(){
        return $this->hasMany("App\Models\Memo");
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // hasManyで所有しているインスタンスの削除
    public static function boot(){
        parent::boot();

        static::deleting(function ($user){
            $user->user_licenses()->delete();
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
