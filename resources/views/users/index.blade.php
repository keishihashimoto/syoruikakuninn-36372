@extends("layouts.app")

@section("content")
<h1>トップページです</h1>
<div class="fs3">こんにちは{{ Auth::user()->name }}さん</div>
<a class="btn btn-outline-primary" href="users/{{Auth::user()->id}}">お客様情報の確認ページ</a>
@endsection