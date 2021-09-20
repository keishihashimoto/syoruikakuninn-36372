@extends("layouts.app")

@section("content")

<div class="text-center alert alert-secondary h3">お持ちの補助書類の登録画面です</div>

<div class="px-4 text-center">お持ちの補助書類を全てお選びください</div>
<div class="alert alert-warning text-center">指定がある場合を除き、ご本人様のものかつ発行から3ヶ月以内のもののみをお選びください</div>

<div class="mx-md-auto" style="max-width: 900px;">

  <form action="/user_papers/update" method="POST">
    @csrf
    @method("patch")

    <div class="d-flex flex-wrap">

      @foreach(App\Models\UserPaper::$papers as $paper)
      <div class="form-check form-group px-5">
        @if(is_null(App\Models\UserPaper::where("user_id", Auth::user()->id)->where("paper_id", $paper['id'])->first()))
        <input type="checkbox" class="form-check-input" name="paper_ids[]" value="{{ $paper['id'] }}" id="paper-id-{{ $paper['id'] }}">
        @else
        <input type="checkbox" checked class="form-check-input" name="paper_ids[]" value="{{ $paper['id'] }}" id="paper-id-{{ $paper['id'] }}">
        @endif
        <label for="paper-id-{{ $paper['id'] }}" class="form-check-label">{{ $paper['name'] }}</label>
      </div>
      @endforeach
    </div>

    <div class="alert alert-secondary alert-dismissible fade show">
      <div class="alert-heading h4 text-center" role="alert">ご注意事項<button type="button" class="close" data-dismiss="alert"><span>✖️</span></button><hr></div>
      <div>
      補助書類とは、住民票や公共料金の領収証などの書類のことを指します。<hr>補助書類のご登録は必須ではございませんが、お持ちの本人確認書類の種類によっては、お手続きの際に補助書類が必要になる可能性がございます。必要に応じてご登録ください。
      </div>
    </div>

    <div class="container ">
        <div class="row row-cols-1 row-cols-md-2">
          <div class="col d-flex justify-content-end justify-content-md-start order-last order-md-first"><a class="btn btn-link text-secondary" href="/users/{{ Auth::user()->id }}"><i class="fas fa-home fa-fw pr-4"></i>お客様情報のページに戻る</a></div>
          <div class="col d-flex justify-content-end order-first order-md-last"><button type="submit" class="btn btn-link"><i class="far fa-save fa-fw pr-4"></i>お持ちの補助書類を登録する</button></div>
        </div>
      </div>

  </form>
</div>

@endsection