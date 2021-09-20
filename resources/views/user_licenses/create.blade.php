@extends("layouts.app")

@section("content")

<div class="container-fluid px-0">
  <div class=" alert alert-secondary text-center">
    <strong style="font-size: 20px;">ご本人様確認書類の登録ページです</strong><hr>お持ちのご本人様確認書類を以下から全てお選びください
  </div>

  <div class="mx-md-auto" style="max-width: 900px;">
    <form method="post" action="{{ route('user_licenses.store') }}">
      @csrf
        
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 px-5">
        @foreach(App\Models\User::$licenses as $license)
        <div class="form-group form-check">
          <input type="checkbox" value="{{ $license['id']  }}" id="license-{{ $license['id'] }}" name="licenses[]" class="form-check-input">
          <label for="license-{{ $license['id'] }}" class="form-check-label">{{ $license['name'] }}</label>
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
          <div class="col d-flex justify-content-end justify-content-md-start order-last order-md-first"><a class="btn btn-link text-secondary" href="/users/{{ Auth::user()->id }}"><i class="fas fa-home fa-fw pr-4"></i>書類を登録せずにトップページに移動する</a></div>
          <div class="col d-flex justify-content-end order-first order-md-last"><button type="submit" class="btn btn-link"><i class="far fa-save fa-fw pr-4"></i>お持ちのご本人様確認書類を登録する</button></div>
        </div>
      </div>
    
    </form>
  </div>
  

</div>

@endsection