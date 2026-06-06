@extends('layouts.app')
@section('title', '会員登録')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection
@section('content')
<div class="auth">
    <div class="auth__card">
        <h1 class="auth__title">会員登録</h1>
        <form action="{{ route('register') }}" method="POST" class="form">
            @csrf
            <div class="form__group">
                <label class="form__label">名前</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form__input">
                @error('name')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
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
            <div class="form__group">
                <label class="form__label">パスワード確認</label>
                <input type="password" name="password_confirmation" class="form__input">
                @error('password_confirmation')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="button button--primary button--block">登録する</button>
        </form>
        <p class="auth__switch">
            <a href="{{ url('/login') }}">ログインはこちら</a>
        </p>
    </div>
</div>
@endsection