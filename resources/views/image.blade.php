<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LightShot - فشرده‌سازی هوشمند تصویر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Vazirmatn', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-strong {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .bubble {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.3) 40%, rgba(200, 220, 255, 0.2) 70%, transparent);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.7);
            box-shadow:
                inset -5px -5px 15px rgba(255, 255, 255, 0.6),
                inset 5px 5px 10px rgba(200, 220, 255, 0.3),
                0 8px 20px rgba(0, 0, 0, 0.05);
            animation: rise linear infinite, wobble 3s ease-in-out infinite;
        }

        .bubble::before {
            content: '';
            position: absolute;
            top: 15%;
            left: 20%;
            width: 30%;
            height: 30%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.9), transparent);
            border-radius: 50%;
            filter: blur(3px);
        }

        .bubble::after {
            content: '';
            position: absolute;
            bottom: 20%;
            right: 25%;
            width: 20%;
            height: 20%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.6), transparent);
            border-radius: 50%;
            filter: blur(2px);
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                opacity: 0;
                transform: translateX(0) scale(1);
            }
            5% {
                opacity: 1;
            }
        var(--pop-point) {
            opacity: 1;
            transform: translateX(var(--drift)) scale(1);
        }
        calc(var(--pop-point) + 1%) {
            opacity: 0;
            transform: translateX(var(--drift)) scale(2);
        }
        100% {
            bottom: 110vh;
            opacity: 0;
            transform: translateX(var(--drift)) scale(2);
        }
        }

        @keyframes wobble {
            0%, 100% { transform: translateX(0) scaleX(1); }
            25% { transform: translateX(-3px) scaleX(0.95); }
            50% { transform: translateX(0) scaleX(1.05); }
            75% { transform: translateX(3px) scaleX(0.98); }
        }

        .bubble:nth-child(1) { width: 25px; height: 25px; left: 5%; animation-duration: 12s; animation-delay: 0s; --drift: 20px; --pop-point: 65%; }
        .bubble:nth-child(2) { width: 32px; height: 32px; left: 15%; animation-duration: 15s; animation-delay: -2s; --drift: -25px; --pop-point: 78%; }
        .bubble:nth-child(3) { width: 20px; height: 20px; left: 25%; animation-duration: 10s; animation-delay: -5s; --drift: 30px; --pop-point: 85%; }
        .bubble:nth-child(4) { width: 28px; height: 28px; left: 35%; animation-duration: 13s; animation-delay: -3s; --drift: -20px; --pop-point: 72%; }
        .bubble:nth-child(5) { width: 22px; height: 22px; left: 45%; animation-duration: 11s; animation-delay: -7s; --drift: 25px; --pop-point: 68%; }
        .bubble:nth-child(6) { width: 30px; height: 30px; left: 55%; animation-duration: 14s; animation-delay: -1s; --drift: -28px; --pop-point: 80%; }
        .bubble:nth-child(7) { width: 26px; height: 26px; left: 65%; animation-duration: 16s; animation-delay: -6s; --drift: 22px; --pop-point: 90%; }
        .bubble:nth-child(8) { width: 24px; height: 24px; left: 75%; animation-duration: 12s; animation-delay: -4s; --drift: -24px; --pop-point: 75%; }
        .bubble:nth-child(9) { width: 29px; height: 29px; left: 85%; animation-duration: 13s; animation-delay: -8s; --drift: 27px; --pop-point: 70%; }
        .bubble:nth-child(10) { width: 21px; height: 21px; left: 10%; animation-duration: 11s; animation-delay: -2.5s; --drift: -22px; --pop-point: 82%; }
        .bubble:nth-child(11) { width: 27px; height: 27px; left: 20%; animation-duration: 14s; animation-delay: -5.5s; --drift: 29px; --pop-point: 88%; }
        .bubble:nth-child(12) { width: 23px; height: 23px; left: 30%; animation-duration: 10s; animation-delay: -7.5s; --drift: -26px; --pop-point: 76%; }
        .bubble:nth-child(13) { width: 31px; height: 31px; left: 40%; animation-duration: 15s; animation-delay: -3.5s; --drift: 23px; --pop-point: 92%; }
        .bubble:nth-child(14) { width: 25px; height: 25px; left: 50%; animation-duration: 12s; animation-delay: -6.5s; --drift: -30px; --pop-point: 79%; }
        .bubble:nth-child(15) { width: 22px; height: 22px; left: 60%; animation-duration: 13s; animation-delay: -1.5s; --drift: 28px; --pop-point: 86%; }
        .bubble:nth-child(16) { width: 28px; height: 28px; left: 70%; animation-duration: 11s; animation-delay: -4.5s; --drift: -25px; --pop-point: 73%; }
        .bubble:nth-child(17) { width: 30px; height: 30px; left: 80%; animation-duration: 16s; animation-delay: -8.5s; --drift: 24px; --pop-point: 95%; }
        .bubble:nth-child(18) { width: 20px; height: 20px; left: 90%; animation-duration: 10s; animation-delay: -2.8s; --drift: -21px; --pop-point: 71%; }
        .bubble:nth-child(19) { width: 26px; height: 26px; left: 12%; animation-duration: 14s; animation-delay: -6.2s; --drift: 31px; --pop-point: 84%; }
        .bubble:nth-child(20) { width: 24px; height: 24px; left: 92%; animation-duration: 12s; animation-delay: -9s; --drift: -27px; --pop-point: 77%; }

        .comparison-container {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            cursor: ew-resize;
        }

        .image-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .before-image,
        .after-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }

        .before-image {
            z-index: 2;
            clip-path: inset(0 50% 0 0);
        }

        .after-image {
            z-index: 1;
        }

        .comparison-slider {
            position: absolute;
            top: 0;
            left: 50%;
            width: 4px;
            height: 100%;
            background: white;
            z-index: 3;
            transform: translateX(-50%);
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        .slider-button {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 64px;
            height: 64px;
            background: white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: 4;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }

        .slider-button:hover {
            transform: translate(-50%, -50%) scale(1.1);
        }

        .slider-button::before,
        .slider-button::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-style: solid;
        }

        .slider-button::before {
            left: 15px;
            border-width: 10px 15px 10px 0;
            border-color: transparent #60a5fa transparent transparent;
        }

        .slider-button::after {
            right: 15px;
            border-width: 10px 0 10px 15px;
            border-color: transparent transparent transparent #60a5fa;
        }

        .logo-glow {
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.6);
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(96, 165, 250, 0.4); }
            50% { box-shadow: 0 0 40px rgba(139, 92, 246, 0.6); }
        }

        .pulse-glow {
            animation: pulse-glow 3s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 min-h-screen">

<!-- Floating Glass Bubbles -->
<div class="fixed inset-0 pointer-events-none z-0">
    @for ($i = 1; $i <= 20; $i++)
        <div class="bubble"></div>
    @endfor
</div>

<!-- Header -->
<header class="glass-strong fixed top-0 left-0 right-0 z-50 shadow-2xl">
    <nav class="container mx-auto px-6 py-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 via-purple-400 to-pink-400 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg pulse-glow">
                    LS
                </div>
                <span class="text-3xl font-extrabold logo-glow gradient-text">
                        LightShot
                    </span>
            </div>
            <ul class="hidden md:flex gap-10 text-gray-700 font-semibold text-lg">
                <li><a href="#top" class="hover:text-blue-600 transition-all duration-300 hover:scale-110 inline-block">خانه</a></li>
                <li><a href="#compare" class="hover:text-pink-600 transition-all duration-300 hover:scale-110 inline-block">مقایسه</a></li>
                <li><a href="#footer" class="hover:text-blue-600 transition-all duration-300 hover:scale-110 inline-block">درباره ما</a></li>
            </ul>
            <button class="glass px-6 py-3 rounded-2xl font-semibold text-blue-600 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hidden md:block">
                شروع رایگان
            </button>
            <button class="md:hidden text-gray-700">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </nav>
</header>

<!-- Hero Section -->
<section id="top" class="relative z-10 pt-40 pb-32 px-4">
    <div class="container mx-auto text-center">
        <div class="max-w-5xl mx-auto">
            <div class="mb-8">
                    <span class="glass px-8 py-3 rounded-full text-sm font-semibold text-purple-600 inline-block mb-6 shadow-lg">
                        🚀 بهترین ابزار فشرده‌سازی تصویر
                    </span>
            </div>
            <h1 class="text-6xl md:text-8xl font-black mb-8 gradient-text leading-tight">
                LightShot
            </h1>
            <p class="text-2xl md:text-3xl text-gray-700 mb-8 leading-relaxed font-medium">
                فشرده‌سازی هوشمند و حرفه‌ای تصاویر
            </p>
            <p class="text-lg md:text-xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                با استفاده از الگوریتم‌های پیشرفته، حجم تصاویر خود را تا 80 درصد کاهش دهید بدون از دست دادن کیفیت
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="#compare" class="glass-strong px-10 py-5 rounded-2xl text-blue-600 font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105">
                    مشاهده نمونه
                </a>
                <a href="#features" class="glass px-10 py-5 rounded-2xl text-purple-600 font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105">
                    امکانات بیشتر
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Comparison Section -->
<section id="compare" class="relative z-10 py-24 px-4">
    <div class="container mx-auto max-w-6xl">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-6 gradient-text">مقایسه کیفیت</h2>
            <p class="text-xl text-gray-600">تفاوت را با چشم خود ببینید</p>
        </div>

        <div class="glass-strong p-8 md:p-12 rounded-3xl shadow-2xl">
            <!-- Upload Form -->
            <div class="mb-12" id="uploadFormContainer">
                <div class="max-w-2xl mx-auto">
                    <form id="uploadForm" class="space-y-6">
                        <div class="glass p-8 rounded-2xl border-2 border-dashed border-blue-400 hover:border-purple-400 transition-all duration-300 cursor-pointer" id="dropZone">
                            <input type="file" id="imageInput" name="image" accept="image/*" class="hidden">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <p class="text-xl font-bold text-gray-700 mb-2">تصویر خود را اینجا بکشید یا کلیک کنید</p>
                                <p class="text-sm text-gray-600">فرمت‌های پشتیبانی شده: JPG, PNG, GIF, BMP, WebP, AVIF</p>
                            </div>
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">فرمت خروجی</label>
                            <select id="formatSelect" name="format" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-blue-600 focus:outline-none font-semibold transition-all duration-300">
                                <option value="jpg" selected>JPG</option>
                                <option value="webp">WebP</option>
                            </select>
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">فشرده سازی</label>
                            <select id="qualitySelect" name="quality" class="w-full px-4 py-3 rounded-xl border-2 border-gray-300 focus:border-blue-600 focus:outline-none font-semibold transition-all duration-300">
                                <option value="ultra" selected>حداکثر</option>
                                <option value="high">بالا</option>
                                <option value="medium">متوسط</option>
                                <option value="low">پایین</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full glass-strong px-8 py-4 rounded-2xl text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed" id="submitBtn">
                            فشرده‌سازی تصویر
                        </button>
                    </form>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="hidden text-center mb-12">
                <div class="inline-block">
                    <div class="w-12 h-12 border-4 border-blue-600 border-t-purple-600 rounded-full animate-spin"></div>
                </div>
                <p class="mt-4 text-lg font-semibold text-gray-700">در حال فشرده‌سازی...</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10" id="statsContainer">
                <div class="glass p-6 rounded-2xl text-center transform hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-black text-blue-600 mb-2" id="originalSizeStat" style="direction: ltr">2485 KB</div>
                    <div class="text-sm text-gray-600 font-semibold">حجم اصلی</div>
                </div>
                <div class="glass p-6 rounded-2xl text-center transform hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-black text-purple-600 mb-2" id="convertedSizeStat" style="direction: ltr">363 KB</div>
                    <div class="text-sm text-gray-600 font-semibold">حجم فشرده</div>
                </div>
                <div class="glass p-6 rounded-2xl text-center transform hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-black text-green-600 mb-2" id="compressionStat" style="direction: ltr">85%</div>
                    <div class="text-sm text-gray-600 font-semibold">کاهش حجم</div>
                </div>
                <div class="glass p-6 rounded-2xl text-center transform hover:scale-105 transition-all duration-300">
                    <div class="text-4xl font-black text-pink-600 mb-2" id="durationStat" style="direction: ltr">0.29s</div>
                    <div class="text-sm text-gray-600 font-semibold">مدت زمان</div>
                </div>
            </div>

            <!-- Image Comparison Slider -->
            <div class="comparison-container shadow-2xl" style="height: 600px;" id="comparisonContainer">
                <div class="image-container">
                    <!-- After Image (Compressed) - Base -->
                    <div class="after-image" id="afterImage"></div>
                    <!-- Before Image (Original) - Overlay -->
                    <div class="before-image" id="beforeImage"></div>
                </div>

                <!-- Slider -->
                <div class="comparison-slider" id="sliderLine"></div>
                <div class="slider-button" id="sliderButton"></div>

                <!-- Labels -->
                <div class="absolute top-6 left-6 glass px-6 py-3 rounded-xl font-bold text-blue-600 shadow-lg z-10">
                    تصویر اصلی
                </div>
                <div class="absolute top-6 right-6 glass px-6 py-3 rounded-xl font-bold text-purple-600 shadow-lg z-10">
                    تصویر فشرده شده
                </div>
            </div>

            <div class="mt-10 text-center" id="sliderInfoContainer">
                <p class="text-gray-600 text-lg mb-6">
                    اسلایدر را حرکت دهید تا تفاوت را مشاهده کنید
                </p>
                <button type="button" id="uploadNewBtn" class="glass-strong px-12 py-5 rounded-2xl text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105 inline-block ml-4">
                    آپلود تصویر جدید
                </button>
                <a id="downloadBtn" href="#" class="glass-strong px-12 py-5 rounded-2xl text-lg font-bold bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-105 inline-block">
                    دانلود تصویر فشرده شده
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="footer" class="relative z-10 glass-strong py-16 px-4 mt-32">
    <div class="container mx-auto text-center">
        <div class="flex items-center justify-center gap-4 mb-8">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-400 via-purple-400 to-pink-400 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                LS
            </div>
            <span class="text-4xl font-extrabold gradient-text logo-glow">
                    LightShot
                </span>
        </div>
        <p class="text-gray-600 text-lg mb-8 max-w-2xl mx-auto">
            بهترین ابزار فشرده‌سازی تصویر با کیفیت حرفه‌ای
        </p>
        <div class="flex flex-wrap justify-center gap-8 text-gray-600 font-semibold mb-8">
            <a href="#" class="hover:text-blue-600 transition-all duration-300 hover:scale-110">حریم خصوصی</a>
            <a href="#" class="hover:text-purple-600 transition-all duration-300 hover:scale-110">شرایط استفاده</a>
            <a href="#" class="hover:text-pink-600 transition-all duration-300 hover:scale-110">تماس با ما</a>
            <a href="#" class="hover:text-blue-600 transition-all duration-300 hover:scale-110">راهنما</a>
            <a href="#" class="hover:text-purple-600 transition-all duration-300 hover:scale-110">وبلاگ</a>
        </div>
        <div class="flex justify-center gap-6 mb-8">
            <a href="#" class="glass w-12 h-12 rounded-xl flex items-center justify-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="#" class="glass w-12 h-12 rounded-xl flex items-center justify-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a href="#" class="glass w-12 h-12 rounded-xl flex items-center justify-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
            </a>
        </div>
        <p class="text-sm text-gray-500">
            © {{ date('Y') }} LightShot. تمامی حقوق محفوظ است | ساخته شده با ❤️ برای بهترین‌ها
        </p>
    </div>
</footer>

<script>
    const comparisonContainer = document.getElementById('comparisonContainer');
    const beforeImage = document.getElementById('beforeImage');
    const afterImage = document.getElementById('afterImage');
    const sliderLine = document.getElementById('sliderLine');
    const sliderButton = document.getElementById('sliderButton');
    const uploadForm = document.getElementById('uploadForm');
    const imageInput = document.getElementById('imageInput');
    const dropZone = document.getElementById('dropZone');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const statsContainer = document.getElementById('statsContainer');
    const sliderInfoContainer = document.getElementById('sliderInfoContainer');
    const uploadFormContainer = document.getElementById('uploadFormContainer');
    const qualitySelect = document.getElementById('qualitySelect');
    const submitBtn = document.getElementById('submitBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const uploadNewBtn = document.getElementById('uploadNewBtn');

    let isDragging = false;
    let convertedImagePath = null;
    let isDefaultSample = true;

    // Load default sample on page load
    window.addEventListener('load', () => {
        loadDefaultSample();
    });

    function loadDefaultSample() {
        const defaultImageUrl = 'http://app.test/storage/sample/1.jpg';
        const compressImageUrl = 'http://app.test/storage/sample/2.jpg';
        beforeImage.style.backgroundImage = `url('${defaultImageUrl}')`;
        afterImage.style.backgroundImage = `url('${compressImageUrl}')`;
        setSlider(50);
        isDefaultSample = true;
    }

    uploadNewBtn.addEventListener('click', () => {
        imageInput.click();
    });

    // Drop zone handling
    dropZone.addEventListener('click', () => imageInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-purple-600', 'bg-purple-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-purple-600', 'bg-purple-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-purple-600', 'bg-purple-50');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imageInput.files = files;
            updateDropZoneStyle();
        }
    });

    imageInput.addEventListener('change', () => {
        updateDropZoneStyle();
        if (imageInput.files.length > 0) {
            uploadForm.dispatchEvent(new Event('submit'));
        }
    });

    function updateDropZoneStyle() {
        if (imageInput.files.length > 0) {
            const file = imageInput.files[0];
            dropZone.classList.add('border-green-600', 'bg-green-50');
            dropZone.classList.remove('border-blue-400', 'hover:border-purple-400');

            const svgIcon = dropZone.querySelector('svg');
            svgIcon.classList.remove('text-blue-600');
            svgIcon.classList.add('text-green-600');

            const textElements = dropZone.querySelectorAll('p');
            textElements[0].textContent = `✓ ${file.name}`;
            textElements[0].classList.add('text-green-700');
            textElements[1].textContent = `حجم: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            textElements[1].classList.add('text-green-600');
        } else {
            dropZone.classList.remove('border-green-600', 'bg-green-50');
            dropZone.classList.add('border-blue-400', 'hover:border-purple-400');

            const svgIcon = dropZone.querySelector('svg');
            svgIcon.classList.add('text-blue-600');
            svgIcon.classList.remove('text-green-600');

            const textElements = dropZone.querySelectorAll('p');
            textElements[0].textContent = 'تصویر خود را اینجا بکشید یا کلیک کنید';
            textElements[0].classList.remove('text-green-700');
            textElements[1].textContent = 'فرمت‌های پشتیبانی شده: JPG, PNG, GIF, BMP, WebP, AVIF';
            textElements[1].classList.remove('text-green-600');
        }
    }

    // Form submission
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!imageInput.files.length) {
            alert('لطفا یک تصویر انتخاب کنید');
            return;
        }

        const formData = new FormData(uploadForm);

        // Show loading, hide form
        uploadFormContainer.style.display = 'none';
        loadingSpinner.style.display = 'block';

        try {
            const response = await fetch('/convert-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert('خطا: ' + (data.message || 'خطا در تبدیل تصویر'));
                loadingSpinner.style.display = 'none';
                uploadFormContainer.style.display = 'block';
                return;
            }

            // Update stats
            document.getElementById('originalSizeStat').textContent = data.original_size_kb + ' KB';
            document.getElementById('convertedSizeStat').textContent = data.converted_size_kb + ' KB';
            document.getElementById('compressionStat').textContent = data.compression_percent + '%';
            document.getElementById('durationStat').textContent = data.duration_seconds + 's';

            // Set images for comparison
            const file = imageInput.files[0];
            const reader = new FileReader();
            reader.onload = (e) => {
                beforeImage.style.backgroundImage = `url('${e.target.result}')`;
                afterImage.style.backgroundImage = `url('${e.target.result}')`;
            };
            reader.readAsDataURL(file);

            convertedImagePath = data.path;
            downloadBtn.href = `/storage/temp_converted_images/${data.path.split('/').pop()}`;

            // Hide loading, show results
            loadingSpinner.style.display = 'none';
            statsContainer.style.display = 'grid';
            comparisonContainer.style.display = 'block';
            sliderInfoContainer.style.display = 'block';

            setSlider(50);
            isDefaultSample = false;
        } catch (error) {
            console.error('Error:', error);
            alert('خطا در ارسال درخواست');
            loadingSpinner.style.display = 'none';
            uploadFormContainer.style.display = 'block';
        }
    });

    function clamp(v, min, max) {
        return Math.min(Math.max(v, min), max);
    }

    function setSlider(percentage) {
        const p = clamp(percentage, 0, 100);
        beforeImage.style.clipPath = `inset(0 ${100 - p}% 0 0)`;
        sliderLine.style.left = p + '%';
        sliderButton.style.left = p + '%';
    }

    function updateFromClientX(clientX) {
        const rect = comparisonContainer.getBoundingClientRect();
        const x = clientX - rect.left;
        const percentage = (x / rect.width) * 100;
        setSlider(percentage);
    }

    function startDrag(e) {
        isDragging = true;
        e.preventDefault();
    }

    sliderButton.addEventListener('mousedown', startDrag);
    sliderLine.addEventListener('mousedown', startDrag);

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    comparisonContainer.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        updateFromClientX(e.clientX);
    });

    comparisonContainer.addEventListener('click', (e) => {
        updateFromClientX(e.clientX);
    });

    // Touch
    sliderButton.addEventListener('touchstart', (e) => {
        isDragging = true;
        e.preventDefault();
    }, { passive: false });

    sliderLine.addEventListener('touchstart', (e) => {
        isDragging = true;
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchend', () => {
        isDragging = false;
    });

    comparisonContainer.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const touch = e.touches[0];
        updateFromClientX(touch.clientX);
        e.preventDefault();
    }, { passive: false });

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            if (this.id !== 'downloadBtn') {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
</script>
</body>
</html>
