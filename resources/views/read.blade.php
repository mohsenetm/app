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
    <div class="sidebar">
        //change menu to ul list and better desig. background color is dark. and user can show and hide menu. AI!
        <div class="sidebar-content">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#">خانه</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">کارت‌ها</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">تنظیمات</a>
                </li>
            </ul>
        </div>
    </div>
    <div id="markdown-container" class="container"
         data-api-url="{{ route('cards.read',['path'=>$path,'fileName'=>$fileName]) }}">
        <div class="loading">در حال دریافت محتوا از سرور...</div>
    </div>
</div>

<script src="{{url('lib/marked.min.js')}}"></script>
<script src="{{url('lib/highlight.min.js')}}"></script>
<script src="{{url('lib/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('lib/read.js')}}"></script>
</body>
</html>
