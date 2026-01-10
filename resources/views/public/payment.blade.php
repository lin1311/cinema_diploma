@extends('layouts.app')

@section('title', 'Оплата')

@section('content')
  <header class="page-header">
    <h1 class="page-header__title">
      <a class="page-header__title-link" href="{{ url('/') }}">Идём<span>в</span>кино</a>
    </h1>
  </header>

  <main>
    <section class="ticket">
      <header class="tichet__check">
        <h2 class="ticket__check-title">Вы выбрали билеты:</h2>
      </header>

      <div class="ticket__info-wrapper">
        <p class="ticket__info">
          На фильм: <span class="ticket__details ticket__title">{{ $movie['title'] ?? 'Без названия' }}</span>
        </p>
        <p class="ticket__info">
          Места: <span class="ticket__details ticket__chairs">{{ $seatsLabel }}</span>
        </p>
        <p class="ticket__info">
          В зале: <span class="ticket__details ticket__hall">{{ $hall['name'] ?? 'Зал' }}</span>
        </p>
        <p class="ticket__info">
          Начало сеанса: <span class="ticket__details ticket__start">{{ data_get($seance, 'start_time') }}</span>
        </p>
        <p class="ticket__info">
          Стоимость: <span class="ticket__details ticket__cost">{{ $totalCost }}</span> рублей
        </p>

        <button class="acceptin-button" type="button" onclick="location.href='{{ route('client.ticket', ['seance' => data_get($seance, 'id')]) }}'">
          Получить код бронирования
        </button>

        <p class="ticket__hint">
          После оплаты билет будет доступен в этом окне, а также придёт вам на почту.
          Покажите QR-код нашему контроллёру у входа в зал.
        </p>
        <p class="ticket__hint">Приятного просмотра!</p>
      </div>
    </section>
  </main>
@endsection