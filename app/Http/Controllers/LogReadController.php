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

    public function chart()
    {
        $contributionData = $this->generateContributionData();
        $stats = $this->calculateStats($contributionData);
        $weeks = $this->groupByWeeks($contributionData);

        return view('log-reads.chart', compact('contributionData', 'stats', 'weeks'));
    }

    public function generateContributionData()
    {
        $data = [];
        $oneYearAgo = Carbon::now()->subYear();
        $now = now();

        while ($oneYearAgo->timestamp <= $now->timestamp) {
            $minutes = LogRead::query()->where('day', $oneYearAgo->format('Y-m-d'))->count();
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
    public function calculateStats($data)
    {
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
    public function groupByWeeks($data)
    {
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

    private function startTimer(string $logName)
    {
        $log = LogRead::query()
            ->where('name', $logName)
            ->where('user_id', auth()->id())
            ->first();

        if($log !== null) {
            return response()->json([
                'success' => true,
                'timestamp' => $log->created_at->format('Y-m-d H:i:s')
            ]);
        }

        $log = LogRead::query()->create([
            'user_id' => auth()->id(),
            'is_main' => true,
            'name' => $logName,
            'time' => 0,
            'day' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'timestamp' => $log->created_at->format('Y-m-d H:i:s')
        ]);
    }

    public function start()
    {
        return $this->startTimer('start!!start');
    }

    public function startWorker()
    {
        return $this->startTimer('start_work!!start_work');
    }

    private function endTimer(string $startLogName, string $betweenLogName, string $endLogName)
    {
        $log = LogRead::query()
            ->where('name', $startLogName)
            ->where('user_id', auth()->id())
            ->first();

        if ($log === null) {
            return response()->json([
                'success' => false,
                'error' => 'No valid start log found'
            ], 400);
        }

        $start = (int)($log->created_at->timestamp / 60);
        $end = (int)(Carbon::now()->format('U') / 60);

        for ($i = $start; $i < $end; $i++) {
            $endLog = LogRead::query()->create([
                'user_id' => auth()->id(),
                'is_main' => false,
                'name' => $betweenLogName,
                'time' => 0,
                'day' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
                'created_at' => $log->created_at->addMinute()->format('Y-m-d H:i:s'),
            ]);

            $log->time += 1;
            $log->save();
        }

        $log->name = $endLogName;
        $log->save();

        return response()->json([
            'success' => true,
            'start' => ['id' => $log->id, 'start' => $log->created_at->format('Y-m-d H:i:s'), 'time' => $log->time],
            'end' => ['id' => $endLog->id, 'end' => $endLog->created_at->format('Y-m-d H:i:s')],
        ]);
    }

    public function end()
    {
        return $this->endTimer('start!!start', 'between!!between', 'start!!end');
    }

    public function endWorker()
    {
        return $this->endTimer('start_work!!start_work', 'between_work!!between_work', 'start_work!!end_work');
    }


    public function timer()
    {
        return view('log-reads.timer');
    }

    public function worker()
    {
        return view('log-reads.worker');
    }
}
