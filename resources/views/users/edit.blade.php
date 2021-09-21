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

                            <div class="col-lg-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ Auth::user()->name }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="birthday" class="col-lg-4 col-form-label text-lg-right">ご生年月日</label>

                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror" name="year" value="{{ date('Y', strtotime($user->birthday)) }}" required autocomplete="year" placeholder="西暦で入力してください" autofocus>
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
                                    <input id="month" type="text" class="form-control @error('month') is-invalid @enderror" name="month" value="{{ date('n', strtotime($user->birthday)) }}" required autocomplete="month" placeholder="半角数字で入力してください" autofocus>
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
                                    <input id="date" type="text" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ date('j', strtotime($user->birthday)) }}" required autocomplete="date" placeholder="半角数字で入力してください" autofocus>
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