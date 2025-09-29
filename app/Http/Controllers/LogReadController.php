<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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

    public function chart(string $type)
    {
        $contributionData = $this->generateContributionData($type);
        $stats = $this->calculateStats($contributionData);
        $weeks = $this->groupByWeeks($contributionData);

        return view('log-reads.chart', compact('contributionData', 'stats', 'weeks'));
    }

    public function generateContributionData($type)
    {
        $data = [];
        $oneYearAgo = Carbon::now()->subYear();
        $now = now();

        while ($oneYearAgo->timestamp <= $now->timestamp) {
            $minutes = LogRead::query()->where('day', $oneYearAgo->format('Y-m-d'))
                ->when($type === 'work', function ($query) {
                    $query->where('name', 'working!!');
                })->when($type === 'read', function ($query) {
                    $query->where('name', '!=', 'working!!');
                })->count();
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
        $existingLog = $this->findActiveLog($logName);

        if ($existingLog) {
            return $this->buildTimerResponse($existingLog);
        }

        $newLog = $this->createNewLog($logName);

        return $this->buildTimerResponse($newLog);
    }

    private function findActiveLog(string $logName)
    {
        return LogRead::query()
            ->where('name', $logName)
            ->where('user_id', auth()->id())
            ->whereNotNull('start')
            ->whereNull('end')
            ->first();
    }

    private function createNewLog(string $logName)
    {
        return LogRead::query()->create([
            'user_id' => auth()->id(),
            'is_main' => true,
            'name' => $logName,
            'time' => 0,
            'day' => now()->format('Y-m-d'),
            'start' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function buildTimerResponse($log)
    {
        return response()->json([
            'success' => true,
            'timestamp' => Carbon::parse($log->start)->format('Y-m-d H:i:s'),
        ]);
    }

    public function start()
    {
        return $this->startTimer('reading!!');
    }

    public function startWorker()
    {
        return $this->startTimer('working!!');
    }

    private function endTimer(string $startLogName)
    {
        $log = LogRead::query()
            ->where('name', $startLogName)
            ->where('user_id', auth()->id())
            ->whereNotNull('start')
            ->whereNull('end')
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
                'name' => $startLogName,
                'time' => 0,
                'day' => \Illuminate\Support\Carbon::now()->format('Y-m-d'),
            ]);

            $log->time += 1;
            $log->save();
        }

        if (!isset($endLog)) {
            return response()->json([
                'success' => false,
                'error' => 'No Elapsed One Minutes'
            ], 400);
        }

        $log->end = now()->format('Y-m-d H:i:s');
        $log->save();

        return response()->json([
            'success' => true,
            'start' => ['id' => $log->id, 'start' => Carbon::parse($log->start)->format('Y-m-d H:i:s'), 'time' => $log->time],
            'end' => ['id' => $endLog->id, 'end' => Carbon::parse($log->end)->format('Y-m-d H:i:s')],
        ]);
    }

    public function end(): JsonResponse
    {
        return $this->endTimer('reading!!');
    }

    public function endWorker(): JsonResponse
    {
        return $this->endTimer('working!!');
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
