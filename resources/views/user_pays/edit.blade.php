@extends("layouts.app")

@section("content")

<div class="container-fluid px-0">
  <div class="text-center alert alert-secondary h3">お支払い方法の変更画面です</div>

  <div class="px-4 text-center">設定可能なお支払い方法をお選びください</div>
  <div class="alert alert-warning text-center">ご本人様のものかつ、有効期限内のもののみをお選びください</div>

  <div class="mx-md-auto" style="max-width: 900px;">
    <form action="/user_pays/update" method="POST">
      @csrf
      @method("patch")
      
      <div class="row row-cols-2 row-cols-md-3 px-5">
        @foreach(App\Models\UserPay::$pays as $pay)
        <div class="form-check pb-2">
          @if(is_null(App\Models\UserPay::where("user_id", Auth::user()->id)->where("pay_id", $pay['id'])->first()))
          <input type="checkbox" class="form-check-input" name="pay_ids[]" value="{{ $pay['id'] }}" id="pay-id-{{ $pay['id'] }}">
          @else
            <input type="checkbox" checked class="form-check-input" name="pay_ids[]" value="{{ $pay['id'] }}" id="pay-id-{{ $pay['id'] }}">
          @endif
          <label for="pay-id-{{ $pay['id'] }}" class="form-check-label">{{ $pay['name'] }}</label>
        </div>
        @endforeach
      </div>

      <div class="alert alert-secondary alert-dismissible fade show">
        <div class="alert-heading h4 text-center" role="alert">ご注意事項<button type="button" class="close" data-dismiss="alert"><span>✖️</span></button><hr></div>
        <div>
          この画面でお支払い方法を設定いただくことにより、当サイトでお支払いが発生することはございません。<hr>ドコモショップでのお手続きの際に、内容によってはお支払い設定が可能なものをお持ちいただく必要があるため、その際の書類確認のためにご登録いただくことをお勧めしております。<hr>この画面でのご登録は必須ではありません。必要に応じてご利用ください。
        </div>
      </div>

      <div class="container">
        <div class="row row-cols-1 row-cols-md-2">
          <div class="col d-flex justify-content-end justify-content-md-start order-last order-md-first"><a class="btn btn-link text-secondary" href="/users/{{ Auth::user()->id }}"><i class="fas fa-home fa-fw pr-4"></i>お客様情報のページに戻る</a></div>
          <div class="col d-flex justify-content-end order-first order-md-last"><button type="submit" class="btn btn-link"><i class="far fa-save fa-fw pr-4"></i>お支払い方法を変更する</button></div>
        </div>
      </div>

    </form>
  </div>
</div>

@endsection