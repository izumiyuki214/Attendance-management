@extends('layouts.app')
@section('title', $staff->name . 'さんの勤怠一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-staff.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
@endsection
@section('content')
<div class="attendance-list">

    <h1 class="attendance-list__title">{{ $staff->name }}さんの勤怠</h1>

    {{-- 月ナビゲーション --}}
    <div class="attendance-list__nav">
        <a href="{{ route('admin.staff.show', ['id' => $staff->id, 'month' => $month->copy()->subMonth()->format('Y-m')]) }}"
           class="attendance-list__nav-btn">
            &larr; 前月
        </a>
        <span class="attendance-list__month">
            <span class="material-icons attendance-list__month-icon">calendar_month</span>
            {{ $month->format('Y/m') }}
        </span>
        <a href="{{ route('admin.staff.show', ['id' => $staff->id, 'month' => $month->copy()->addMonth()->format('Y-m')]) }}"
           class="attendance-list__nav-btn">
            翌月 &rarr;
        </a>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-table">
        <thead class="attendance-table__head">
            <tr>
                <th class="attendance-table__th">日付</th>
                <th class="attendance-table__th">出勤</th>
                <th class="attendance-table__th">退勤</th>
                <th class="attendance-table__th">休憩</th>
                <th class="attendance-table__th">合計</th>
                <th class="attendance-table__th">詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                @php
                    $date      = \Carbon\Carbon::parse($attendance->date);
                    $dayOfWeek = ['日','月','火','水','木','金','土'][$date->dayOfWeek];

                    $breakMinutes = $attendance->breakRecords
                        ->filter(fn($b) => $b->break_end)
                        ->sum(fn($b) => \Carbon\Carbon::parse($b->break_start)
                            ->diffInMinutes(\Carbon\Carbon::parse($b->break_end)));

                    $workMinutes = ($attendance->clock_in && $attendance->clock_out)
                        ? \Carbon\Carbon::parse($attendance->clock_in)
                            ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $breakMinutes
                        : 0;

                    $formatMinutes = fn(int $m): string => sprintf('%d:%02d', intdiv($m, 60), $m % 60);
                @endphp
                <tr class="attendance-table__row">
                    <td class="attendance-table__td">
                        {{ $date->format('m/d') }}({{ $dayOfWeek }})
                    </td>
                    <td class="attendance-table__td">
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                    </td>
                    <td class="attendance-table__td">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
                    </td>
                    <td class="attendance-table__td">
                        {{ $breakMinutes > 0 ? $formatMinutes($breakMinutes) : '-' }}
                    </td>
                    <td class="attendance-table__td">
                        {{ $workMinutes > 0 ? $formatMinutes($workMinutes) : '-' }}
                    </td>
                    <td class="attendance-table__td attendance-table__td-black">
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}"
                           class="attendance-table__link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="attendance-table__empty">データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- CSV出力ボタン --}}
    <div class="staff-list__footer">
        <a href="{{ route('admin.staff.show', ['id' => $staff->id, 'month' => $month->format('Y-m'), 'export' => 'csv']) }}"
           class="button button--csv">
            CSV出力
        </a>
    </div>

</div>
@endsection