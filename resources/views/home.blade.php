@extends('layouts.app')

@section('content')
<div class="text-center pt-3 h5 alert alert-secondary">スマートフォンから<br class="d-inline d-sm-none">書類がご確認頂けるようになりました</div>
<div class="container">
    
        <!--アイキャッチ画像 -->
    
    <div class="carousel slide" data-ride="carousel" id="carousel">
        <div class="carousel-inner">
            <div class="carousel-item" data-interval="3000">
                <div class="card mx-1 shadow text-center">
                    <img class="img-fluid" src="/images/5142200_l.jpg">
                    <div class="alert alert-secondary mb-0 rounded-0">ご来店前の書類確認にご協力ください</div>
                </div>
            </div>
            <div class="carousel-item active" data-interval="3000">
                <div class="card mx-1 shadow text-center">
                    <img class="img-fluid" src="/images/licenses.jpg">
                    <div class="alert alert-secondary mb-0 rounded-0">お手続きに必要な書類を自動で表示</div>
                </div>
            </div>
            <div class="carousel-item" data-interval="3000">
                <div class="card mx-1 shadow text-center">
                    <img class="img-fluid" src="/images/smartphone_check_3.jpg">
                    <div class="alert alert-secondary mb-0 rounded-0">一度確認した内容は後からでも表示可能</div>
                </div>    
            </div>
            <a href="#carousel" role="button" class="carousel-control-prev" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" role="button" href="#carousel" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="h4 card-header text-center pt-2 alert alert-secondary mb-1" role="alert">ご注意事項</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">特に指定がある場合を除き、ご本人様確認書類は現住所記載かつ有効期限内であること、補助書類は発行から3ヶ月以内である必要がございます。</li>
            <li class="list-group-item">作成されるメモは、メモ作成時点でご登録いただいている書類・お客さま情報に基づくものになります。</li>
            <li class="list-group-item">現在、当サイトでは未成年契約者の方のお手続きには対応していません。あらかじめご了承ください。</li>
            <li class="list-group-item">ご不明な点につきましてはドコモショップまたはドコモインフォメーションセンターにお問い合わせください</li>
        </ul>
    </div>

    <div class="card my-3">
        <div class="accordion" id="accordion">
            <div class="text-center border h3 pt-2 alert alert-secondary pb-1 mb-0">よくあるお問い合わせ</div>
                <div class="accordion-item">
                    <div class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed bg-light border-0 btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        このサイトで表示された以外の書類でも手続き可能かどうか調べたい
                        </button>
                    </div>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="accordion-body pl-4 mt-2 mb-1">お手数ですが、ドコモショップもしくはドコモインフォメーションセンターにてお問い合わせください。</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header" id="headingTwo">
                        <button type="button" class="accordion-button collapsed bg-light border-0 btn btn-link btn-block text-left" data-toggle="collapse" data-target="#collapseTwo" aria-controls="collapseTwo" aria-expanded="false">
                            このサイトで選択できない手続きについても必要書類を確認したい
                        </button>
                    </div>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="accordion-body pl-4 mt-2 mb-1">当サイトでご確認いただけるお手続き内容に関しては、順次追加予定です。<br>現時点で選択できないお手続き内容に関しては、お手数ですがドコモショップまたはドコモインフォメーションセンターにてご確認ください。</div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header" id="headingThree">
                        <button type="button" class="accordion-button collapsed bg-light border-0 btn btn-link btn-block text-left" data-toggle="collapse" data-target="#collapseThree" aria-controls="collapseThree" aria-expanded="false">
                            法人名義だがこのサイトは利用できるか
                        </button>
                    </div>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="accordion-body pl-4 mt-2 mb-1">法人様御名義のお客様の必要書類確認に関しても、順次対応予定でございます。今しばらくお待ちください。</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection
