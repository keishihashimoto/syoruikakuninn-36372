@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">お客様情報を登録してください</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-lg-4 col-form-label text-lg-right">お名前</label>

                            <div class="col-lg-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">ご生年月日</label>

                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="input-group input-group-sm col-4 col-sm-6 pr-0">
                                        <input id="year" type="text" class="form-control @error('year') is-invalid @enderror" name="year" value="{{ old('year') }}" required autocomplete="year" autofocus placeholder="西暦">
                                        <div class="input-group-append">
                                            <span class="input-group-text rounded-0">年</span>
                                        </div>
                                    </div>
                                    <div class="input-group input-group-sm col-4 col-sm-3 px-0">
                                        <input id="month" type="text" class="form-control rounded-0 @error('month') is-invalid @enderror" name="month" value="{{ old('month') }}" required autocomplete="month" autofocus>
                                        <div class="input-group-append">
                                            <span class="input-group-text rounded-0">月</span>
                                        </div>
                                    </div>
                                    <div class="input-group input-group-sm col-4 col-sm-3 pl-0">
                                        <input id="date" type="text" class="form-control rounded-0 @error('date') is-invalid @enderror" name="date" value="{{ old('date') }}" required autocomplete="date" autofocus>
                                        <div class="input-group-append">
                                            <span class="input-group-text">日</span>
                                        </div>
                                    </div>
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

                                

                                @if($errors->has("month"))
                                    <div>
                                        @foreach($errors->get("month") as $error)
                                        <div style="list-style: none;" role="alert" class="text-danger birthday-errors">
                                            <strong>{{ $error }}</strong>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif

        

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
                            <label for="email" class="col-lg-4 col-form-label text-lg-right">メールアドレス</label>

                            <div class="col-lg-8">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-lg-4 col-form-label text-lg-right">パスワード</label>

                            <div class="col-lg-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-lg-4 col-form-label text-lg-right">パスワード(確認用)</label>

                            <div class="col-lg-8">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
                        
                        <div class="form-group mb-0 d-flex justify-content-end">
                            <div>
                                <button type="submit" class="btn btn-link">
                                    <i class="far fa-user fa-fw"></i>お客様情報を登録する
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
