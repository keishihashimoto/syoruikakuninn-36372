@extends("layouts.app")

@section("content")

<div>ご本人様確認書類の変更が完了しました</div>

<a class="btn btn-outline-primary" href="/users/{{ Auth::user()->id }}">お客様情報の確認ページに戻る</a>

@endsection