<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StaffController extends Controller
{
    private const CSV_HEADERS = ['日付', '出勤', '退勤', '休憩', '合計'];

    /**
     * スタッフ一覧画面
     */
    public function index(): View
    {
        $staffs = User::where('admin_status', false)
            ->orderBy('id')
            ->get();

        return view('admin.staff.index', compact('staffs'));
    }

    /**
     * スタッフ別月次勤怠一覧画面 / CSV出力
     */
    public function show(Request $request, int $id): View|Response
    {
        $staff = User::where('admin_status', false)->findOrFail($id);

        $month = $request->input('month')
            ? Carbon::parse($request->input('month'))->startOfMonth()
            : Carbon::today()->startOfMonth();

        $attendances = AttendanceRecord::with('breakRecords')
            ->where('user_id', $staff->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->orderBy('date')
            ->get();

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($staff->name, $month, $attendances);
        }

        return view('admin.attendance.staff', compact('staff', 'attendances', 'month'));
    }

    /**
     * CSV出力
     */
    private function exportCsv(string $staffName, Carbon $month, $attendances): Response
    {
        $filename = sprintf(
            '%s_%s.csv',
            $staffName,
            $month->format('Y-m')
        );

        $rows = $attendances->map(function (AttendanceRecord $attendance): array {
            $breakMinutes = $attendance->breakRecords
                ->filter(fn($b) => $b->break_end)
                ->sum(fn($b) => Carbon::parse($b->break_start)
                    ->diffInMinutes(Carbon::parse($b->break_end)));

            $workMinutes = ($attendance->clock_in && $attendance->clock_out)
                ? Carbon::parse($attendance->clock_in)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out)) - $breakMinutes
                : 0;

            $formatMinutes = fn(int $m): string => sprintf('%d:%02d', intdiv($m, 60), $m % 60);

            return [
                Carbon::parse($attendance->date)->format('Y/m/d'),
                $attendance->clock_in  ? Carbon::parse($attendance->clock_in)->format('H:i')  : '-',
                $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                $breakMinutes > 0 ? $formatMinutes($breakMinutes) : '-',
                $workMinutes  > 0 ? $formatMinutes($workMinutes)  : '-',
            ];
        });

        $csv = collect([self::CSV_HEADERS])
            ->concat($rows)
            ->map(fn(array $row): string => implode(',', $row))
            ->implode("\n");

        // Windows対応：BOM付きUTF-8
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}