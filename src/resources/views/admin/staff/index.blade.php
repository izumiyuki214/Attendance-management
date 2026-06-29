@extends('layouts.app')
@section('title', 'スタッフ一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection
@section('content')
<div class="attendance-list">

    <h1 class="attendance-list__title">スタッフ一覧</h1>

    <table class="attendance-table">
        <thead class="attendance-table__head">
            <tr>
                <th class="attendance-table__th">名前</th>
                <th class="attendance-table__th">メールアドレス</th>
                <th class="attendance-table__th">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffs as $staff)
                <tr class="attendance-table__row">
                    <td class="attendance-table__td">{{ $staff->name }}</td>
                    <td class="attendance-table__td">{{ $staff->email }}</td>
                    <td class="attendance-table__td attendance-table__td-black">
                        <a href="{{ route('admin.staff.show', $staff->id) }}"
                           class="attendance-table__link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="attendance-table__empty">スタッフが登録されていません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection