@extends('layouts.app')
@section('title', '管理者ログイン')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection
@section('content')
<div class="auth">
    <div class="auth__card">
        <h1 class="auth__title">管理者ログイン</h1>
        <form action="{{ route('admin.login') }}" method="POST" class="form">
            @csrf
            <div class="form__group">
                <label class="form__label">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form__input">
                @error('email')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label class="form__label">パスワード</label>
                <input type="password" name="password" class="form__input">
                @error('password')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="button button--primary button--block">管理者ログインする</button>
        </form>
    </div>
</div>
@endsection