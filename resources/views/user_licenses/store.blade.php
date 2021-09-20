@extends("layouts.app")

@section("content")
<div class="display-5">お持ちのご本人様確認書類の登録が完了しました</div>
<a class="btn btn-outline-danger" href="{{ route('user_pays.create') }}">お支払い方法の設定画面に進む</a>
<a class="btn btn-outline-info" href="users/{{ Auth::user()->id }}">お客様情報の確認ページに戻る</a>
@endsection