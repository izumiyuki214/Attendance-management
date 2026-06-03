<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'attendance_records';

    /**
     * 一括割り当て可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'comment',
    ];

    /**
     * キャスト対象の属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    /**
     * ステータスの定数
     */
    const STATUS_OFF_WORK = 'off_work';
    const STATUS_WORKING = 'working';
    const STATUS_ON_BREAK = 'on_break';
    const STATUS_FINISHED = 'finished';

    /**
     * 勤怠記録の所有ユーザー
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤怠記録の休憩記録
     *
     * @return HasMany
     */
    public function breakRecords(): HasMany
    {
        return $this->hasMany(BreakRecord::class);
    }

    /**
     * 勤怠記録の修正申請
     *
     * @return HasMany
     */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /**
     * 労働時間計算（分単位）
     *
     * @return int|null
     */
    public function getWorkingMinutes(): ?int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $workingTime = $this->clock_out->diffInMinutes($this->clock_in);
        $breakTime = $this->breakRecords->sum(function (BreakRecord $break) {
            if ($break->break_end) {
                return $break->break_end->diffInMinutes($break->break_start);
            }
            return 0;
        });

        return $workingTime - $breakTime;
    }

    /**
     * 労働時間計算（時間単位）
     *
     * @return float|null
     */
    public function getWorkingHours(): ?float
    {
        $minutes = $this->getWorkingMinutes();
        if ($minutes === null) {
            return null;
        }

        return $minutes / 60;
    }
}