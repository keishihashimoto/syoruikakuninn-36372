@extends("layouts.app")

@section("content")

<h2 class="alert alert-secondary text-center">お手続きに必要な書類の<br class="d-inline d-sm-none">ご確認ページです</h2>

<div class="mx-md-auto px-3" style="max-width: 900px;">

  @if($errors->has("procedure-select"))
    <ul>
      @foreach($errors->get("procedure-select") as $error)
        <li style="list-style: none;" class="alert text-danger">{{$error}}</li>
      @endforeach
    </ul>
  @endif

  <div class="px-3 fw-normal">ご希望のお手続きをお選びください</div>
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

    <div id="comer" style="display: none;">
      <div class="fs-2 alert alert-danger">今回のお手続きで契約者ご本人様はご来店されますか？<br class="d-inline d-sm-none">（未選択の場合はご本人様ご来店として扱われます）</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input comer" id="comer-self" name="comer" value="1" checked>
        <label for="comer-self" class="form-check-label">契約者本人は来店する</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input comer" id="comer-not-self" name="comer" value="2">
        <label for="comer-not-self" class="form-check-label">契約者本人は来店しない</label>
      </div>
    </div>

    <div id="ownDocomo" style="display: none;">
      <div class="fs-2 alert alert-danger">契約者ご本人様はご自身名義の回線・dクレジットカード・インターネットのご契約をお持ちですか？</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="ownDocomoYes" name="ownDocomo" value="1">
        <label for="ownDocomoYes" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っている</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="ownDocomoNo" name="ownDocomo" value="2">
        <label for="ownDocomoNo" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っていない</label>
      </div>
    </div>
    
    <div id="pointCardUser" style="display: none;">
      <div class="fs-2 alert alert-danger">ポイントカードのご利用者様について該当するものを以下から一つお選びください<br>（上記にて「回線・クレジットカード・インターネットの契約を持っていない」を選択した場合には、この項目の選択は不要です）。</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="yesAndCome" name="pointCardUser" value="1">
        <label for="yesAndCome" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っており、手続き時に来店する</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="yesAndStay" name="pointCardUser" value="2">
        <label for="yesAndStay" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っているが、手続き時に来店しない</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="noAndCome" name="pointCardUser" value="3">
        <label for="noAndCome" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っていないが、手続き時に来店する</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="noAndStay" name="pointCardUser" value="4">
        <label for="noAndStay" class="form-check-label">ご自身名義の回線・dクレジットカード・インターネットを持っておらず、手続き時に来店しない</label>
      </div>
    </div>

    <div id="relation" style="display: none;">
      <div class="fs-2 alert alert-danger">今回ご来店される方と契約者の方のご関係をお選びください<br class="d-inline d-sm-none">（未選択の場合は「同一住所の方」として扱われます）</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="sameAddress" name="relation" value="1">
        <label for="sameAddress" class="form-check-label">契約者の方と同一住所</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="sameFamilyName" name="relation" value="2">
        <label for="sameFamilyName" class="form-check-label">同一住所ではないが名字は同じ</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="family" name="relation" value="3">
        <label for="family" class="form-check-label">名字・住所ともに異なるが、親族関係にある<br class="d-inline d-sm-none">（住民票などで続柄が証明できる場合にはこちらをお選びください）。</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="notFamily" name="relation" value="4">
        <label for="notFamily" class="form-check-label">名字・住所ともに異なる。親族関係ではない。<br class="d-inline d-sm-none">（住民票などで続柄が証明できない場合にはこちらをお選びください）。</label>
      </div>
    </div>

    <div id="agent" style="display: none;">
      <div class="fs-2 alert alert-danger">今回のお手続きでご来店される方は、ご自身名義の回線のご契約をお持ちですか？<br class="d-inline d-sm-none">（未選択の場合はお持ちでないとして扱われます）</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="agentFamily" name="agent" value="1">
        <label for="agentFamily" class="form-check-label">回線契約があり、今回手続きをする回線とファミリー割引グループ・一括請求グループを両方とも組んでいる。</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="agentDocomo" name="agent" value="2">
        <label for="agentDocomo" class="form-check-label">回線契約があるものの、今回手続きをする回線とはファミリー割引グループ・一括請求グループのどちらかもしくは両方を組んでいない。</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="agentNot" name="agent" value="3">
        <label for="agentNot" class="form-check-label">回線契約を持っていない。</label>
      </div>
    </div>

    @if(Auth::user()->is_corporation == false && $age < 20)
      <div id="parent">
        <div class="fs-2 alert alert-danger">保護者の方が同時にご来店されるかどうかお選びください<br class="d-inline d-md-none">（未選択の場合はご来店なしとして扱われます）</div>
        <div class="form-check form-group px-5">
          <input type="radio" class="form-check-input" id="parent-with" name="parent" value="1" checked>
          <label for="parent-with" class="form-check-label">保護者の来店あり</label>
        </div>
        <div class="form-check form-group px-5">
          <input type="radio" class="form-check-input" id="without" name="parent" value="2">
          <label for="parent-without" class="form-check-label">保護者の方の来店なし</label>
        </div>
      </div>
    @endif
    
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

    <div id="sim" style="display: none;">
      <div class="fs-2 alert alert-danger">今回お手続きされる回線のSIMカードをお持ちいただくことは可能ですか？<br class="d-inline d-sm-none">SIMカードを取り出していただく必要はございません。本体に入ったままで大丈夫です。</div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="sim-ok" name="sim" value="1" checked>
        <label for="sim-ok" class="form-check-label">SIMカードを持参可能</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="sim-ng" name="sim" value="2">
        <label for="sim-ng" class="form-check-label">SIMカードは持参できない</label>
      </div>
    </div>

    <div id="compensation" style="display: none;">
      <div class="fs-2 alert alert-danger">
        <strong>ご注意！！</strong><br>
        紛失に伴いケータイ補償サービスをご利用されるお客様は、「ケータイ補償サービスのお申し込み・お受け取り」ではなく「紛失」をお選びください。<br>
        今回のお手続きは補償サービスのお申し込みとお受け取りのどちらですか？
        <br class="d-inline d-sm-none">以下からお選びください
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="go" name="compensation" value="1" checked>
        <label for="go" class="form-check-label">補償サービスのお申し込み</label>
      </div>
      <div class="form-check form-group px-5">
        <input type="radio" class="form-check-input" id="back" name="compensation" value="2">
        <label for="back" class="form-check-label">補償サービスのお受け取り</label>
      </div>
    </div>

    <div id="nwpw" style="display: none;">
      <div class="fs-6 mt-2 alert alert-danger">ネットワーク暗証番号がお分かりかどうかを以下からお選びください<br class="d-inline d-sm-none">（ご契約者様以外の方がご来店される場合には、選択は不要です）</div>
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
      <div class="fs-6 mt-2 alert alert-danger">お支払い用紙をお持ちかどうかを以下からお選びください</div>
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