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
<style>
    /* Sidebar Styles */
    .sidebar {
        flex-basis: 20%; /* عرض اولیه ۲۰٪ */
        flex-shrink: 0; /* اجازه نده کوچک شود */
        background-color: #212529;
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .sidebar-flex {
        flex-basis: 20%; /* عرض اولیه ۲۰٪ */
        flex-shrink: 0; /* اجازه نده کوچک شود */
        background-color: #212529;
        color: white;
        padding: 20px;
    }

    .sidebar.collapsed {
        transform: translateX(-100%);
    }

    .sidebar .nav-link {
        color: #adb5bd;
        padding: 12px 20px;
        border-radius: 0;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        color: #fff;
        background-color: #495057;
    }

    .sidebar .nav-link.active {
        color: #fff;
        background-color: #0d6efd;
    }

    .sidebar .nav-link i {
        margin-right: 10px;
        width: 16px;
    }

    /* Main Content */
    .main-content {
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 0;
    }

    /* Top Navbar */
    .top-navbar {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }
    }

    /* Auth Pages */
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .auth-card {
        width: 100%;
        max-width: 400px;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }

    /* Side-by-side layout */
    .content {
        display: flex;
        flex-direction: row;
        min-height: 100vh;
        padding: 0px;
    }

    .sidebar {
        width: 250px;
        flex-shrink: 0;
    }

    .sidebar.collapsed {
        margin-left: -250px;
    }

    #markdown-container {
        flex-grow: 1;
        padding-top: 20px;
        transition: margin-right 0.3s ease;
        color: white;
    }

    #markdown-container.expanded {
        margin-right: 0;
    }
</style>
<div id="content" class="content">
    <nav class="sidebar" id="sidebar" dir="auto">
        <div class="d-flex flex-column h-100">
            <!-- Brand -->
            <div class="p-3 border-bottom border-secondary">
                <div class="d-flex">

                    <h5 class="text-white mb-0">{{$path}}</h5>
                    <button id="darkModeToggle" class="btn btn-outline-secondary" onclick="toggleDarkMode()"
                            style="margin-left: auto">
                        <i class="bi bi-moon-fill"></i> Dark Mode
                    </button>
                </div>
            </div>

            <!-- Navigation Menu -->
            <ul class="nav nav-pills flex-column flex-grow-1">
                @foreach($files as $file)
                    @php
                        $fileName = $file->getFileName();
                        $fileName = str_replace('.md','',$fileName)
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('read',['path'=>$path,'fileName'=>$fileName]) }}">
                            <i class="bi bi-speedometer2"></i>
                            {{$fileName}}
                        </a>
                    </li>
                @endforeach
                <li class="nav-item">
                    <a class="nav-link"
                       href="{{ route('history') }}">
                        <i class="bi bi-speedometer2"></i>
                        History
                    </a>
                </li>
            </ul>

            <!-- User Info & Logout -->
            <div class="mt-auto border-top border-secondary p-3">
                <div class="d-flex align-items-center text-white mb-2">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                         style="width: 32px; height: 32px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ Auth::user()->name }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <div id="markdown-container" class="container"
         data-api-url="{{ route('cards.read',['path'=>$path,'fileName'=>$fileName]) }}">
        <div class="loading">در حال دریافت محتوا از سرور...</div>
    </div>
</div>

<script src="{{url('lib/marked.min.js')}}"></script>
<script src="{{url('lib/highlight.min.js')}}"></script>
<script src="{{url('lib/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('lib/read.js')}}"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const container = document.getElementById('markdown-container');
        sidebar.classList.toggle('collapsed');
        container.classList.toggle('expanded');
    }

    function toggleDarkMode() {
        const container = document.getElementById('content');
        const button = document.getElementById('darkModeToggle');
        const markdownContent = container.querySelector('.markdown-content');

        container.classList.toggle('bg-dark');
        container.classList.toggle('text-white');

        if (markdownContent) {
            markdownContent.classList.toggle('bg-dark');
            markdownContent.classList.toggle('text-white');
        }

        // Update button appearance
        if (container.classList.contains('bg-dark')) {
            button.innerHTML = '<i class="bi bi-sun-fill"></i> Light Mode';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-outline-light');
        } else {
            button.innerHTML = '<i class="bi bi-moon-fill"></i> Dark Mode';
            button.classList.remove('btn-outline-light');
            button.classList.add('btn-outline-secondary');
        }
    }
</script>
</body>
</html>
