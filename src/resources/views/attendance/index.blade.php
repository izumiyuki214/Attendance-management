@extends('layouts.app')
@section('title', '退勤登録')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection
@section('content')
<div class="attendance">
    {{-- ステータス表示 --}}
    <p class="attendance__status">
        @if ($status === 'off_work')
            勤務外
        @elseif ($status === 'working')
            出勤中
        @elseif ($status === 'on_break')
            休憩中
        @elseif ($status === 'finished')
            退勤済
        @endif
    </p>

    {{-- 現在日時 --}}
    <div class="attendance__datetime">
        <p class="attendance__date">{{ $now->format('Y年n月j日') }}（{{ ['日','月','火','水','木','金','土'][$now->dayOfWeek] }}）</p>
        <p class="attendance__time">{{ $now->format('H:i') }}</p>
    </div>


    {{-- 打刻ボタン --}}
    <div class="attendance__actions">
        @if ($status === 'off_work')
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="clock_in">
                <button type="submit" class="button button--primary button--attendance">出勤</button>
            </form>

        @elseif ($status === 'working')
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="clock_out">
                <button type="submit" class="button button--primary button--attendance">退勤</button>
            </form>
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="break_start">
                <button type="submit" class="button button--secondary button--attendance">休憩入</button>
            </form>

        @elseif ($status === 'on_break')
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="break_end">
                <button type="submit" class="button button--secondary button--attendance">休憩戻</button>
            </form>

        @elseif ($status === 'finished')
            <p class="attendance__finished-message">お疲れ様でした。</p>
        @endif
    </div>

</div>
@endsection