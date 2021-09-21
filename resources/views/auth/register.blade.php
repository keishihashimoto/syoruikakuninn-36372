@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">お名前</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="birthday" class="col-md-4 col-form-label text-md-right">ご生年月日<i> (全て半角数字で入力してください)</i></label>

                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror" name="year" value="{{ old('year') }}" required autocomplete="year" placeholder="西暦で入力してください" autofocus>
                                    <div class="p-2">年</div>
                                </div>

                                @if($errors->has("year"))
                                <div>
                                    @foreach($errors->get("year") as $error)
                                    <div style="list-style: none;" role="alert" class="text-danger birthday-errors">
                                        <strong>{{ $error }}</strong>
                                    </div>
                                    @endforeach
                                </div>   
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <input id="month" type="text" class="form-control @error('month') is-invalid @enderror" name="month" value="{{ old('month') }}" required autocomplete="month" placeholder="半角数字で入力してください" autofocus>
                                    <div class="p-2">月</div>
                                </div>

                                @if($errors->has("month"))
                                    <div>
                                        @foreach($errors->get("month") as $error)
                                        <div style="list-style: none;" role="alert" class="text-danger birthday-errors">
                                            <strong>{{ $error }}</strong>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <input id="date" type="text" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date') }}" required autocomplete="date" placeholder="半角数字で入力してください" autofocus>
                                    <div class="p-2">日</div>
                                </div>

                                @if($errors->has("date"))
                                <div>
                                    @foreach($errors->get("date") as $error)
                                    <div style="list-style: none;" role="alert" class="text-danger birthday-errors">
                                        <strong>{{ $error }}</strong>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">メールアドレス</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">パスワード</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">パスワードをもう一度後入力ください</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
