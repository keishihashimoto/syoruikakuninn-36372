@extends("layouts.app")

@section("content")
<div class="alert text-center alert-secondary">お手続きメモの確認ページです</div>

<div class="mx-md-auto" style="max-width: 900px;">

  <div class="container">
    <div class="card shadow-sm">
      <div class="card-title text-center h4 py-2 bg-light mb-0">お手続きメモ</div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-sm-flex justify-content-sm-between">
            <div>作成日時</div>
            <div class="text-right">{{ $memo->created_at->format("Y年n月j日") }}</div>
          </li>
          <li class="list-group-item d-sm-flex justify-content-sm-between">
            <div>お手続き内容</div>
            <div class="text-right">{{ App\Models\Memo::$procedures[($memo->procedure_id - 1)]['name'] }}</div>
          </li>
          
          @if(is_null(App\Models\MemoLicense::where("memo_id", $memo->id)->where("license_id", 99)->first()))
            <li class="list-group-item">
              <div>お手続きに必要な書類</div>
              <div>
                @if(count($memo->memo_licenses) === 0)
                  <div>特にお持ちいただく書類はありません</div>
                @else
                  <div class="text-right" style="font-size: 10px;">以下の中からいずれか一つをお持ちください</div>
                  <div class="text-right" style="font-size: 10px;">契約者ご本人様のものをお持ちください</div>
                  <div class="d-flex flex-wrap pt-2">
                    @foreach($memo->memo_licenses as $memo_license)
                      <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ App\Models\User::$licenses[($memo_license->license_id - 1)]['name'] }}</div>
                    @endforeach
                  </dvi>
                @endif
              </div>
            </li>
          @else
            <li class="list-group-item d-sm-flex justify-content-sm-between">
              <div>お手続きの可否</div>
              <div>申し訳ございませんが現在ご登録いただいている書類ではこのお手続きを承ることは致しかねます。</div>
            </li>
          @endif
          
          @if($memo->memo_pays->first() != null)
            <li class="list-group-item">
              <div>お支払い設定に必要なもの</div>
              <div class="text-right" style="font-size: 10px;">以下の中からいずれか一つをお持ちください</div>
              <div class="text-right" style="font-size: 10px;">契約者ご本人様のものをお持ちください</div>
              <div class="d-flex flex-wrap pt-2">
                @foreach($memo->memo_pays as $memo_pay)
                  <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ App\Models\UserPay::$pays[($memo_pay->pay_id - 1)]['name'] }}</div>
                @endforeach
              </div>
            </li>
          @endif

          @if($memo->memo_papers->first() != null)
            <li class="list-group-item">
              <div>お手続きに必要な補助書類</div>
              <div class="text-right" style="font-size: 10px;">以下の中からいずれか一つをお持ちください</div>
              <div class="text-right" style="font-size: 10px;">契約者ご本人様のものをお持ちください</div>
              <div class="d-flex flex-wrap pt-2">
                @foreach($memo->memo_papers as $memo_paper)
                  <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ App\Models\UserPaper::$papers[($memo_paper->paper_id - 1)]["name"] }}</div>
                @endforeach
              </div>
            </li>
          @endif

          @if($memo->condition !== null)
            <li class="list-group-item">
              <div><strong>備考</strong></div>
              <div class="pl-2">{!! nl2br(e($memo->condition)) !!}</div>
            </li>
          @endif

          @if($memo->notice !== null)
            <li class="list-group-item">
              <div style="margin-bottom: -18px;"><strong>ご注意事項</strong></div>
              <div class="pl-2">{!! nl2br(e($memo->notice)) !!}</div>
            </li>    
          @endif
        </ul>
      </div>
    
      <div class="row row-cols-1">
        <div class="cols d-flex justify-content-end pr-4 pt-2">
          <a class="btn btn-link" href="/users/{{ Auth::user()->id}}"><i class="far fa-user fa-fw"></i>お客様情報のページに戻る</a>
        </div>
        <div class="cols d-flex justify-content-end pr-4">
          <form action="/memos/{{ $memo->id }}" method="POST">
          @csrf
          @method("delete")
            <button type="submit" class="btn btn-link text-danger"><i class="fas fa-trash fa-fw"></i>メモを削除する</button>
          </form>
        </div>
      </div>
    
    </div>
  

  </div>


</div>

@endsection