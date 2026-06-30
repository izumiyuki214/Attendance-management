<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use App\Models\BreakRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorrectionController extends Controller
{
    private const STATUS_PENDING  = 'pending';
    private const STATUS_APPROVED = 'approved';

    /**
     * 管理者：申請一覧画面（全スタッフ分）
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', self::STATUS_PENDING);

        $corrections = AttendanceCorrection::with(['user', 'attendanceRecord'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.correction.index', compact('corrections', 'status'));
    }

    /**
     * 管理者：修正申請承認画面
     */
    public function show(int $attendance_correct_request_id): View
    {
        $correction = AttendanceCorrection::with(['user', 'attendanceRecord', 'breakCorrections'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.correction.approve', compact('correction'));
    }

    /**
     * 管理者：修正申請承認処理
     */
    public function approve(int $attendance_correct_request_id): RedirectResponse
    {
        $correction = AttendanceCorrection::with('breakCorrections')
            ->findOrFail($attendance_correct_request_id);

        $attendance = $correction->attendanceRecord;

        $attendance->update([
            'clock_in'  => $correction->clock_in,
            'clock_out' => $correction->clock_out,
            'comment'   => $correction->comment,
        ]);

        $attendance->breakRecords()->delete();

        foreach ($correction->breakCorrections as $breakCorrection) {
            BreakRecord::create([
                'attendance_record_id' => $attendance->id,
                'break_start' => $breakCorrection->break_start,
                'break_end'   => $breakCorrection->break_end,
            ]);
        }

        $correction->update(['status' => self::STATUS_APPROVED]);

        return redirect()->route('admin.correction.show', $attendance_correct_request_id);
    }
}