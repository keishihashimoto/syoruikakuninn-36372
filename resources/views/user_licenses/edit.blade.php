@extends("layouts.app")

@section("content")

<div class="alert alert-secondary text-center">お持ちのご本人様確認書類を以下の中から全てお選びください</div>

<div class="mx-md-auto" style="max-width: 900px;">
  <div class="container-fluid px-0">
    <form action="{{ route('user_licenses.update') }}" method="POST">
      @csrf
      @method("patch")
      <div class="d-flex flex-wrap row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 px-5">
        @foreach(App\Models\User::$licenses as $license)
          <div class="form-check form-group col">
          @if(is_null(App\Models\UserLicense::where('user_id', Auth::user()->id)->where('license_id', $license['id'])->first()))
              <input type="checkbox" value="{{ $license['id'] }}" name="license_ids[]" id="licence-id-{{ $license['id'] }}" class="form-check-input">
          @else
            <input type="checkbox" checked value="{{ $license['id'] }}" name="license_ids[]" id="licence-id-{{ $license['id'] }}" class="form-check-input">
          @endif
            <label for="license-id-{{ $license['id'] }}" class="form-check-label">{{ $license['name'] }}</label>
          </div>
        @endforeach
      </div>

      <div class="alert alert-danger alert-dismissible fade show">
        <div class="alert-heading h4 text-center" role="alert">ご注意事項<button type="button" class="close" data-dismiss="alert"><span>✖️</span></button><hr></div>
        <div>
          ご本人様確認書類につきましては、お名前・ご住所が正しいものであることを必ずご確認ください。<hr>
          有効期限のあるご本人様確認書類につきましては、有効期限内であることを必ずご確認ください。<hr>
          ご結婚やお引っ越しなどで御名字・ご住所などの変更があり、上記書類が一時的に有効ではなくなってしまった場合には、一度チェックを外してから必要書類をご確認いただくようにお願いいたします。
        </div>
      </div>

      <div class="container ">
        <div class="row row-cols-1 row-cols-md-2">
          <div class="col d-flex justify-content-end justify-content-md-start order-last order-md-first"><a class="btn btn-link text-secondary" href="/users/{{ Auth::user()->id }}"><i class="fas fa-home fa-fw pr-4"></i>お客様情報のページに戻る</a></div>
          <div class="col d-flex justify-content-end"><button type="submit" class="btn btn-link order-first order-md-last"><i class="far fa-save fa-fw pr-4"></i>変更内容を保存する</button></div>
        </div>
      </div>

    </form>
  </div>
</div>


@endsection