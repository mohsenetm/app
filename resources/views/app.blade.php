<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperMemo Vt Flash Cards</title>
    <link href="{{url('lib/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{url('lib/fonts.css')}}" rel="stylesheet">
    <link href="{{url('lib/github-dark.min.css')}}" rel="stylesheet">
    <link href="{{url('lib/style.css')}}" rel="stylesheet">
</head>
<body>
<div class="content">
    <div id="markdown-container" class="container" data-api-url="{{ route('cards.study',['path'=>$path]) }}">
        <div class="loading">در حال دریافت محتوا از سرور...</div>
    </div>
</div>
<div class="actions">
    <div class="alert alert-info text-center mb-0 rounded-0" role="alert">
        <div class="time-display">
            <span class="time-badge">
                <span id="remaining-cards"></span>
            </span>
        </div>
    </div>
    <div class="d-flex justify-content-center flex-wrap rating-buttons">
        <button id="again" class="action-btn btn-again" onclick="performAction('again')">
            1-
            🔄 دوباره
        </button>

        <button id="hard" class="action-btn btn-hard" onclick="performAction('hard')">
            2-
            💪 سخت
        </button>
        <button id="good" class="action-btn btn-good" onclick="performAction('good')">
            3-
            👍 خوب
        </button>
        <button id="easy" class="action-btn btn-easy" onclick="performAction('easy')">
            4-
            😊 آسان
        </button>
    </div>
</div>

<script src="{{url('lib/marked.min.js')}}"></script>
<script src="{{url('lib/highlight.min.js')}}"></script>
<script src="{{url('lib/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('lib/app.js')}}"></script>
</body>
</html>
