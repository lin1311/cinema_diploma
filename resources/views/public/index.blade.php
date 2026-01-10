@extends('layouts.app')

@section('title', 'ИдёмВКино')

@section('content')
  <header class="page-header">
    <h1 class="page-header__title">
      <a class="page-header__title-link" href="{{ url('/') }}">Идём<span>в</span>кино</a>
    </h1>
  </header>

  <nav class="page-nav" data-role="date-slider">
    @for ($i = 0; $i < 6; $i++)
      <a class="page-nav__day" href="#" data-role="date-day">
        <span class="page-nav__day-week"></span><span class="page-nav__day-number"></span>
      </a>
    @endfor
    <a class="page-nav__day page-nav__day_next" href="#" data-role="date-next"></a>
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
                @php
                  $seanceId = data_get($seance, 'id') ?? data_get($seance, 'seance_id');
                @endphp
                <li class="movie-seances__time-block">
                  <a class="movie-seances__time"
                     href="{{ $seanceId ? route('client.hall', ['seance' => $seanceId]) : '#' }}"
                     data-role="seance-link"
                     data-base-href="{{ $seanceId ? route('client.hall', ['seance' => $seanceId]) : '#' }}">
                    {{ data_get($seance, 'start_time') }}
                  </a>
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
  <script src="{{ asset('assets/client/js/data_slider.js') }}"></script>
@endsection
