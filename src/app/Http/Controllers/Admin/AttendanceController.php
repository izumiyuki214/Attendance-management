<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * 管理者：勤怠一覧画面（日別・全ユーザー）
     */
    public function index(Request $request): View
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : Carbon::today();

        $attendances = AttendanceRecord::with(['user', 'breakRecords'])
            ->whereDate('date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'date'));
    }

    /**
     * 管理者：勤怠詳細画面
     */
    public function show(int $id): View
    {
        $attendance = AttendanceRecord::with(['user', 'breakRecords'])
            ->findOrFail($id);

        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * 管理者：勤怠修正処理
     */
    public function update(AttendanceRequest $request, int $id): RedirectResponse
    {
        $attendance = AttendanceRecord::findOrFail($id);

        $date = Carbon::parse($attendance->date);

        $attendance->update([
            'clock_in'  => $date->copy()->setTimeFromTimeString($request->clock_in),
            'clock_out' => $date->copy()->setTimeFromTimeString($request->clock_out),
            'comment'   => $request->comment,
        ]);

        // 既存の休憩レコードを全削除して再登録
        $attendance->breakRecords()->delete();

        foreach ($request->breaks ?? [] as $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd   = $break['break_end']   ?? null;

            if (!$breakStart) {
                continue;
            }

            BreakRecord::create([
                'attendance_record_id' => $attendance->id,
                'break_start' => $date->copy()->setTimeFromTimeString($breakStart),
                'break_end'   => $breakEnd
                    ? $date->copy()->setTimeFromTimeString($breakEnd)
                    : null,
            ]);
        }

        return redirect()->route('admin.attendance.show', $id);
    }
}