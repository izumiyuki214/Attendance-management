@extends('layouts.app')
@section('title', '申請一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection
@section('content')
<div class="correction-list">

    <h1 class="correction-list__title">申請一覧</h1>

    <div class="correction-list__tabs">
        <a
            href="{{ route('admin.correction.list', ['status' => 'pending']) }}"
            class="correction-list__tab {{ $status === 'pending' ? 'correction-list__tab--active' : '' }}"
        >
            承認待ち
        </a>
        <a
            href="{{ route('admin.correction.list', ['status' => 'approved']) }}"
            class="correction-list__tab {{ $status === 'approved' ? 'correction-list__tab--active' : '' }}"
        >
            承認済み
        </a>
    </div>

    <table class="correction-table">
        <thead>
            <tr class="correction-table__row">
                <th class="correction-table__th">状態</th>
                <th class="correction-table__th">名前</th>
                <th class="correction-table__th">対象日時</th>
                <th class="correction-table__th">申請理由</th>
                <th class="correction-table__th">申請日時</th>
                <th class="correction-table__th">詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($corrections as $correction)
                <tr class="correction-table__row">
                    <td class="correction-table__td">
                        {{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}
                    </td>
                    <td class="correction-table__td">
                        {{ $correction->user->name }}
                    </td>
                    <td class="correction-table__td">
                        {{ \Carbon\Carbon::parse($correction->attendanceRecord->date)->format('Y/m/d') }}
                    </td>
                    <td class="correction-table__td">
                        {{ $correction->comment }}
                    </td>
                    <td class="correction-table__td">
                        {{ $correction->created_at->format('Y/m/d') }}
                    </td>
                    <td class="correction-table__td">
                        <a
                            href="{{ route('admin.correction.show', $correction->id) }}"
                            class="correction-table__link"
                        >
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr class="correction-table__row">
                    <td class="correction-table__td" colspan="6">該当する申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection