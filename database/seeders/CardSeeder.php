<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    public function run()
    {
        $cardData = $this->getCardData();

        foreach (Deck::all() as $deck) {
            $cards = $this->getCardsForDeck($deck->name, $cardData);

            foreach ($cards as $card) {
                Card::create([
                    'deck_id' => $deck->id,
                    'front' => $card['front'],
                    'back' => $card['back'],
                    'notes' => $card['notes'] ?? null,
                    'type' => $card['type'] ?? 'basic',
                    'tags' => $card['tags'] ?? [],
                ]);
            }
        }
    }

    private function getCardsForDeck($deckName, $cardData)
    {
        // بر اساس نام دسته، کارت‌های مناسب را برمی‌گرداند
        foreach ($cardData as $category => $cards) {
            if (str_contains($deckName, $category)) {
                return array_slice($cards, 0, rand(30, 100)); // 30-100 کارت برای هر دسته
            }
        }

        // اگر دسته‌ای پیدا نشد، کارت‌های عمومی برمی‌گرداند
        return array_slice($cardData['عمومی'], 0, rand(20, 50));
    }

    private function getCardData()
    {
        return [
            'انگلیسی' => [
                ['front' => 'Hello', 'back' => 'سلام', 'tags' => ['greeting', 'basic']],
                ['front' => 'Good morning', 'back' => 'صبح بخیر', 'tags' => ['greeting', 'basic']],
                ['front' => 'Thank you', 'back' => 'متشکرم', 'tags' => ['polite', 'basic']],
                ['front' => 'Please', 'back' => 'لطفاً', 'tags' => ['polite', 'basic']],
                ['front' => 'Water', 'back' => 'آب', 'tags' => ['noun', 'basic']],
                ['front' => 'Food', 'back' => 'غذا', 'tags' => ['noun', 'basic']],
                ['front' => 'Book', 'back' => 'کتاب', 'tags' => ['noun', 'basic']],
                ['front' => 'Computer', 'back' => 'کامپیوتر', 'tags' => ['noun', 'technology']],
                ['front' => 'Programming', 'back' => 'برنامه‌نویسی', 'tags' => ['noun', 'technology']],
                ['front' => 'Database', 'back' => 'پایگاه داده', 'tags' => ['noun', 'technology']],
                ['front' => 'Algorithm', 'back' => 'الگوریتم', 'tags' => ['noun', 'technology']],
                ['front' => 'Function', 'back' => 'تابع', 'tags' => ['noun', 'programming']],
                ['front' => 'Variable', 'back' => 'متغیر', 'tags' => ['noun', 'programming']],
                ['front' => 'Loop', 'back' => 'حلقه', 'tags' => ['noun', 'programming']],
                ['front' => 'Condition', 'back' => 'شرط', 'tags' => ['noun', 'programming']],
                ['front' => 'Array', 'back' => 'آرایه', 'tags' => ['noun', 'programming']],
                ['front' => 'Object', 'back' => 'شیء', 'tags' => ['noun', 'programming']],
                ['front' => 'Class', 'back' => 'کلاس', 'tags' => ['noun', 'programming']],
                ['front' => 'Method', 'back' => 'متد', 'tags' => ['noun', 'programming']],
                ['front' => 'Property', 'back' => 'ویژگی', 'tags' => ['noun', 'programming']],
                ['front' => 'Inheritance', 'back' => 'وراثت', 'tags' => ['noun', 'oop']],
                ['front' => 'Polymorphism', 'back' => 'چندریختی', 'tags' => ['noun', 'oop']],
                ['front' => 'Encapsulation', 'back' => 'کپسوله‌سازی', 'tags' => ['noun', 'oop']],
                ['front' => 'Interface', 'back' => 'رابط', 'tags' => ['noun', 'oop']],
                ['front' => 'Abstract', 'back' => 'انتزاعی', 'tags' => ['adjective', 'oop']],
                ['front' => 'To run', 'back' => 'دویدن', 'tags' => ['verb', 'basic']],
                ['front' => 'To walk', 'back' => 'راه رفتن', 'tags' => ['verb', 'basic']],
                ['front' => 'To eat', 'back' => 'خوردن', 'tags' => ['verb', 'basic']],
                ['front' => 'To sleep', 'back' => 'خوابیدن', 'tags' => ['verb', 'basic']],
                ['front' => 'To code', 'back' => 'کد نویسی کردن', 'tags' => ['verb', 'technology']],
            ],

            'PHP' => [
                ['front' => 'echo', 'back' => 'دستور نمایش خروجی در PHP', 'notes' => 'echo "Hello World";'],
                ['front' => 'print', 'back' => 'دستور نمایش خروجی (فقط یک مقدار)', 'notes' => 'print "Hello";'],
                ['front' => '$variable', 'back' => 'نحوه تعریف متغیر در PHP', 'notes' => '$name = "Ali";'],
                ['front' => 'array()', 'back' => 'تعریف آرایه در PHP', 'notes' => '$arr = array(1, 2, 3);'],
                ['front' => 'function', 'back' => 'تعریف تابع در PHP', 'notes' => 'function myFunc() { }'],
                ['front' => 'class', 'back' => 'تعریف کلاس در PHP', 'notes' => 'class MyClass { }'],
                ['front' => 'public', 'back' => 'سطح دسترسی عمومی', 'notes' => 'public $property;'],
                ['front' => 'private', 'back' => 'سطح دسترسی خصوصی', 'notes' => 'private $property;'],
                ['front' => 'protected', 'back' => 'سطح دسترسی محافظت شده', 'notes' => 'protected $property;'],
                ['front' => 'extends', 'back' => 'وراثت در PHP', 'notes' => 'class Child extends Parent { }'],
                ['front' => 'implements', 'back' => 'پیاده‌سازی interface', 'notes' => 'class MyClass implements MyInterface { }'],
                ['front' => 'namespace', 'back' => 'فضای نام در PHP', 'notes' => 'namespace App\\Models;'],
                ['front' => 'use', 'back' => 'استفاده از namespace', 'notes' => 'use App\\Models\\User;'],
                ['front' => 'require', 'back' => 'وارد کردن فایل (اجباری)', 'notes' => 'require "file.php";'],
                ['front' => 'include', 'back' => 'وارد کردن فایل (اختیاری)', 'notes' => 'include "file.php";'],
                ['front' => 'isset()', 'back' => 'بررسی وجود متغیر', 'notes' => 'if (isset($var)) { }'],
                ['front' => 'empty()', 'back' => 'بررسی خالی بودن متغیر', 'notes' => 'if (empty($var)) { }'],
                ['front' => 'count()', 'back' => 'شمارش تعداد عناصر آرایه', 'notes' => '$count = count($array);'],
                ['front' => 'strlen()', 'back' => 'طول رشته', 'notes' => '$length = strlen($string);'],
                ['front' => 'str_replace()', 'back' => 'جایگزینی در رشته', 'notes' => 'str_replace("old", "new", $string);'],
            ],

            'پایگاه داده' => [
                ['front' => 'SELECT', 'back' => 'دستور انتخاب داده‌ها', 'notes' => 'SELECT * FROM users;'],
                ['front' => 'INSERT', 'back' => 'دستور درج داده', 'notes' => 'INSERT INTO users (name) VALUES ("Ali");'],
                ['front' => 'UPDATE', 'back' => 'دستور بروزرسانی داده', 'notes' => 'UPDATE users SET name = "Ali" WHERE id = 1;'],
                ['front' => 'DELETE', 'back' => 'دستور حذف داده', 'notes' => 'DELETE FROM users WHERE id = 1;'],
                ['front' => 'WHERE', 'back' => 'شرط در SQL', 'notes' => 'SELECT * FROM users WHERE age > 18;'],
                ['front' => 'JOIN', 'back' => 'اتصال جداول', 'notes' => 'SELECT * FROM users JOIN orders ON users.id = orders.user_id;'],
                ['front' => 'INNER JOIN', 'back' => 'اتصال داخلی', 'notes' => 'فقط رکوردهای مشترک'],
                ['front' => 'LEFT JOIN', 'back' => 'اتصال چپ', 'notes' => 'همه رکوردهای جدول چپ'],
                ['front' => 'RIGHT JOIN', 'back' => 'اتصال راست', 'notes' => 'همه رکوردهای جدول راست'],
                ['front' => 'GROUP BY', 'back' => 'گروه‌بندی نتایج', 'notes' => 'SELECT city, COUNT(*) FROM users GROUP BY city;'],
                ['front' => 'ORDER BY', 'back' => 'مرتب‌سازی نتایج', 'notes' => 'SELECT * FROM users ORDER BY name ASC;'],
                ['front' => 'LIMIT', 'back' => 'محدود کردن تعداد نتایج', 'notes' => 'SELECT * FROM users LIMIT 10;'],
                ['front' => 'PRIMARY KEY', 'back' => 'کلید اصلی', 'notes' => 'شناسه یکتا برای هر رکورد'],
                ['front' => 'FOREIGN KEY', 'back' => 'کلید خارجی', 'notes' => 'ارجاع به کلید اصلی جدول دیگر'],
                ['front' => 'INDEX', 'back' => 'ایندکس', 'notes' => 'برای سرعت بخشیدن به جستجو'],
                ['front' => 'UNIQUE', 'back' => 'یکتا', 'notes' => 'مقدار تکراری مجاز نیست'],
                ['front' => 'NOT NULL', 'back' => 'غیر تهی', 'notes' => 'مقدار NULL مجاز نیست'],
                ['front' => 'AUTO_INCREMENT', 'back' => 'افزایش خودکار', 'notes' => 'معمولاً برای ID'],
                ['front' => 'VARCHAR', 'back' => 'رشته با طول متغیر', 'notes' => 'VARCHAR(255)'],
                ['front' => 'INT', 'back' => 'عدد صحیح', 'notes' => 'INT(11)'],
            ],

            'تاریخ' => [
                ['front' => 'سلسله هخامنشیان', 'back' => '550-330 پیش از میلاد', 'notes' => 'بنیانگذار: کوروش بزرگ'],
                ['front' => 'کوروش بزرگ', 'back' => 'بنیانگذار سلسله هخامنشی', 'notes' => '590-530 پ.م'],
                ['front' => 'داریوش بزرگ', 'back' => 'بزرگترین پادشاه هخامنشی', 'notes' => '522-486 پ.م'],
                ['front' => 'تخت جمشید', 'back' => 'پایتخت تشریفاتی هخامنشیان', 'notes' => 'در نزدیکی شیراز'],
                ['front' => 'سلسله ساسانی', 'back' => '224-651 میلادی', 'notes' => 'آخرین سلسله ایران باستان'],
                ['front' => 'اردشیر بابکان', 'back' => 'بنیانگذار سلسله ساسانی', 'notes' => '224-241 میلادی'],
                ['front' => 'انوشیروان', 'back' => 'خسرو اول، عادل', 'notes' => '531-579 میلادی'],
                ['front' => 'فتح ایران توسط اعراب', 'back' => '651 میلادی', 'notes' => 'پایان سلسله ساسانی'],
                ['front' => 'سلسله صفوی', 'back' => '1501-1736 میلادی', 'notes' => 'شیعه مذهب رسمی ایران شد'],
                ['front' => 'شاه اسماعیل اول', 'back' => 'بنیانگذار صفویه', 'notes' => '1501-1524'],
                ['front' => 'شاه عباس بزرگ', 'back' => 'اوج قدرت صفویه', 'notes' => '1588-1629'],
                ['front' => 'نادر شاه افشار', 'back' => 'فاتح هند', 'notes' => '1736-1747'],
                ['front' => 'کریم خان زند', 'back' => 'وکیل الرعایا', 'notes' => '1751-1779'],
                ['front' => 'آغا محمد خان قاجار', 'back' => 'بنیانگذار قاجاریه', 'notes' => '1794-1797'],
                ['front' => 'عباس میرزا', 'back' => 'نایب السلطنه قاجار', 'notes' => 'اصلاحات نظامی'],
                ['front' => 'امیرکبیر', 'back' => 'میرزا تقی خان', 'notes' => 'صدراعظم ناصرالدین شاه'],
                ['front' => 'انقلاب مشروطه', 'back' => '1906 میلادی', 'notes' => 'اولین مجلس شورای ملی'],
                ['front' => 'رضا شاه پهلوی', 'back' => 'بنیانگذار پهلوی', 'notes' => '1925-1941'],
                ['front' => 'ملی شدن صنعت نفت', 'back' => '1951', 'notes' => 'دکتر محمد مصدق'],
                ['front' => 'انقلاب اسلامی', 'back' => '1357/1979', 'notes' => 'پایان سلطنت در ایران'],
            ],

            'جغرافیا' => [
                ['front' => 'پایتخت ایران', 'back' => 'تهران', 'tags' => ['capital', 'asia']],
                ['front' => 'پایتخت ترکیه', 'back' => 'آنکارا', 'tags' => ['capital', 'asia']],
                ['front' => 'پایتخت ژاپن', 'back' => 'توکیو', 'tags' => ['capital', 'asia']],
                ['front' => 'پایتخت چین', 'back' => 'پکن', 'tags' => ['capital', 'asia']],
                ['front' => 'پایتخت هند', 'back' => 'دهلی نو', 'tags' => ['capital', 'asia']],
                ['front' => 'پایتخت روسیه', 'back' => 'مسکو', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت آلمان', 'back' => 'برلین', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت فرانسه', 'back' => 'پاریس', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت انگلستان', 'back' => 'لندن', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت ایتالیا', 'back' => 'رم', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت اسپانیا', 'back' => 'مادرید', 'tags' => ['capital', 'europe']],
                ['front' => 'پایتخت آمریکا', 'back' => 'واشنگتن دی.سی', 'tags' => ['capital', 'america']],
                ['front' => 'پایتخت کانادا', 'back' => 'اتاوا', 'tags' => ['capital', 'america']],
                ['front' => 'پایتخت برزیل', 'back' => 'برازیلیا', 'tags' => ['capital', 'america']],
                ['front' => 'پایتخت آرژانتین', 'back' => 'بوئنوس آیرس', 'tags' => ['capital', 'america']],
                ['front' => 'پایتخت استرالیا', 'back' => 'کانبرا', 'tags' => ['capital', 'oceania']],
                ['front' => 'پایتخت مصر', 'back' => 'قاهره', 'tags' => ['capital', 'africa']],
                ['front' => 'پایتخت آفریقای جنوبی', 'back' => 'پرتوریا', 'tags' => ['capital', 'africa']],
                ['front' => 'بلندترین قله جهان', 'back' => 'اورست', 'notes' => '8848 متر'],
                ['front' => 'بلندترین قله ایران', 'back' => 'دماوند', 'notes' => '5610 متر'],
            ],

            'ریاضی' => [
                ['front' => 'مساحت دایره', 'back' => 'πr²', 'notes' => 'r = شعاع'],
                ['front' => 'محیط دایره', 'back' => '2πr', 'notes' => 'r = شعاع'],
                ['front' => 'مساحت مربع', 'back' => 'a²', 'notes' => 'a = ضلع'],
                ['front' => 'مساحت مستطیل', 'back' => 'طول × عرض', 'notes' => 'l × w'],
                ['front' => 'مساحت مثلث', 'back' => '½ × قاعده × ارتفاع', 'notes' => '½bh'],
                ['front' => 'حجم مکعب', 'back' => 'a³', 'notes' => 'a = ضلع'],
                ['front' => 'حجم کره', 'back' => '⁴⁄₃πr³', 'notes' => 'r = شعاع'],
                ['front' => 'قضیه فیثاغورس', 'back' => 'a² + b² = c²', 'notes' => 'در مثلث قائم الزاویه'],
                ['front' => 'مشتق x^n', 'back' => 'nx^(n-1)', 'notes' => 'قانون توان'],
                ['front' => 'انتگرال x^n', 'back' => 'x^(n+1)/(n+1) + C', 'notes' => 'n ≠ -1'],
                ['front' => 'sin²θ + cos²θ', 'back' => '1', 'notes' => 'اتحاد مثلثاتی'],
                ['front' => 'مشتق sin(x)', 'back' => 'cos(x)', 'notes' => 'مشتق سینوس'],
                ['front' => 'مشتق cos(x)', 'back' => '-sin(x)', 'notes' => 'مشتق کسینوس'],
                ['front' => 'لگاریتم حاصلضرب', 'back' => 'log(ab) = log(a) + log(b)', 'notes' => 'خاصیت لگاریتم'],
                ['front' => 'e^(ln x)', 'back' => 'x', 'notes' => 'x > 0'],
                ['front' => 'فرمول ترکیب', 'back' => 'C(n,r) = n!/(r!(n-r)!)', 'notes' => 'انتخاب r از n'],
                ['front' => 'فرمول جایگشت', 'back' => 'P(n,r) = n!/(n-r)!', 'notes' => 'چینش r از n'],
                ['front' => 'مجموع اعداد 1 تا n', 'back' => 'n(n+1)/2', 'notes' => 'فرمول گاوس'],
                ['front' => 'تعداد اعداد اول', 'back' => 'بینهایت', 'notes' => 'اثبات اقلیدس'],
                ['front' => 'عدد طلایی', 'back' => '(1+√5)/2 ≈ 1.618', 'notes' => 'نسبت طلایی'],
                ['front' => 'Big O(n²)', 'back' => 'زمان مربعی', 'notes' => 'مرتب‌سازی حبابی'],
                ['front' => 'Big O(2^n)', 'back' => 'زمان نمایی', 'notes' => 'مسائل NP'],
                ['front' => 'Stack', 'back' => 'پشته - LIFO', 'notes' => 'آخرین ورودی، اولین خروجی'],
                ['front' => 'Queue', 'back' => 'صف - FIFO', 'notes' => 'اولین ورودی، اولین خروجی'],
                ['front' => 'Linked List', 'back' => 'لیست پیوندی', 'notes' => 'عناصر با اشاره‌گر'],
                ['front' => 'Binary Tree', 'back' => 'درخت دودویی', 'notes' => 'هر گره حداکثر 2 فرزند'],
                ['front' => 'Hash Table', 'back' => 'جدول هش', 'notes' => 'جستجوی O(1)'],
                ['front' => 'Graph', 'back' => 'گراف', 'notes' => 'گره‌ها و یال‌ها'],
                ['front' => 'DFS', 'back' => 'جستجوی عمق اول', 'notes' => 'Depth First Search'],
                ['front' => 'BFS', 'back' => 'جستجوی سطح اول', 'notes' => 'Breadth First Search'],
                ['front' => 'Dijkstra', 'back' => 'کوتاه‌ترین مسیر', 'notes' => 'گراف وزن‌دار'],
                ['front' => 'Dynamic Programming', 'back' => 'برنامه‌نویسی پویا', 'notes' => 'حل مسائل بهینه‌سازی'],
                ['front' => 'Recursion', 'back' => 'بازگشت', 'notes' => 'تابع خود را صدا می‌زند'],
                ['front' => 'Binary Search', 'back' => 'جستجوی دودویی', 'notes' => 'O(log n) در آرایه مرتب'],
                ['front' => 'Merge Sort', 'back' => 'مرتب‌سازی ادغام', 'notes' => 'تقسیم و حل O(n log n)'],
                ['front' => 'Quick Sort', 'back' => 'مرتب‌سازی سریع', 'notes' => 'میانگین O(n log n)'],
            ],
            'عمومی' => [
                ['front' => 'کدام زبان برنامه‌نویسی محبوب‌تر است؟', 'back' => 'بستگی به کاربرد دارد', 'notes' => 'Python, JavaScript, Java'],
                ['front' => 'REST API', 'back' => 'رابط برنامه‌نویسی RESTful', 'notes' => 'معماری وب سرویس'],
                ['front' => 'MVC', 'back' => 'Model-View-Controller', 'notes' => 'الگوی معماری نرم‌افزار'],
                ['front' => 'Git', 'back' => 'سیستم کنترل نسخه', 'notes' => 'ابزار مدیریت کد'],
                ['front' => 'Docker', 'back' => 'پلتفرم کانتینر', 'notes' => 'مجازی‌سازی سطح سیستم‌عامل'],
                ['front' => 'Kubernetes', 'back' => 'سیستم مدیریت کانتینر', 'notes' => 'K8s'],
                ['front' => 'CI/CD', 'back' => 'یکپارچه‌سازی/استقرار مداوم', 'notes' => 'Continuous Integration/Deployment'],
                ['front' => 'Agile', 'back' => 'روش توسعه چابک', 'notes' => 'متدولوژی نرم‌افزار'],
                ['front' => 'Scrum', 'back' => 'چارچوب مدیریت پروژه', 'notes' => 'بخشی از Agile'],
                ['front' => 'DevOps', 'back' => 'توسعه و عملیات', 'notes' => 'فرهنگ و شیوه کاری'],
            ],
        ];
    }
}
