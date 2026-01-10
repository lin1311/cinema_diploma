@extends('layouts.app')

@section('title', 'Выбор мест')

@section('content')
  <header class="page-header">
    <h1 class="page-header__title">
      <a class="page-header__title-link" href="{{ url('/') }}">Идём<span>в</span>кино</a>
    </h1>
  </header>

  <main>
    <section class="buying">
      <div class="buying__info">
        <div class="buying__info-description">
          <h2 class="buying__info-title">{{ $movie['title'] ?? 'Без названия' }}</h2>
          <p class="buying__info-start">
            Начало сеанса: {{ $dateLabel ?? '' }} в {{ data_get($seance, 'start_time') }}
          </p>
          <p class="buying__info-hall">{{ $hall['name'] ?? 'Зал' }}</p>
        </div>
        <div class="buying__info-hint">
          <p>Тапните дважды,<br>чтобы увеличить</p>
        </div>
      </div>
      <div class="buying-scheme">
        <div class="buying-scheme__wrapper">
          @foreach(($scheme['seatsGrid'] ?? []) as $row)
            <div class="buying-scheme__row">
              @foreach($row as $seatType)
                @php
                  $normalizedType = in_array($seatType, ['standart', 'vip', 'disabled'], true)
                    ? $seatType
                    : 'standart';
                @endphp
                <span class="buying-scheme__chair buying-scheme__chair_{{ $normalizedType }}"></span>
              @endforeach
            </div>
          @endforeach
        </div>
        <div class="buying-scheme__legend">
            @php
            $standartPrice = data_get($prices, 'standart');
            $vipPrice = data_get($prices, 'vip');
            @endphp
            <div class="col">
                <p class="buying-scheme__legend-price">
                    <span class="buying-scheme__chair buying-scheme__chair_standart"></span>
                    Свободно (<span class="buying-scheme__legend-value">{{ $standartPrice ?? '—' }}</span>руб)
                </p>
                <p class="buying-scheme__legend-price">
                    <span class="buying-scheme__chair buying-scheme__chair_vip"></span>
                    Свободно VIP (<span class="buying-scheme__legend-value">{{ $vipPrice ?? '—' }}</span>руб)
                </p>
            </div>
            <div class="col">
                <p class="buying-scheme__legend-price"><span class="buying-scheme__chair buying-scheme__chair_taken"></span> Занято</p>
                <p class="buying-scheme__legend-price"><span class="buying-scheme__chair buying-scheme__chair_selected"></span> Выбрано</p>
            </div>
        </div>
      </div>
      <button class="acceptin-button" onclick="location.href='payment.html'">Забронировать</button>
    </section>
  </main>
@endsection
