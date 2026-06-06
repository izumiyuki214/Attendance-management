<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakCorrection extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'break_corrections';

    /**
     * 一括割り当て可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_correction_id',
        'break_start',
        'break_end',
    ];

    /**
     * キャスト対象の属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    /**
     * 休憩修正情報の所有修正申請
     *
     * @return BelongsTo
     */
    public function attendanceCorrection(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class);
    }

    /**
     * 休憩時間計算（分単位）
     *
     * @return int
     */
    public function getBreakMinutes(): int
    {
        return $this->break_end->diffInMinutes($this->break_start);
    }

    /**
     * 休憩時間計算（時間単位）
     *
     * @return float
     */
    public function getBreakHours(): float
    {
        return $this->getBreakMinutes() / 60;
    }
}