@extends('layouts.app')

@section('title', 'Билет')

@section('content')
  <header class="page-header">
    <h1 class="page-header__title">
      <a class="page-header__title-link" href="{{ url('/') }}">Идём<span>в</span>кино</a>
    </h1>
  </header>
  
    <main>
        <section class="ticket">
        
        <header class="tichet__check">
            <h2 class="ticket__check-title">Электронный билет</h2>
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

            <img class="ticket__info-qr" src="{{ asset('assets/client/i/qr-code.png') }}" alt="QR код билета">

            <p class="ticket__hint">Покажите QR-код нашему контроллеру для подтверждения бронирования.</p>
            <p class="ticket__hint">Приятного просмотра!</p>
        </div>
        </section>     
    </main>
@endsection