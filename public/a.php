<?php
/**
 * تبدیل Markdown به Mermaid Flowchart Syntax
 * خروجی قابل استفاده در mermaid.live یا هر ابزار Mermaid
 */

class MarkdownToMermaid {
    private $nodeCounter = 0;
    private $nodes = [];
    private $connections = [];
    private $processedNodes = [];
    
    /**
     * تبدیل Markdown به Mermaid syntax
     */
    public function convert($markdown) {
        $lines = explode("\n", $markdown);
        $structure = $this->parseMarkdown($lines);
        return $this->generateMermaidSyntax($structure);
    }
    
    /**
     * پردازش خطوط Markdown و تبدیل به ساختار درختی
     */
    private function parseMarkdown($lines) {
        $tree = [];
        $stack = [];
        $parentMap = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $level = 0;
            $text = '';
            $nodeType = 'rectangle'; // نوع پیش‌فرض
            
            // تشخیص هدینگ‌ها
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $text = $matches[2];
                
                // تعیین شکل بر اساس سطح
                switch($level) {
                    case 1:
                        $nodeType = 'stadium'; // شکل استادیوم برای عنوان اصلی
                        break;
                    case 2:
                        $nodeType = 'hexagon'; // شش‌ضلعی برای سطح 2
                        break;
                    case 3:
                        $nodeType = 'rhombus'; // لوزی برای سطح 3
                        break;
                    default:
                        $nodeType = 'rectangle';
                }
            }
            // تشخیص لیست‌ها
            elseif (preg_match('/^(\s*)[-*]\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]) / 2 + 7;
                $text = $matches[2];
                $nodeType = 'round'; // گرد برای آیتم‌های لیست
            } else {
                continue;
            }
            
            // پاکسازی متن از نشانه‌های Markdown
            $text = $this->cleanText($text);
            
            // ایجاد نود
            $nodeId = $this->generateNodeId();
            $node = [
                'id' => $nodeId,
                'text' => $text,
                'level' => $level,
                'type' => $nodeType,
                'children' => []
            ];
            
            // پیدا کردن والد
            while (count($stack) > 0 && $stack[count($stack) - 1]['level'] >= $level) {
                array_pop($stack);
            }
            
            if (count($stack) == 0) {
                $tree[] = &$node;
            } else {
                $parent = &$stack[count($stack) - 1];
                $parent['children'][] = &$node;
                
                // ذخیره ارتباط والد-فرزند
                $this->connections[] = [
                    'from' => $parent['id'],
                    'to' => $node['id'],
                    'type' => $this->getConnectionType($parent['level'], $level)
                ];
            }
            
            $stack[] = &$node;
            $this->nodes[$nodeId] = [
                'text' => $text,
                'type' => $nodeType
            ];
            
            unset($node);
        }
        
        return $tree;
    }
    
    /**
     * تولید ID یکتا برای نود
     */
    private function generateNodeId() {
        return 'node' . ++$this->nodeCounter;
    }
    
    /**
     * تعیین نوع اتصال بر اساس سطح
     */
    private function getConnectionType($parentLevel, $childLevel) {
        if ($childLevel - $parentLevel == 1) {
            return '-->'; // پیکان معمولی
        } elseif ($childLevel > 6) {
            return '-.->'; // خط‌چین برای لیست‌ها
        } else {
            return '==>'; // پیکان ضخیم برای ارتباط‌های مهم
        }
    }
    
    /**
     * پاکسازی متن از نشانه‌های Markdown
     */
    private function cleanText($text) {
        // حذف لینک‌ها
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        // حذف bold
        $text = preg_replace('/\*\*([^\*]+)\*\*/', '$1', $text);
        // حذف italic
        $text = preg_replace('/\*([^\*]+)\*/', '$1', $text);
        // حذف code
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        // escape کردن کاراکترهای خاص Mermaid
        $text = str_replace('"', "'", $text);
        $text = str_replace(['[', ']', '(', ')', '{', '}'], '', $text);
        
        return $text;
    }
    
    /**
     * تولید سینتکس Mermaid
     */
    private function generateMermaidSyntax($tree) {
        $mermaid = "graph TD\n";
        $mermaid .= "    %% تولید شده از Markdown با PHP\n";
        $mermaid .= "    %% قابل استفاده در mermaid.live\n\n";
        
        // استایل‌ها
        $mermaid .= $this->generateStyles();
        
        // تعریف نودها
        foreach ($this->nodes as $id => $node) {
            $mermaid .= "    " . $this->formatNode($id, $node['text'], $node['type']) . "\n";
        }
        
        $mermaid .= "\n";
        
        // تعریف اتصالات
        foreach ($this->connections as $conn) {
            $mermaid .= "    {$conn['from']} {$conn['type']} {$conn['to']}\n";
        }
        
        return $mermaid;
    }
    
    /**

     */
    private function formatNode($id, $text, $type) {
        switch($type) {
            case 'stadium':
                return "$id([\"$text\"])";
            case 'hexagon':
                return "$id{{\"$text\"}}";
            case 'rhombus':
                return "$id{\"$text\"}";
            case 'round':
                return "$id(\"$text\")";
            case 'rectangle':
            default:
                return "$id[$text]";
        }
    }
    
    /**
     * تولید استایل‌های Mermaid
     */
    private function generateStyles() {
        $styles = "    %% استایل‌ها\n";
        $styles .= "    classDef default fill:#f9f9f9,stroke:#333,stroke-width:2px,color:#333\n";
        $styles .= "    classDef level1 fill:#667eea,stroke:#5a67d8,stroke-width:3px,color:#fff\n";
        $styles .= "    classDef level2 fill:#f093fb,stroke:#e91e63,stroke-width:2px,color:#fff\n";
        $styles .= "    classDef level3 fill:#4facfe,stroke:#00acc1,stroke-width:2px,color:#fff\n";
        $styles .= "    classDef list fill:#43e97b,stroke:#26a69a,stroke-width:2px,color:#fff\n\n";
        
        // اعمال کلاس‌ها به نودها
        $level1Nodes = [];
        $level2Nodes = [];
        $level3Nodes = [];
        $listNodes = [];
        
        foreach ($this->nodes as $id => $node) {
            if ($node['type'] == 'stadium') {
                $level1Nodes[] = $id;
            } elseif ($node['type'] == 'hexagon') {
                $level2Nodes[] = $id;
            } elseif ($node['type'] == 'rhombus') {
                $level3Nodes[] = $id;
            } elseif ($node['type'] == 'round') {
                $listNodes[] = $id;
            }
        }
        
        if (!empty($level1Nodes)) {
            $styles .= "    class " . implode(',', $level1Nodes) . " level1\n";
        }
        if (!empty($level2Nodes)) {
            $styles .= "    class " . implode(',', $level2Nodes) . " level2\n";
        }
        if (!empty($level3Nodes)) {
            $styles .= "    class " . implode(',', $level3Nodes) . " level3\n";
        }
        if (!empty($listNodes)) {
            $styles .= "    class " . implode(',', $listNodes) . " list\n";
        }
        
        $styles .= "\n";
        return $styles;
    }
}

// نمونه Markdown
$markdown = '
# سیستم مدیریت فروشگاه

## ماژول محصولات
- مدیریت انبار
  - ورود کالا
  - خروج کالا
- قیمت‌گذاری
  - قیمت خرید
  - قیمت فروش

## ماژول مشتریان
### ثبت مشتری جدید
- اطلاعات شخصی
- اطلاعات تماس

### مدیریت سفارشات
- سفارش جدید
- پیگیری سفارش
- لغو سفارش

## ماژول گزارشات
- گزارش فروش روزانه
- گزارش موجودی انبار
- گزارش مالی
';

// تبدیل به Mermaid
$converter = new MarkdownToMermaid();
$mermaidCode = $converter->convert($markdown);

// نمایش خروجی
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تبدیل Markdown به Mermaid Flowchart</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .panel {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .panel h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        textarea {
            width: 100%;
            height: 400px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            resize: vertical;
            direction: ltr;
            text-align: left;
        }
        
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .markdown-input {
            background: #f8f9fa;
        }
        
        .mermaid-output {
            background: #f0f8ff;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        button {
            flex: 1;
            padding: 12px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        button:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .success-button {
            background: #48bb78;
        }
        
        .success-button:hover {
            background: #38a169;
        }
        
        .info-box {
            background: #e6f3ff;
            border-right: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin-right: 20px;
            color: #555;
        }
        
        .info-box li {
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 تبدیل Markdown به Mermaid Flowchart</h1>
        
        <div class="grid">
            <div class="panel">
                <h2>📝 ورودی Markdown</h2>
                <textarea class="markdown-input" readonly><?php echo htmlspecialchars($markdown); ?></textarea>
            </div>
            
            <div class="panel">
                <h2>📊 خروجی Mermaid</h2>
                <textarea class="mermaid-output" id="mermaidOutput" readonly><?php echo htmlspecialchars($mermaidCode); ?></textarea>
                
                <div class="button-group">
                    <button onclick="copyToClipboard()">📋 کپی کد</button>
                    <button class="success-button" onclick="openInMermaidLive()">🚀 باز کردن در Mermaid Live</button>
                </div>
            </div>
        </div>
        
        <div class="panel">
            <div class="info-box">
                <h3>💡 راهنمای استفاده:</h3>
                <ul>
                    <li>کد تولید شده را کپی کنید</li>
                    <li>به سایت <strong>mermaid.live</strong> بروید</li>
                    <li>کد را در ویرایشگر paste کنید</li>
                    <li>فلوچارت به صورت خودکار رسم می‌شود</li>
                    <li>می‌توانید خروجی را به صورت PNG یا SVG دانلود کنید</li>
                </ul>
            </div>
            
            <div class="info-box" style="margin-top: 15px;">
                <h3>📐 اشکال استفاده شده:</h3>
                <ul>
                    <li><strong>استادیوم:</strong> عناوین اصلی (#)</li>
                    <li><strong>شش‌ضلعی:</strong> عناوین سطح 2 (##)</li>
                    <li><strong>لوزی:</strong> عناوین سطح 3 (###)</li>
                    <li><strong>دایره:</strong> آیتم‌های لیست (-)</li>
                    <li><strong>مستطیل:</strong> سایر موارد</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const textarea = document.getElementById('mermaidOutput');
            textarea.select();
            document.execCommand('copy');
            
            // نمایش پیام موفقیت
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = '✅ کپی شد!';
            button.style.background = '#48bb78';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = '#667eea';
            }, 2000);
        }
        
        function openInMermaidLive() {
            const code = document.getElementById('mermaidOutput').value;
            const encoded = btoa(JSON.stringify({
                code: code,
                mermaid: { theme: 'default' }
            }));
            window.open('https://mermaid.live/edit#base64:' + encoded, '_blank');
        }
    </script>
</body>
</html>