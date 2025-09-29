<?php
// تولید داده‌های contribution برای یک سال گذشته
function generateContributionData() {
    $data = [];
    $today = new DateTime();
    $oneYearAgo = clone $today;
    $oneYearAgo->sub(new DateInterval('P365D'));
    
    $currentDate = clone $oneYearAgo;
    
    while ($currentDate <= $today) {
        $contributions = rand(0, 19); // تصادفی بین 0-19
        $level = 0;
        if ($contributions > 0) $level = 1;
        if ($contributions > 3) $level = 2;
        if ($contributions > 7) $level = 3;
        if ($contributions > 12) $level = 4;
        
        $data[] = [
            'date' => $currentDate->format('Y-m-d'),
            'contributions' => $contributions,
            'level' => $level,
            'day_of_week' => $currentDate->format('w'), // 0=یکشنبه
            'persian_date' => $currentDate->format('Y/m/d')
        ];
        
        $currentDate->add(new DateInterval('P1D'));
    }
    
    return $data;
}

// محاسبه آمار
function calculateStats($data) {
    $totalContributions = array_sum(array_column($data, 'contributions'));
    $bestDay = max(array_column($data, 'contributions'));
    
    // محاسبه streak فعلی
    $currentStreak = 0;
    $longestStreak = 0;
    $tempStreak = 0;
    
    foreach (array_reverse($data) as $day) {
        if ($day['contributions'] > 0) {
            $tempStreak++;
            if ($currentStreak == 0) {
                $currentStreak = $tempStreak;
            }
        } else {
            if ($tempStreak > $longestStreak) {
                $longestStreak = $tempStreak;
            }
            $tempStreak = 0;
            if ($currentStreak > 0) {
                break;
            }
        }
    }
    
    return [
        'total' => $totalContributions,
        'best_day' => $bestDay,
        'current_streak' => $currentStreak,
        'longest_streak' => max($longestStreak, $tempStreak)
    ];
}

// گروه‌بندی داده‌ها بر اساس هفته
function groupByWeeks($data) {
    $weeks = [];
    $currentWeek = [];
    
    foreach ($data as $day) {
        if ($day['day_of_week'] == 0 && !empty($currentWeek)) {
            $weeks[] = $currentWeek;
            $currentWeek = [];
        }
        $currentWeek[] = $day;
    }
    
    if (!empty($currentWeek)) {
        $weeks[] = $currentWeek;
    }
    
    return $weeks;
}

$contributionData = generateContributionData();
$stats = calculateStats($contributionData);
$weeks = groupByWeeks($contributionData);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Contribution Graph - PHP Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contribution-day {
            width: 11px;
            height: 11px;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.1s ease;
        }
        .contribution-day:hover {
            outline: 1px solid rgba(27, 31, 36, 0.15);
            outline-offset: 2px;
            transform: scale(1.1);
        }
        
        /* سطح‌های مختلف فعالیت */
        .level-0 { background-color: #ebedf0; }
        .level-1 { background-color: #9be9a8; }
        .level-2 { background-color: #40c463; }
        .level-3 { background-color: #30a14e; }
        .level-4 { background-color: #216e39; }
        
        /* Tooltip styles */
        .tooltip {
            position: absolute;
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            transform: translateX(-50%);
        }
        
        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
        
        .tooltip.show {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <i class="fab fa-github mr-2"></i>
                GitHub Contribution Graph
            </h1>
            <div class="flex items-center justify-between">
                <p class="text-gray-600"><?= number_format($stats['total']) ?> contributions in the last year</p>
                <div class="flex items-center space-x-2 text-xs text-gray-600">
                    <span>Less</span>
                    <div class="flex space-x-1">
                        <div class="contribution-day level-0"></div>
                        <div class="contribution-day level-1"></div>
                        <div class="contribution-day level-2"></div>
                        <div class="contribution-day level-3"></div>
                        <div class="contribution-day level-4"></div>
                    </div>
                    <span>More</span>
                </div>
            </div>
        </div>

        <!-- Graph Container -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 overflow-x-auto">
            <!-- Month Labels -->
            <div class="flex mb-2 text-xs text-gray-600">
                <div class="w-8"></div>
                <div class="flex-1 flex justify-between px-1">
                    <span>Jan</span>
                    <span>Feb</span>
                    <span>Mar</span>
                    <span>Apr</span>
                    <span>May</span>
                    <span>Jun</span>
                    <span>Jul</span>
                    <span>Aug</span>
                    <span>Sep</span>
                    <span>Oct</span>
                    <span>Nov</span>
                    <span>Dec</span>
                </div>
            </div>

            <!-- Graph Grid -->
            <div class="flex">
                <!-- Day Labels -->
                <div class="flex flex-col justify-between text-xs text-gray-600 mr-2 h-20">
                    <span>Mon</span>
                    <span>Wed</span>
                    <span>Fri</span>
                </div>

                <!-- Contribution Grid -->
                <div class="flex space-x-1">
                    <?php foreach ($weeks as $weekIndex => $week): ?>
                        <div class="flex flex-col space-y-1">
                            <?php 
                            // اضافه کردن فضای خالی در ابتدای هفته اول
                            if ($weekIndex === 0 && !empty($week)) {
                                $firstDayOfWeek = $week[0]['day_of_week'];
                                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                    echo '<div class="contribution-day level-0"></div>';
                                }
                            }
                            ?>
                            
                            <?php foreach ($week as $day): ?>
                                <div class="contribution-day level-<?= $day['level'] ?>" 
                                     data-contributions="<?= $day['contributions'] ?>"
                                     data-date="<?= $day['date'] ?>"
                                     data-persian-date="<?= $day['persian_date'] ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['total']) ?></div>
                <div class="text-sm text-gray-600">Total contributions</div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-2xl font-bold text-green-600"><?= $stats['best_day'] ?></div>
                <div class="text-sm text-gray-600">Best day</div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-2xl font-bold text-blue-600"><?= $stats['current_streak'] ?></div>
                <div class="text-sm text-gray-600">Current streak</div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="text-2xl font-bold text-purple-600"><?= $stats['longest_streak'] ?></div>
                <div class="text-sm text-gray-600">Longest streak</div>
            </div>
        </div>

        <!-- Detailed Stats -->
        <div class="mt-6 bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold mb-4">Contribution Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                <?php
                $levelCounts = array_count_values(array_column($contributionData, 'level'));
                $levelNames = ['No contributions', '1-3 contributions', '4-7 contributions', '8-12 contributions', '13+ contributions'];
                ?>
                <?php for ($i = 0; $i <= 4; $i++): ?>
                    <div class="flex items-center space-x-2">
                        <div class="contribution-day level-<?= $i ?>"></div>
                        <span><?= $levelCounts[$i] ?? 0 ?> days</span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Tooltip Element -->
    <div id="tooltip" class="tooltip"></div>

    <script>
        // فقط برای tooltip و interaction
        document.addEventListener('DOMContentLoaded', function() {
            const contributionDays = document.querySelectorAll('.contribution-day[data-contributions]');
            const tooltip = document.getElementById('tooltip');
            
            contributionDays.forEach(day => {
                day.addEventListener('mouseenter', function(e) {
                    const contributions = this.dataset.contributions;
                    const date = this.dataset.date;
                    const persianDate = this.dataset.persianDate;
                    
                    tooltip.innerHTML = `${contributions} contributions on ${persianDate}`;
                    tooltip.style.left = (e.pageX - tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = (e.pageY - tooltip.offsetHeight - 10) + 'px';
                    tooltip.classList.add('show');
                });
                
                day.addEventListener('mouseleave', function() {
                    tooltip.classList.remove('show');
                });
                
                day.addEventListener('mousemove', function(e) {
                    tooltip.style.left = (e.pageX - tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = (e.pageY - tooltip.offsetHeight - 10) + 'px';
                });
            });
        });
    </script>
</body>
</html>