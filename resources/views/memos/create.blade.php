@extends("layouts.app")

@section("content")

<h2 class="alert alert-secondary text-center">お手続きに必要な書類の<br class="d-inline d-sm-none">ご確認ページです</h2>

<div class="mx-md-auto" style="max-width: 900px;">

  @if($errors->has("procedure-select"))
    <ul>
      @foreach($errors->get("procedure-select") as $error)
        <li style="list-style: none;">{{$error}}</li>
      @endforeach
    </ul>
  @endif

  <div class="px-3 fw-normal">ご希望のお手続きを以下の中から選択してください</div>
  <form action="{{ route('memos.store') }}" method="post">
    @csrf
    <div class="form-group">
      <select class="form-control" id="procedure-select" name="procedure-select">
        <option value="未選択">選択してください</option>
        @foreach(App\Models\Memo::$procedures as $procedure)
          <option value="{{ $procedure['id'] }}">
            {{ $procedure['name'] }}
          </option>
        @endforeach
      </select>
    </div>

    <div id="loan" style="display: none;">
      <div class="fs-2 alert alert-danger">機種のご購入方法を以下からお選びください<br class="d-inline d-sm-none">（未選択の場合は分割をご希望として扱われます）</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="loan-on" name="loan" value="1">
        <label for="loan-on" class="form-check-label">機種の分割購入を希望する</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="loan-off" name="loan" value="2">
        <label for="loan-off" class="form-check-label">機種の分割購入を希望しない(一括で購入する)</label>
      </div>
    </div>

    <div id="nwpw" style="display: none;">
      <div class="fs-6 mt-2 alert alert-danger">ネットワーク暗証番号がお分かりかどうかを以下からお選びください<br class="d-inline d-sm-none">（未選択の場合は番号がお分かりでないとして扱われます）</div>
      <div>
        <div class="form-check form-group px-5">
          <input type="radio" class="form-check-input" name="nwpw" value="1" id="nwpw-ok">
          <label for="nwpw-ok" class="form-check-label">ネットワーク暗証番号がわかる</label>
        </div>
        <div class="form-check form-group px-5">
          <input type="radio" class="form-check-input" name="nwpw" value="2" id="nwpw-ng">
          <label for="nwpw-ng" class="form-check-label">ネットワーク暗証番号が不安である・わからない</label>
        </div>
      </div>
    </div>

    <div id="payment-form" style="display: none;">
      <div class="fs-6 mt-2 alert alert-danger">お支払い用紙をお持ちかどうかを以下からお選びください<br class="d-inline d-sm-none">（未選択の場合は用紙をお持ちではないとして扱われます）</div>
      <div>
        <div class="form-check form-group px-5">
          <input type="radio" id="payment-form-ok" name="payment-form" value="1" class="form-check-input">
          <label for="payment-form-ok" class="form-check-label">お支払い用紙がある</label>
        </div>
        <div class="form-check form-group px-5">
          <input type="radio" id="payment-form-ng" name="payment-form" value="2" class="form-check-input">
          <label for="payment-form-ng" class="form-check-label">お支払い用紙がない</label>
        </div>
      </div>
    </div>

    <div id="student"  style="display: none;">
      <div class="fs-2 alert alert-danger mx-0">学生の方かそうでないかを選択してください<br class="d-inline d-sm-none">（未選択の場合には、学生の方ではないとみなされます）</div>
      <div class="form-check form-group px-5">
        <input type="radio" id="is_student" name="student" value="1" class="form-check-input">
        <label for="is_student" class="form-check-label">学生である</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" id="not_student" name="student" value="2" class="form-check-input">
        <label for="not_student" class="form-check-label">学生ではない</label>
      </div>
    </div>
    
    <div class="container ">
      <div class="row row-cols-1 row-cols-md-2">
      <div class="col d-flex justify-content-end justify-content-md-start order-last order-md-first"><a class="btn btn-link text-secondary" href="/users/{{ Auth::user()->id }}"><i class="fas fa-home fa-fw pr-4"></i>お客様情報のページに戻る</a></div>
        <div class="col d-flex justify-content-end order-first order-md-last"><button type="submit" class="btn btn-link"><i class="fas fa-info-circle fa-fw"></i>お手続きに必要な書類を確認する</button></div>
      </div>
    </div>

  </form>
</div>
@endsection