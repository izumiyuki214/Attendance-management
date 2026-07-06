<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    // 標準勤務時間（分）：1日8時間
    private const STANDARD_WORK_MINUTES = 480;

    // 長時間労働とみなす実労働時間のしきい値（分）：10時間超
    private const LONG_WORK_THRESHOLD_MINUTES = 600;

    // 標準の出勤・退勤時刻（H:i形式での比較用）
    private const STANDARD_START_TIME = '09:00';
    private const STANDARD_END_TIME   = '18:00';

    // 集計対象期間（過去6ヶ月）の月数
    private const PERIOD_MONTHS = 6;

    /**
     * マイ勤怠レポート画面を表示する
     */
    public function index(): View
    {
        $user = Auth::user();

        $periodStart = Carbon::today()->subMonths(self::PERIOD_MONTHS - 1)->startOfMonth();
        $periodEnd   = Carbon::today()->endOfMonth();

        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->with('breakRecords')
            ->get();

        $totalMinutes    = $records->sum(fn (AttendanceRecord $record) => $this->calculateNetMinutes($record));
        $overtimeMinutes = $records->sum(
            fn (AttendanceRecord $record) => max($this->calculateNetMinutes($record) - self::STANDARD_WORK_MINUTES, 0)
        );
        $averageMinutes = $records->isNotEmpty() ? intdiv($totalMinutes, $records->count()) : 0;

        $monthlyTrend = $this->buildMonthlyTrend($records, $periodStart);

        $thisMonthRecords = $records->filter(
            fn (AttendanceRecord $record) => Carbon::parse($record->date)->isSameMonth(Carbon::today())
        );

        $lateCount = $thisMonthRecords->filter(
            fn (AttendanceRecord $record) => Carbon::parse($record->clock_in)->format('H:i') > self::STANDARD_START_TIME
        )->count();

        $earlyLeaveCount = $thisMonthRecords->filter(
            fn (AttendanceRecord $record) => Carbon::parse($record->clock_out)->format('H:i') < self::STANDARD_END_TIME
        )->count();

        $longWorkCount = $thisMonthRecords->filter(
            fn (AttendanceRecord $record) => $this->calculateNetMinutes($record) > self::LONG_WORK_THRESHOLD_MINUTES
        )->count();

        return view('attendance.report', [
            'totalHours'             => $this->formatMinutes($totalMinutes),
            'overtimeHours'          => $this->formatMinutes($overtimeMinutes),
            'averageHours'           => $this->formatMinutes($averageMinutes),
            'monthlyTrend'           => $monthlyTrend,
            'lateCount'              => $lateCount,
            'earlyLeaveCount'        => $earlyLeaveCount,
            'longWorkCount'          => $longWorkCount,
            'standardStartTime'      => self::STANDARD_START_TIME,
            'standardEndTime'        => self::STANDARD_END_TIME,
            'longWorkThresholdHours' => intdiv(self::LONG_WORK_THRESHOLD_MINUTES, 60),
        ]);
    }

    /**
     * 月ごとの労働時間・残業時間の推移を生成する（対象期間内の全月、データがない月も0で表示する）
     */
    private function buildMonthlyTrend(Collection $records, Carbon $periodStart): array
    {
        $grouped = $records->groupBy(fn (AttendanceRecord $record) => Carbon::parse($record->date)->format('Y-m'));

        $trend = [];

        for ($i = 0; $i < self::PERIOD_MONTHS; $i++) {
            $month = $periodStart->copy()->addMonths($i);
            $key   = $month->format('Y-m');

            $monthRecords = $grouped->get($key, collect());

            $monthTotalMinutes = $monthRecords->sum(fn (AttendanceRecord $record) => $this->calculateNetMinutes($record));
            $monthOvertimeMinutes = $monthRecords->sum(
                fn (AttendanceRecord $record) => max($this->calculateNetMinutes($record) - self::STANDARD_WORK_MINUTES, 0)
            );

            $trend[] = [
                'month'         => $key,
                'workHours'     => $this->formatMinutes($monthTotalMinutes),
                'overtimeHours' => $this->formatMinutes($monthOvertimeMinutes),
            ];
        }

        return $trend;
    }

    /**
     * 勤怠レコードの実労働時間（分）を計算する（休憩時間を除く）
     */
    private function calculateNetMinutes(AttendanceRecord $record): int
    {
        $clockIn  = Carbon::parse($record->clock_in);
        $clockOut = Carbon::parse($record->clock_out);

        $totalMinutes = $clockIn->diffInMinutes($clockOut);

        $breakMinutes = $record->breakRecords
            ->filter(fn ($break) => $break->break_start && $break->break_end)
            ->sum(fn ($break) => Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end)));

        return max($totalMinutes - $breakMinutes, 0);
    }

    /**
     * 分を「○h ○m」形式の文字列に変換する
     */
    private function formatMinutes(int $minutes): string
    {
        $hours   = intdiv($minutes, 60);
        $remains = $minutes % 60;

        return "{$hours}h {$remains}m";
    }
}