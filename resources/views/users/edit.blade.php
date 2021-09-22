@extends("layouts.app")

@section("content")
<h1 class="text-center alert alert-secondary">お客様情報の編集ページです</h1>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">お客様情報を登録してください</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method("patch")

                        <div class="form-group row">
                            <label for="name" class="col-lg-4 col-form-label text-lg-right">お名前</label>

                            <div class="col-lg-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label text-lg-right">ご生年月日</label>

                            <div class="container col-lg-8 mx-lg-0">
                                <div class="row px-3">
                                    <div class="col-4 col-sm-6 input-group input-group-sm p-0">
                                        <input class="form-control" type="text" name="year" value="{{ old('year',date('Y', strtotime($user->birthday))) }}" required autocomplete="year" autofocus placeholder="西暦">
                                        <div class="input-group-append">
                                            <span class="input-group-text rounded-0">年</span>
                                        </div>
                                    </div>
                                
                                    <div class="col-4 col-sm-3 input-group input-group-sm p-0">
                                        <input class="form-control rounded-0" type="text" name="month" value="{{ old('month', date('n', strtotime($user->birthday))) }}" required autocomplete="month" autofocus>
                                        <div class="input-group-append">
                                            <span class="input-group-text rounded-0">月</span>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 input-group input-group-sm p-0">
                                        <input class="form-control rounded-0" type="text" name="date" value="{{ old('date', date('j', strtotime($user->birthday))) }}" required autocomplete="date" autofocus>
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
                        
                        
                        <div class="form-group mb-0 row row-cols-1 pr-2">
                            <div class="col text-right">
                                <button type="submit" class="btn btn-link">
                                    <i class="far fa-user fa-fw"></i>お客様情報を登録する
                                </button>
                            </div>
                            <div class="col text-right">
                                <button type="button" class="btn btn-link text-secondary"><i class="fas fa-long-arrow-alt-left fa-fw"></i><a href="/users/{{ Auth::user()->id }}" class="text-secondary">お客様情報のページに戻る</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection