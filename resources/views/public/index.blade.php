@extends('layouts.app')

@section('title', 'ИдёмВКино')

@section('content')
  <header class="page-header">
    <h1 class="page-header__title">Идём<span>в</span>кино</h1>
  </header>

  <nav class="page-nav">
    <a class="page-nav__day page-nav__day_today" href="#">
      <span class="page-nav__day-week">Пн</span><span class="page-nav__day-number">31</span>
    </a>
    <a class="page-nav__day" href="#">
      <span class="page-nav__day-week">Вт</span><span class="page-nav__day-number">1</span>
    </a>
    <a class="page-nav__day page-nav__day_chosen" href="#">
      <span class="page-nav__day-week">Ср</span><span class="page-nav__day-number">2</span>
    </a>
    <a class="page-nav__day" href="#">
      <span class="page-nav__day-week">Чт</span><span class="page-nav__day-number">3</span>
    </a>
    <a class="page-nav__day" href="#">
      <span class="page-nav__day-week">Пт</span><span class="page-nav__day-number">4</span>
    </a>
    <a class="page-nav__day page-nav__day_weekend" href="#">
      <span class="page-nav__day-week">Сб</span><span class="page-nav__day-number">5</span>
    </a>
    <a class="page-nav__day page-nav__day_next" href="#">
    </a>
  </nav>

  <main>
    @forelse ($movies as $movie)
      <section class="movie">
        <div class="movie__info">
          <div class="movie__poster">
            <img class="movie__poster-image" alt="{{ $movie->title ?? 'Постер фильма' }}"
                 src="{{ $movie->poster_url ?? asset('assets/client/i/poster1.jpg') }}">
          </div>
          <div class="movie__description">
            <h2 class="movie__title">{{ $movie->title ?? 'Без названия' }}</h2>
            @if (!empty($movie->description))
              <p class="movie__synopsis">{{ $movie->description }}</p>
            @endif
            <p class="movie__data">
              @if (!empty($movie->duration))
                <span class="movie__data-duration">{{ $movie->duration }} минут</span>
              @endif
              @if (!empty($movie->country))
                <span class="movie__data-origin">{{ $movie->country }}</span>
              @endif
            </p>
          </div>
        </div>

        @foreach ($seancesByMovie->get($movie->id, collect()) as $hallId => $hallSeances)
          @php $hall = $hallsById[$hallId] ?? null; @endphp
          <div class="movie-seances__hall">
            <h3 class="movie-seances__hall-title">{{ $hall['name'] ?? 'Зал' }}</h3>
            <ul class="movie-seances__list">
              @foreach ($hallSeances as $seance)
                <li class="movie-seances__time-block">
                  <a class="movie-seances__time" href="#">{{ $seance['start_time'] }}</a>
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </section>
    @empty
      <section class="movie">
        <div class="movie__info">
          <div class="movie__description">
            <h2 class="movie__title">Нет опубликованных сеансов</h2>
            <p class="movie__synopsis">Администратор ещё не опубликовал расписание сеансов.</p>
          </div>
        </div>
      </section>
    @endforelse
  </main>
@endsection
