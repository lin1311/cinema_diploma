@extends('layouts.admin')
@section('title', 'Админка | ИдёмВКино')
@section('content')
  <header class="page-header">
      <h1 class="page-header__title">Идём<span>в</span>кино</h1>
      <span class="page-header__subtitle">Администраторская</span>
    </header>
    
    <main>
      <section class="login">
        <header class="login__header">
          <h2 class="login__title">Авторизация</h2>
        </header>
        <div class="login__wrapper">
          <form class="login__form" action="{{ route('admin.login.submit') }}" method="POST" accept-charset="utf-8">
            @csrf
            <label class="login__label" for="email">
              E-mail
              <input class="login__input" type="text" placeholder="admin@example.com" name="email" value="{{ old('email') }}" required>
            </label>
            <label class="login__label" for="pwd">
              Пароль
              <input class="login__input" type="password" placeholder="" name="password" required>
            </label>
            @if ($errors->any())
              <p class="login__message">{{ $errors->first() }}</p>
            @endif
            <div class="text-center">
              <input value="Авторизоваться" type="submit" class="login__button">
            </div>
          </form>
        </div>
      </section>
    </main>
@endsection