<?php
/**
 * تبدیل‌کننده پیشرفته Markdown به Mermaid Flowchart
 * پشتیبانی کامل از همه 6 سطح هدینگ (# تا ######)
 * 
 * @version 3.0
 */

class MarkdownToMermaidFlowchart {
    private $nodeCounter = 0;
    private $nodes = [];
    private $connections = [];
    private $levelStyles = [
        1 => ['type' => 'stadium', 'class' => 'level1'],      // ([text])
        2 => ['type' => 'hexagon', 'class' => 'level2'],      // {{text}}
        3 => ['type' => 'subroutine', 'class' => 'level3'],   // [[text]]
        4 => ['type' => 'cylinder', 'class' => 'level4'],     // [(text)]
        5 => ['type' => 'asymmetric', 'class' => 'level5'],   // [/text\]
        6 => ['type' => 'rectangle', 'class' => 'level6'],    // [text]
    ];
    
    /**
     * تبدیل Markdown به Mermaid Flowchart
     */
    public function convert($markdown) {
        $this->reset();
        $lines = explode("\n", $markdown);
        $this->parseMarkdown($lines);
        return $this->generateMermaid();
    }
    
    /**
     * پردازش خطوط Markdown
     */
    private function parseMarkdown($lines) {
        $stack = [];
        
        foreach ($lines as $line) {
            $originalLine = $line;
            $line = trim($line);
            
            if (empty($line)) continue;
            
            $level = 0;
            $text = '';
            $nodeType = 'rectangle';
            $nodeClass = 'default';
            $isDecision = false;
            
            // تشخیص هدینگ‌های # تا ######
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = $matches[2];
                
                // تشخیص نود تصمیم (با ؟)
                if (strpos($text, '?') !== false || strpos($text, '؟') !== false) {
                    $isDecision = true;
                    $nodeType = 'rhombus';
                    $nodeClass = 'decision';
                } else {
                    $nodeType = $this->levelStyles[$level]['type'];
                    $nodeClass = $this->levelStyles[$level]['class'];
                }
            }
            // تشخیص لیست‌ها با تورفتگی
            elseif (preg_match('/^(\s*)[-*+]\s+(.+)$/', $line, $matches)) {
                $indent = strlen($matches[1]);
                $level = (int)($indent / 2) + 7; // سطح 7 به بالا برای لیست‌ها
                $text = $matches[2];
                
                // تشخیص انواع خاص با براکت
                if (preg_match('/^\[(.+)\]$/', $text, $m)) {
                    $nodeType = 'trapezoid';
                    $nodeClass = 'data';
                    $text = $m[1];
                } elseif (preg_match('/^\((.+)\)$/', $text, $m)) {
                    $nodeType = 'circle';
                    $nodeClass = 'process';
                    $text = $m[1];
                } elseif (preg_match('/^{(.+)}$/', $text, $m)) {
                    $nodeType = 'rhombus';
                    $nodeClass = 'decision';
                    $text = $m[1];
                    $isDecision = true;
                } else {
                    $nodeType = 'round';
                    $nodeClass = 'list';
                }
            } else {
                continue;
            }
            
            // پاکسازی متن
            $text = $this->cleanText($text);
            
            // ایجاد نود جدید
            $nodeId = $this->generateNodeId();
            $node = [
                'id' => $nodeId,
                'text' => $text,
                'level' => $level,
                'type' => $nodeType,
                'class' => $nodeClass,
                'isDecision' => $isDecision
            ];
            
            // مدیریت سلسله مراتب (پیدا کردن والد)
            while (count($stack) > 0 && $stack[count($stack) - 1]['level'] >= $level) {
                array_pop($stack);
            }
            
            // ایجاد اتصال به والد
            if (count($stack) > 0) {
                $parent = $stack[count($stack) - 1];
                $connectionType = $this->determineConnectionType($parent, $node);
                $label = $this->determineConnectionLabel($parent, $node);
                
                $this->connections[] = [
                    'from' => $parent['id'],
                    'to' => $nodeId,
                    'type' => $connectionType,
                    'label' => $label
                ];
            }
            
            // ذخیره نود
            $this->nodes[$nodeId] = $node;
            $stack[] = $node;
        }
    }
    
    /**
     * تعیین نوع اتصال بر اساس سطوح
     */
    private function determineConnectionType($parent, $child) {
        $levelDiff = $child['level'] - $parent['level'];
        
        if ($parent['isDecision']) {
            return '-->';  // اتصال معمولی برای تصمیم‌ها
        } elseif ($child['level'] > 6) {
            return '-.->'; // خط‌چین برای لیست‌ها
        } elseif ($levelDiff == 1) {
            return '-->';  // اتصال مستقیم یک سطح
        } elseif ($levelDiff > 1) {
            return '==>';  // اتصال ضخیم برای پرش چند سطحی
        } else {
            return '-->';
        }
    }
    
    /**
     * تعیین برچسب اتصال
     */
    private function determineConnectionLabel($parent, $child) {
        if (!$parent['isDecision']) {
            return '';
        }
        
        // برای نودهای تصمیم، برچسب بله/خیر
        static $decisionLabels = [];
        
        if (!isset($decisionLabels[$parent['id']])) {
            $decisionLabels[$parent['id']] = 0;
        }
        
        $decisionLabels[$parent['id']]++;
        
        if ($decisionLabels[$parent['id']] == 1) {
            return 'بله';
        } else {
            return 'خیر';
        }
    }
    
    /**
     * تولید کد Mermaid نهایی
     */
    private function generateMermaid() {
        $output = "flowchart TD\n";
        $output .= "    %% Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "    %% تبدیل شده از Markdown با پشتیبانی کامل 6 سطح\n\n";
        
        // استایل‌ها
        $output .= $this->generateStyles();
        
        // تعریف نودها
        $output .= "    %% ==> تعریف نودها\n";
        foreach ($this->nodes as $id => $node) {
            $output .= "    " . $this->formatNode($id, $node) . "\n";
        }
        
        $output .= "\n    %% ==> اتصالات\n";
        
        // تعریف اتصالات
        foreach ($this->connections as $conn) {
            $output .= "    {$conn['from']} {$conn['type']} ";
            if (!empty($conn['label'])) {
                $output .= "|{$conn['label']}| ";
            }
            $output .= "{$conn['to']}\n";
        }
        
        // اعمال کلاس‌ها به نودها
        $output .= "\n    %% ==> اعمال استایل‌ها\n";
        $output .= $this->applyClasses();
        
        return $output;
    }
    
    /**
     * فرمت کردن نود بر اساس نوع
     */
    private function formatNode($id, $node) {
        $text = $node['text'];
        
        switch($node['type']) {
            case 'stadium':
                // سطح 1: شکل استادیوم
                return "$id([\"$text\"])";
                
            case 'hexagon':
                // سطح 2: شش‌ضلعی
                return "$id{{\"$text\"}}";
                
            case 'subroutine':
                // سطح 3: مستطیل با خط عمودی
                return "$id{{\"$text\"}}";
                
            case 'cylinder':
                // سطح 4: استوانه (دیتابیس)
                return "$id{{\"$text\"}}";
                
            case 'asymmetric':
                // سطح 5: شکل نامتقارن
                return "$id>\"$text\"]";
                
            case 'rectangle':
                // سطح 6: مستطیل معمولی
                return "$id{{\"$text\"}}";
                
            case 'rhombus':
                // تصمیم: لوزی
                return "$id{\"$text\"}";
                
            case 'round':
                // لیست: گرد
                return "$id(\"$text\")";
                
            case 'trapezoid':
                // داده: ذوزنقه
                return "$id{{\"$text\"}}";
                
            case 'circle':
                // فرآیند: دایره
                return "$id((\"$text\"))";
                
            default:
                return "$id{{\"$text\"}}";
        }
    }
    
    /**
     * تولید استایل‌ها
     */
    private function generateStyles() {
        $styles = "    %% ==> استایل‌ها\n";
        
        // استایل پیش‌فرض
        $styles .= "    classDef default fill:#f9f9f9,stroke:#333,stroke-width:2px,color:#333,font-size:14px,font-family:Tahoma\n";
        
        // استایل سطح 1 - بنفش تیره
        $styles .= "    classDef level1 fill:#667eea,stroke:#5a67d8,stroke-width:4px,color:#fff,font-weight:bold,font-size:18px\n";
        
        // استایل سطح 2 - صورتی
        $styles .= "    classDef level2 fill:#f093fb,stroke:#e91e63,stroke-width:3px,color:#fff,font-weight:bold,font-size:16px\n";
        
        // استایل سطح 3 - آبی
        $styles .= "    classDef level3 fill:#4facfe,stroke:#00acc1,stroke-width:3px,color:#fff,font-size:15px\n";
        
        // استایل سطح 4 - سبز آبی
        $styles .= "    classDef level4 fill:#43e97b,stroke:#26a69a,stroke-width:2px,color:#fff,font-size:14px\n";
        
        // استایل سطح 5 - نارنجی
        $styles .= "    classDef level5 fill:#fa709a,stroke:#ee5a6f,stroke-width:2px,color:#fff,font-size:14px\n";
        
        // استایل سطح 6 - خاکستری
        $styles .= "    classDef level6 fill:#a8edea,stroke:#667eea,stroke-width:2px,color:#333,font-size:13px\n";
        
        // استایل تصمیم - زرد
        $styles .= "    classDef decision fill:#ffd93d,stroke:#ff6b6b,stroke-width:3px,color:#333,font-weight:bold\n";
        
        // استایل لیست - سبز روشن
        $styles .= "    classDef list fill:#96e6a1,stroke:#52c41a,stroke-width:2px,color:#fff\n";
        
        // استایل فرآیند - آبی روشن
        $styles .= "    classDef process fill:#84fab0,stroke:#06beb6,stroke-width:2px,color:#333\n";
        
        // استایل داده - بنفش روشن
        $styles .= "    classDef data fill:#d4a5ff,stroke:#9c27b0,stroke-width:2px,color:#fff\n";
        
        $styles .= "\n";
        return $styles;
    }
    
    /**
     * اعمال کلاس‌ها به نودها
     */
    private function applyClasses() {
        $classSets = [];
        
        // گروه‌بندی نودها بر اساس کلاس
        foreach ($this->nodes as $id => $node) {
            $class = $node['class'];
            if (!isset($classSets[$class])) {
                $classSets[$class] = [];
            }
            $classSets[$class][] = $id;
        }
        
        $output = '';
        foreach ($classSets as $className => $nodeIds) {
            if (!empty($nodeIds)) {
                $output .= "    class " . implode(',', $nodeIds) . " $className\n";
            }
        }
        
        return $output;
    }
    
    /**
     * پاکسازی متن از نشانه‌های Markdown
     */
    private function cleanText($text) {
        // حذف لینک‌ها [text](url)
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        
        // حذف bold **text**
        $text = preg_replace('/\*\*([^\*]+)\*\*/', '$1', $text);
        
        // حذف italic *text*
        $text = preg_replace('/\*([^\*]+)\*/', '$1', $text);
        
        // حذف code `text`
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        
        // حذف strikethrough ~~text~~
        $text = preg_replace('/~~([^~]+)~~/', '$1', $text);
        
        // escape کردن گیومه
        $text = str_replace('"', "'", $text);
        
        // حذف کاراکترهای خاص Mermaid
        $text = str_replace(['[', ']', '(', ')', '{', '}', '<', '>'], '', $text);
        
        return trim($text);
    }
    
    /**
     * تولید ID یکتا
     */
    private function generateNodeId() {
        return 'N' . str_pad(++$this->nodeCounter, 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * ریست کردن وضعیت
     */
    private function reset() {
        $this->nodeCounter = 0;
        $this->nodes = [];
        $this->connections = [];
    }
}

// ===============================
// نمونه استفاده و رابط کاربری
// ===============================

$defaultMarkdown = '# سیستم مدیریت دانشگاه

## ماژول دانشجویان
### ثبت‌نام اولیه
#### اطلاعات شخصی
##### نام و نام خانوادگی
###### کد ملی
- ورود اطلاعات
- [اعتبارسنجی داده‌ها]

#### اطلاعات تحصیلی
- مقطع تحصیلی
- رشته تحصیلی

### مدیریت نمرات
- {آیا نمرات تایید شده؟}
  - ثبت نمرات
  - ارسال به سیستم
- (محاسبه معدل)

## ماژول اساتید
### اطلاعات فردی
- نام استاد
- کد پرسنلی

### دروس ارائه شده
#### دروس نظری
- برنامه‌ریزی پیشرفته
- ساختمان داده‌ها

#### دروس عملی
- آزمایشگاه شبکه
- کارگاه برنامه‌نویسی

## ماژول مالی
### پرداخت شهریه
- {روش پرداخت؟}
  - آنلاین
  - حضوری
- صدور قبض

### گزارش‌گیری
#### گزارش روزانه
- تراکنش‌های روز
##### جزئیات تراکنش
###### کد رهگیری
- ثبت در سیستم

#### گزارش ماهانه
- تحلیل درآمد
- تحلیل هزینه

## سیستم احراز هویت
### ورود کاربران
- {نوع کاربر؟}
  - دانشجو
  - استاد
  - کارمند
- (بررسی مجوزها)

### امنیت
#### رمزنگاری
##### الگوریتم رمزنگاری
###### AES-256
- پیاده‌سازی

#### لاگ فعالیت‌ها
- ثبت ورود
- ثبت خروج';

// پردازش فرم
$markdown = $_POST['markdown'] ?? $defaultMarkdown;
$showCode = isset($_POST['show_code']);

// تبدیل
$converter = new MarkdownToMermaidFlowchart();
$mermaidCode = $converter->convert($markdown);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تبدیل Markdown به Mermaid Flowchart - نسخه کامل</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        
        h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .badge {
            display: inline-block;
            background: #48bb78;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
            font-weight: bold;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 500px 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .panel {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .panel h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            resize: vertical;
            direction: ltr;
            text-align: left;
            transition: border-color 0.3s;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .markdown-input {
            height: 500px;
            background: #f8f9fa;
        }
        
        .code-output {
            height: 400px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        button {
            flex: 1;
            min-width: 150px;
            padding: 14px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: Tahoma;
            font-weight: bold;
        }
        
        button:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-success {
            background: #48bb78;
        }
        
        .btn-success:hover {
            background: #38a169;
        }
        
        .btn-info {
            background: #4299e1;
        }
        
        .btn-info:hover {
            background: #3182ce;
        }
        
        .btn-warning {
            background: #ed8936;
        }
        
        .btn-warning:hover {
            background: #dd6b20;
        }
        
        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            overflow-x: auto;
            min-height: 400px;
        }
        
        .mermaid {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-right: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
        }
        
        .info-box h3 {
            color: #667eea;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .info-box ul {
            margin-right: 20px;
            color: #555;
            line-height: 1.8;
        }
        
        .info-box li {
            margin: 5px 0;
        }
        
        .stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .stat-item {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 120px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .checkbox-group {
            margin: 15px 0;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-left: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .alert-success {
            background: #c6f6d5;
            border-right: 4px solid #48bb78;
            color: #2f855a;
        }
        
        .code-block {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            margin-top: 15px;
            display: none;
        }
        
        .code-block.show {
            display: block;
        }
        
        @media (max-width: 1200px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .panel {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🎯 تبدیل‌کننده Markdown به Mermaid Flowchart</h1>
            <p class="subtitle">پشتیبانی کامل از همه 6 سطح هدینگ (#  تا ######)</p>
            <span class="badge">✨ نسخه پیشرفته 3.0</span>
        </header>
        
        <form method="post" id="converterForm">
            <div class="grid">
                <div class="panel">
                    <h2>📝 ورودی Markdown</h2>
                    <div class="form-group">
                        <label>متن Markdown خود را وارد کنید:</label>
                        <textarea name="markdown" class="markdown-input" placeholder="# عنوان اصلی&#10;## عنوان فرعی&#10;### زیرعنوان&#10;- آیتم لیست"><?php echo htmlspecialchars($markdown); ?></textarea>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="show_code" <?php echo $showCode ? 'checked' : ''; ?>>
                            نمایش کد Mermaid
                        </label>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn-primary">🔄 تبدیل</button>
                        <button type="button" onclick="copyMarkdown()" class="btn-info">📋 کپی</button>
                        <button type="button" onclick="clearForm()" class="btn-warning">🗑️ پاک کردن</button>
                    </div>
                </div>
                
                <div class="panel">
                    <h2>📊 خروجی نمودار</h2>
                    
                    <?php if ($showCode): ?>
                    <div class="alert alert-success">
                        ✅ کد Mermaid با موفقیت تولید شد!
                    </div>
                    
                    <div class="code-block show">
                        <pre><?php echo htmlspecialchars($mermaidCode); ?></pre>
                    </div>
                    
                    <div class="button-group" style="margin-top: 15px;">
					<button type="button" onclick="copyMermaidCode()" class="btn-success">📋 کپی کد</button>
                        <button type="button" onclick="downloadMermaid()" class="btn-info">💾 دانلود</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        
        <div class="panel">
            <h2>🎨 نمایش نمودار</h2>
            <div class="chart-container">
                <div class="mermaid">
                    <?php echo $mermaidCode; ?>
                </div>
            </div>
            
            <div class="button-group" style="margin-top: 20px;">
                <button type="button" onclick="downloadDiagram('svg')" class="btn-success">💾 دانلود SVG</button>
                <button type="button" onclick="downloadDiagram('png')" class="btn-success">💾 دانلود PNG</button>
                <button type="button" onclick="fullscreen()" class="btn-info">🖥️ تمام صفحه</button>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-box">
                <h3>📌 سطوح پشتیبانی شده</h3>
                <ul>
                    <li><strong># سطح 1:</strong> شکل استادیوم (بنفش تیره)</li>
                    <li><strong>## سطح 2:</strong> شش‌ضلعی (صورتی)</li>
                    <li><strong>### سطح 3:</strong> مستطیل با خط (آبی)</li>
                    <li><strong>#### سطح 4:</strong> استوانه (سبز آبی)</li>
                    <li><strong>##### سطح 5:</strong> نامتقارن (نارنجی)</li>
                    <li><strong>###### سطح 6:</strong> مستطیل (خاکستری)</li>
                </ul>
            </div>
            
            <div class="info-box">
                <h3>🎯 شکل‌های ویژه</h3>
                <ul>
                    <li><strong>- متن؟</strong> → نود تصمیم (لوزی زرد)</li>
                    <li><strong>- (متن)</strong> → فرآیند (دایره)</li>
                    <li><strong>- [متن]</strong> → داده (ذوزنقه)</li>
                    <li><strong>- {متن}</strong> → تصمیم (لوزی)</li>
                    <li><strong>- متن</strong> → عادی (گرد)</li>
                </ul>
            </div>
            
            <div class="info-box">
                <h3>⚡ ویژگی‌ها</h3>
                <ul>
                    <li>✅ پشتیبانی از همه 6 سطح هدینگ</li>
                    <li>✅ تشخیص خودکار نودهای تصمیم</li>
                    <li>✅ رنگ‌بندی هوشمند بر اساس سطح</li>
                    <li>✅ اتصالات هوشمند (معمولی/ضخیم/خط‌چین)</li>
                    <li>✅ برچسب‌گذاری خودکار (بله/خیر)</li>
                    <li>✅ پشتیبانی از فارسی و انگلیسی</li>
                </ul>
            </div>
            
            <div class="info-box">
                <h3>💡 نکات کاربردی</h3>
                <ul>
                    <li>از # تا ###### برای سطوح مختلف استفاده کنید</li>
                    <li>با ? یا ؟ نود تصمیم بسازید</li>
                    <li>تورفتگی لیست‌ها را رعایت کنید</li>
                    <li>از براکت‌های ویژه برای شکل‌های خاص</li>
                    <li>متن‌های طولانی خودکار کوتاه می‌شوند</li>
                </ul>
            </div>
        </div>
        
        <div class="panel">
            <h2>📈 آمار نمودار</h2>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($converter->nodes ?? []); ?></div>
                    <div class="stat-label">تعداد نودها</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($converter->connections ?? []); ?></div>
                    <div class="stat-label">تعداد اتصالات</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        $levels = array_unique(array_column($converter->nodes ?? [], 'level'));
                        echo count($levels);
                    ?></div>
                    <div class="stat-label">سطوح استفاده شده</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php 
                        $decisions = array_filter($converter->nodes ?? [], function($n) { 
                            return $n['isDecision'] ?? false; 
                        });
                        echo count($decisions);
                    ?></div>
                    <div class="stat-label">نودهای تصمیم</div>
                </div>
            </div>
        </div>
        
        <div class="panel">
            <h2>📖 نمونه‌های آماده</h2>
            <div class="button-group">
                <button type="button" onclick="loadExample('simple')" class="btn-info">ساده</button>
                <button type="button" onclick="loadExample('medium')" class="btn-info">متوسط</button>
                <button type="button" onclick="loadExample('complex')" class="btn-info">پیچیده</button>
                <button type="button" onclick="loadExample('university')" class="btn-info">دانشگاه</button>
                <button type="button" onclick="loadExample('ecommerce')" class="btn-info">فروشگاه</button>
                <button type="button" onclick="loadExample('project')" class="btn-info">پروژه</button>
            </div>
        </div>
    </div>
    
    <script>
        // تنظیمات Mermaid
        mermaid.initialize({ 
            startOnLoad: true,
            theme: 'default',
            flowchart: {
                useMaxWidth: true,
                htmlLabels: true,
                curve: 'basis',
                padding: 20
            }
        });
        
        // کپی کردن Markdown
        function copyMarkdown() {
            const textarea = document.querySelector('.markdown-input');
            textarea.select();
            document.execCommand('copy');
            showNotification('✅ متن Markdown کپی شد!');
        }
        
        // کپی کردن کد Mermaid
        function copyMermaidCode() {
            const code = `<?php echo addslashes($mermaidCode); ?>`;
            navigator.clipboard.writeText(code).then(() => {
                showNotification('✅ کد Mermaid کپی شد!');
            });
        }
        
        // دانلود کد Mermaid
        function downloadMermaid() {
            const code = `<?php echo addslashes($mermaidCode); ?>`;
            const blob = new Blob([code], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'flowchart-' + Date.now() + '.mmd';
            a.click();
            showNotification('✅ فایل دانلود شد!');
        }
        
        // دانلود نمودار به صورت تصویر
        async function downloadDiagram(format) {
            const svg = document.querySelector('.mermaid svg');
            if (!svg) {
                showNotification('❌ نمودار یافت نشد!', 'error');
                return;
            }
            
            if (format === 'svg') {
                const svgData = new XMLSerializer().serializeToString(svg);
                const blob = new Blob([svgData], { type: 'image/svg+xml' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'flowchart-' + Date.now() + '.svg';
                a.click();
                showNotification('✅ فایل SVG دانلود شد!');
            } else if (format === 'png') {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const svgData = new XMLSerializer().serializeToString(svg);
                const img = new Image();
                
                img.onload = function() {
                    canvas.width = img.width * 2;
                    canvas.height = img.height * 2;
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    canvas.toBlob(function(blob) {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'flowchart-' + Date.now() + '.png';
                        a.click();
                        showNotification('✅ فایل PNG دانلود شد!');
                    });
                };
                
                img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
            }
        }
        
        // نمایش تمام صفحه
        function fullscreen() {
            const container = document.querySelector('.chart-container');
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            }
        }
        
        // پاک کردن فرم
        function clearForm() {
            if (confirm('آیا مطمئن هستید که می‌خواهید فرم را پاک کنید؟')) {
                document.querySelector('.markdown-input').value = '';
                showNotification('🗑️ فرم پاک شد!');
            }
        }
        
        // بارگذاری نمونه‌ها
        function loadExample(type) {
            const examples = {
                simple: `# فرآیند ورود کاربر

## بررسی اطلاعات
- {آیا اطلاعات صحیح است؟}
  - ورود موفق
  - خطا در ورود

## پنل کاربری
- نمایش داشبورد
- (دسترسی به امکانات)`,

                medium: `# سیستم فروش آنلاین

## ثبت سفارش
### انتخاب محصول
- جستجو در لیست
- (مشاهده جزئیات)

### تایید سبد خرید
- {آیا موجودی کافی است؟}
  - محاسبه قیمت
  - پیام عدم موجودی

## پرداخت
### انتخاب روش
#### پرداخت آنلاین
- درگاه بانکی
- [اطلاعات تراکنش]

#### پرداخت در محل
- ثبت آدرس
- تایید سفارش`,

                complex: `# سیستم مدیریت پروژه

## برنامه‌ریزی
### تعریف پروژه
#### اطلاعات پایه
##### نام پروژه
###### کد پروژه
- ثبت در سیستم

#### تیم پروژه
- انتخاب مدیر
- (تخصیص اعضا)

### تعریف وظایف
- {اولویت بالا؟}
  - تخصیص فوری
  - صف انتظار

## اجرا
### توسعه
#### طراحی
- UI/UX
- [مستندات]

#### کدنویسی
##### Backend
- API Development
- Database Design

##### Frontend
- React Components
- State Management

### تست
- {تست موفق؟}
  - آماده استقرار
  - بازگشت به توسعه

## استقرار
### Production
- Deploy
- (مانیتورینگ)`,

                university: `# سیستم مدیریت دانشگاه

## بخش دانشجویان
### ثبت‌نام
#### مرحله اول
##### اطلاعات شخصی
- نام و نام خانوادگی
- کد ملی

#### مرحله دوم
- {رشته انتخابی موجود؟}
  - ثبت نام قطعی
  - انتخاب رشته دیگر

## بخش آموزش
### انتخاب واحد
- (بررسی پیش‌نیازها)
- [لیست دروس]

### ثبت نمرات
#### نمرات میان‌ترم
- ورود نمره
- تایید استاد

#### نمرات پایان‌ترم
- ورود نمره
- (محاسبه معدل)`,

                ecommerce: `# پلتفرم فروشگاه اینترنتی

## مدیریت محصولات
### افزودن محصول
#### اطلاعات پایه
##### نام و قیمت
###### دسته‌بندی
- ثبت در پایگاه

#### تصاویر
- آپلود عکس
- (بهینه‌سازی)

### موجودی
- {موجودی کافی؟}
  - فعال
  - غیرفعال

## سفارش‌ها
### دریافت سفارش
- بررسی سبد
- [محاسبه هزینه]

### پردازش
#### بسته‌بندی
- آماده‌سازی
- چاپ برچسب

#### ارسال
##### انتخاب پست
- پست معمولی
- پست پیشتاز

##### پیگیری
- کد رهگیری
- (اطلاع به مشتری)`,

                project: `# چرخه حیات نرم‌افزار

## مرحله تحلیل
### جمع‌آوری نیازمندی‌ها
#### مصاحبه
##### ذینفعان
- مدیران
- کاربران

#### مستندسازی
- [سند نیازمندی‌ها]
- (بررسی و تایید)

## مرحله طراحی
### طراحی معماری
- {معماری Microservice؟}
  - طراحی سرویس‌ها
  - طراحی Monolithic

### طراحی پایگاه داده
#### مدل‌سازی
- ERD
- Schema Design

## مرحله توسعه
### Backend
#### API Layer
##### REST API
###### Endpoints
- CRUD Operations

#### Business Logic
- سرویس‌ها
- (ولیدیشن)

### Frontend
#### UI Components
- صفحات
- کامپوننت‌ها

### تست
- {تمام تست‌ها OK؟}
  - آماده Deploy
  - رفع باگ‌ها

## مرحله نگهداری
### مانیتورینگ
- لاگ‌ها
- [گزارش‌ها]

### بروزرسانی
- (اعمال تغییرات)
- تست رگرسیون`
            };
            
            if (examples[type]) {
                document.querySelector('.markdown-input').value = examples[type];
                showNotification('✅ نمونه بارگذاری شد! روی دکمه تبدیل کلیک کنید.');
            }
        }
        
        // نمایش اعلان
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: ${type === 'success' ? '#48bb78' : '#f56565'};
                color: white;
                padding: 15px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideDown 0.3s ease-out;
                font-family: Tahoma;
                font-weight: bold;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideUp 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // انیمیشن‌های CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translate(-50%, -20px);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, 0);
                }
            }
            
            @keyframes slideUp {
                from {
                    opacity: 1;
                    transform: translate(-50%, 0);
                }
                to {
                    opacity: 0;
                    transform: translate(-50%, -20px);
                }
            }
        `;
        document.head.appendChild(style);
        
        // ذخیره خودکار
        let autoSaveTimer;
        document.querySelector('.markdown-input').addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                localStorage.setItem('markdown_draft', this.value);
            }, 1000);
        });
        
        // بازیابی از localStorage
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('markdown_draft');
            if (draft && !document.querySelector('.markdown-input').value) {
                if (confirm('یک پیش‌نویس ذخیره شده وجود دارد. آیا می‌خواهید آن را بازیابی کنید؟')) {
                    document.querySelector('.markdown-input').value = draft;
                }
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter = Submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('converterForm').submit();
            }
            
            // Ctrl/Cmd + S = Save draft
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const value = document.querySelector('.markdown-input').value;
                localStorage.setItem('markdown_draft', value);
                showNotification('💾 پیش‌نویس ذخیره شد!');
            }
        });
    </script>
</body>
</html>