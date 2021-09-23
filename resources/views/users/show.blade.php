@extends("layouts.app")

@section("content")
<h2 class="alert alert-secondary text-center">お客様情報の<br class="d-inline d-sm-none">ご確認ページです</h2>

<div style="max-width: 788px;" class="mx-md-auto">

  <div class="container">
    <div class="card shadow-sm">
      <div class="text-center h2 pt-2 pb-1 bg-light mb-0 pt-sm-3 pb-sm-2">プロフィール</div>
      <ul class="list-group list-group-flush">
        <li class="d-flex justify-content-between px-4 list-group-item">
          <div class="pl-sm-4" style="font-size: 20px;">お名前</div>
          <div class="pr-sm-4"><span style="font-size: 20px;">{{ $user->name }}</span>  様</div>
        </li>
        <li class="d-flex justify-content-between px-4 list-group-item">
          <div class="pl-sm-4" style="font-size: 20px;">お客様の属性</div>
          @if($user->is_corporation)
          <div class="pr-sm-4" style="font-size: 20px;"><i class="far fa-building fa-fw"></i>法人名義</div>
          @else
          <div class="pr-sm-4" style="font-size: 20px;"><i class="far fa-user fa-fw"></i>個人名義</div>
          @endif
        </li>
        @if($user->is_corporation == false)
        <li class="d-flex justify-content-between px-4 list-group-item">
          <div class="pl-sm-4" style="font-size: 20px;">ご生年月日</div>
          <div class="pr-sm-4" style="font-size: 20px;">{{  $user->birthday }}</div>
        </li>
        @endif
        <li class="list-group-item d-flex justify-content-end pt-1 pb-1">
          <button type="button" class="btn btn-link"><a href="{{ route('users.edit', $user) }}"><i class="far fa-edit fa-fw"></i>お客様情報を編集する</a></button>
        </li>
      </ul>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="text-center h2 pt-2 pb-1 bg-light mb-0">お手持ちのご本人様確認書類一覧</div>
      <div class="d-flex justify-content-between align-items-center pl-2">
        <div class="d-flex flex-wrap py-2 pl-sm-4">
          @if((Auth::user()->user_licenses->first()) == null)
          現在ご登録いただいているご本人様確認書類は特にございません
          @else
            @foreach($user->user_licenses as $user_license)
            <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ App\Models\User::$licenses[($user_license->license_id - 1)]["name"] }}</div>
            @endforeach
          @endif
        </div>
        <div class="d-flex justify-content-end pt-1 pb-1 pr-sm-4">
          <button type="button" class="btn btn-link d-inline-block " style="width: 130px;"><a href="{{ route('user_licenses.edit') }}"><i class="far fa-edit fa-fw"></i>変更<span class="d-none d-md-inline">する</span></a></button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="text-center h2 pt-2 pb-1 bg-light mb-0">ご登録のお支払い方法一覧</div>
      <div class="d-flex justify-content-between align-items-center pl-2">
        <div class="d-flex flex-wrap py-2 pl-sm-4">
          @if(is_null(Auth::user()->user_pays->first()))
          現在ご登録いただいているお支払い方法はありません
          @else
            @foreach(App\Models\UserPay::$pays as $pay)
              @if(is_null(App\Models\UserPay::where("user_id", Auth::user()->id)->where("pay_id", $pay['id'])->first()))
              @else
                <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ $pay['name'] }}</div>
              @endif
            @endforeach
          @endif
        </div>
        <div class="d-flex justify-content-end pt-1 pb-1 pr-sm-4">
          <button type="button" class="btn btn-link d-inline-block" style="width: 130px;"><a href="{{ route('user_pays.edit') }}"><i class="far fa-edit fa-fw"></i>変更<span class="d-none d-md-inline">する</span></a></button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mt-4">
      <div class="text-center h2 pt-2 pb-1 bg-light mb-0">ご登録の補助書類一覧</div>
      <div class="d-flex justify-content-between align-items-center pl-2">
        <div class="d-flex flex-wrap py-2 pl-sm-4">
          @if(is_null(Auth::user()->user_papers->first()))
          現在ご登録いただいている補助書類はありません
          @else
            @foreach(App\Models\UserPaper::$papers as $paper)
              @if(is_null(App\Models\UserPaper::where("user_id", Auth::user()->id)->where("paper_id", $paper['id'])->first()))
              @else
                <div class="mr-3 my-1 badge badge-gray" style="font-size: 14px;">{{ $paper['name'] }}</div>
              @endif
            @endforeach
          @endif
        </div>
        <div class="d-flex justify-content-end pt-1 pb-1 pr-sm-4">
          <button type="button" class="btn btn-link d-inline-block" style="width: 130px;"><a href="{{ route('user_papers.edit') }}"><i class="far fa-edit fa-fw"></i>変更<span class="d-none d-md-inline">する</span></a></button>
        </div>
      </div>
    </div>
    
    <table class="table mt-4 font-12">
      <thead>
        <tr>
          <th colspan="2"  id="memo">ご登録いただいているメモ一覧</th>
        </tr>
      </thead>
      <tr>
        <th>作成日時</th>
        <th>お手続き内容</th>
      </tr>
      @foreach(Auth::user()->memos as $memo)
      <tr>
        <td class="align-middle">{{ $memo->created_at->format("y/m/d") }}</td>
        <td class="d-sm-flex justify-content-sm-between align-items-center">
          <div class="py-2 py-sm-4 memo-name" id="{{ $memo->id }}"><strong>{{ App\Models\Memo::$procedures[($memo->procedure_id - 1)]['name'] }}</strong></div>
          <div class="row row-cols-2 row-cols-sm-1">
            <a class="btn btn-link d-block col text-right pr-4 pb-0" href="/memos/{{$memo->id}}"><i class="fas fa-link fa-fw"></i>詳細</a>
            <form method="POST" action="/memos/{{ $memo->id }}" class="col text-right">
              @csrf
              @method("delete")
              <button type="submit" class="btn btn-link text-danger pb-0 delete-memo"><i class="far fa-trash-alt fa-fw"></i>削除</button>
            </form>
          </div>
        </td>
      </tr>
      @endforeach
      @if(Auth::user()->memos->first() == null)
      <tr>
        <td colspan="2">現在ご登録いただいているメモはありません</td>
      </tr>
      @endif
      <tr>
        <td colspan="2">
          <div class="d-flex justify-content-end">
            <a class="btn btn-link" href="{{ route('memos.create') }}"><i class="fas fa-info-circle fa-fw"></i>お手続きに必要な書類を確認する</a>
          </div>
        </td>
      </tr>
    </table>

    <div class="px-3 row row-cols-1 row-cols-sm-2">
      <div class="col d-flex justify-content-end justify-content-sm-start">
        <a class="btn btn-link" href="{{ route('home') }}"><i class="fas fa-arrow-left"></i>トップページに戻る</a>
      </div>
      <div class="col d-flex justify-content-end">
        <form action="{{ route('users.destroy', $user) }}" method="post">
          @csrf
          @method("DELETE")
          <button type="submit" class="btn btn-link text-danger">
            <i class="far fa-trash-alt fa-fw"></i>お客様情報を削除する
          </button>

        </form>
      </div>
    </div>
  </div>

</div>

@endsection