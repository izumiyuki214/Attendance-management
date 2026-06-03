<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakRecord extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'break_records';

    /**
     * 一括割り当て可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_record_id',
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
     * 休憩記録の所有勤怠記録
     *
     * @return BelongsTo
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * 休憩時間計算（分単位）
     *
     * @return int|null
     */
    public function getBreakMinutes(): ?int
    {
        if (!$this->break_end) {
            return null;
        }

        return $this->break_end->diffInMinutes($this->break_start);
    }

    /**
     * 休憩時間計算（時間単位）
     *
     * @return float|null
     */
    public function getBreakHours(): ?float
    {
        $minutes = $this->getBreakMinutes();
        if ($minutes === null) {
            return null;
        }

        return $minutes / 60;
    }
}