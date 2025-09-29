<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogReadController extends Controller
{
    public function index()
    {
        $logReads = LogRead::query()->where('is_main', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('log-reads.index', compact('logReads'));
    }

    public function read(string $path, string $fileName)
    {
        $files = File::allFiles($path);

        foreach ($files as $key => $file) {
            if ($file->getExtension() !== 'md') {
                unset($files[$key]);
            }
        }

        return view('read', compact('path', 'fileName', 'files'));
    }

    public function chart(){
        $contributionData = $this->generateContributionData();
        $stats = $this->calculateStats($contributionData);
        $weeks = $this->groupByWeeks($contributionData);

        return view('log-reads.chart', compact('contributionData', 'stats', 'weeks'));
    }

    public function generateContributionData() {
        $data = [];
        $oneYearAgo = Carbon::now()->subYear();
        $now = now();

        while ($oneYearAgo->timestamp <= $now->timestamp) {
            $minutes = LogRead::query()->where('day',$oneYearAgo->format('Y-m-d'))->count();
            $level = 0;
            if ($minutes > 0) $level = 1;
            if ($minutes > 25) $level = 2;
            if ($minutes > 50) $level = 3;
            if ($minutes > 75) $level = 4;

            $data[] = [
                'date' => $oneYearAgo->format('Y-m-d'),
                'minutes' => $minutes,
                'level' => $level,
                'day_of_week' => $oneYearAgo->format('w'), // 0=یکشنبه
                'persian_date' => $oneYearAgo->format('Y/m/d')
            ];

            $oneYearAgo->addDay();
        }

        return $data;
    }

// محاسبه آمار
    public function calculateStats($data) {
        $totalMinutes = array_sum(array_column($data, 'minutes'));
        $bestDay = max(array_column($data, 'minutes'));

        // محاسبه streak فعلی
        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;

        foreach (array_reverse($data) as $day) {
            if ($day['minutes'] > 0) {
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
            'total' => $totalMinutes,
            'best_day' => $bestDay,
            'current_streak' => $currentStreak,
            'longest_streak' => max($longestStreak, $tempStreak)
        ];
    }

// گروه‌بندی داده‌ها بر اساس هفته
    public function groupByWeeks($data) {
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
}
