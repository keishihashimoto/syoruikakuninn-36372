<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = "/user_licenses/create";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            "year" => ["required", "integer", "min:1800"],
            "month" => ["required", "integer", "min:1", "max:12"],
            "date" => ["required", "integer", "min:1", "max:31"]
        ],[
            "name.required" => "お名前を入力してください",
            "name.string" => "お名前に不適切な文字が使用されています",
            "name.max" => "お名前は255文字以下で入力してください",
            "email.required" => "メールアドレスを入力してください",
            "email.string" => "メールアドレスに不適切な文字が使用されています",
            "email.max" => "メールアドレスは255文字以下で入力してください",
            "email.unique" => "このメールアドレスはすでに使用されています",
            "password.required" => "パスワードが入力されていません",
            "password.string" => "パスワードに不適切な文字が使用されています",
            "password.min" => "パスワードには最低でも8文字以上が必要です",
            "password.confirmed" => "パスワードが一致しません",
            "year.required" => "お生まれになった年が入力されていません",
            "year.integer" => "お生まれになった年は数字で入力してください",
            "year.min" => "お生まれになった年は4桁の数字で入力してください",
            "month.required" => "お生まれになった月が入力されていません",
            "month.integer" => "お生まれになった月は数字で入力してください",
            "month.min" => "お生まれになった月は1 ~ 12で入力してください",
            "month.max" => "お生まれになった月は1 ~ 12で入力してください",
            " date.required" => "お生まれになった日が入力されていません",
            "date.integer" => "お生まれになった日は数字で入力してください",
            "date.min" => "お生まれになった日は1 ~ 31で入力してください",
            "date.max" => "お生まれになった日は1 ~ 31で入力してください"
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            "birthday" => "{$data['year']}-{$data['month']}-{$data['date']}"
        ]);
    }
}
