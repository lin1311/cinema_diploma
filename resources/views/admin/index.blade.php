@extends('layouts.admin')
@section('title', 'Админка | ИдёмВКино')
@section('content')
  <header class="page-header">
    <h1 class="page-header__title">Идём<span>в</span>кино</h1>
    <span class="page-header__subtitle">Администраторская</span>
  </header>
  
  <main class="conf-steps">
    <section class="conf-step" data-step="scheme">
      <header class="conf-step__header conf-step__header_opened">
        <h2 class="conf-step__title">Управление залами</h2>
      </header>
      <div class="conf-step__wrapper">
        <p class="conf-step__paragraph">Доступные залы:</p>
        <ul class="conf-step__list">
          @if(isset($halls) && count($halls))
            @foreach($halls as $hall)
              <li data-hall-id="{{ $hall->id }}">
                {{ $hall->name }}
                <form action="{{ route('admin.halls.destroy', $hall->id) }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button
                    type="button"
                    class="conf-step__button conf-step__button-trash"
                    data-hall-name="{{ $hall->name }}"
                    data-hall-id="{{ $hall->id }}">
                  </button>
                </form>
              </li>
            @endforeach
          @else
            <li id="no-halls-message">Нет данных о залах. Создайте новый</li>
          @endif
        </ul>
        <button id="create-hall-btn" class="conf-step__button conf-step__button-accent">Создать зал</button>
      </div>
    </section>

    
    <section class="conf-step">
      <header class="conf-step__header conf-step__header_closed">
        <h2 class="conf-step__title">Конфигурация залов</h2>
      </header>
      <div class="conf-step__wrapper">
        <p class="conf-step__paragraph">Выберите зал для конфигурации:</p>
        <ul class="conf-step__selectors-box" data-type="chairs">
          @if(isset($halls) && count($halls))
            @foreach($halls as $hall)
              <li>
                <input type="radio"
                  class="conf-step__radio"
                  name="chairs-hall"
                  value="{{ $hall->id }}"
                  @if($loop->first) checked @endif>
                <span class="conf-step__selector">{{ $hall->name }}</span>
              </li>
            @endforeach
          @else
            <li class="no-halls-message">Нет залов для настройки.</li>
          @endif
        </ul>
        <div class="hall-scheme-block"
            @if(!isset($halls) || !count($halls)) style="display:none;" @endif>
          <p class="conf-step__paragraph">Укажите количество рядов и максимальное количество кресел в ряду:</p>
          <div class="conf-step__legend">
            <label class="conf-step__label">
              Рядов, шт
              <input type="number" id="hall-rows-input" class="conf-step__input" placeholder="10" min="1">
            </label>
            <span class="multiplier">x</span>
            <label class="conf-step__label">
              Мест, шт
              <input type="number" id="hall-seats-input" class="conf-step__input" placeholder="8" min="1">
            </label>
          </div>

          <p class="conf-step__paragraph">Теперь вы можете указать типы кресел на схеме зала:</p>
          <div class="conf-step__legend">
            <span class="conf-step__chair conf-step__chair_standart"></span> — обычные кресла
            <span class="conf-step__chair conf-step__chair_vip"></span> — VIP кресла
            <span class="conf-step__chair conf-step__chair_disabled"></span> — заблокированные (нет кресла)
            <p class="conf-step__hint">Чтобы изменить вид кресла, нажмите по нему левой кнопкой мыши</p>
          </div>

          <div class="conf-step__hall">
            <div class="conf-step__hall-wrapper" id="hall-scheme-wrapper"></div>
          </div>

          <fieldset class="conf-step__buttons text-center">
            <button id="cancel-scheme-btn" type="button" class="conf-step__button conf-step__button-regular">Отмена</button>
            <button id="save-scheme-btn" type="button" class="conf-step__button conf-step__button-accent">Сохранить</button>
          </fieldset>
        </div>       
      </div>
    </section>
    
    <section class="conf-step">
      <header class="conf-step__header conf-step__header_closed">
        <h2 class="conf-step__title">Конфигурация цен</h2>
      </header>

      <div class="conf-step__wrapper" data-section="prices">
        <p class="conf-step__paragraph">Выберите зал для конфигурации:</p>

        <ul class="conf-step__selectors-box" data-type="prices">
          @if(isset($halls) && count($halls))
            @foreach($halls as $hall)
              <li>
                <input type="radio"
                      class="conf-step__radio"
                      name="prices-hall"
                      value="{{ $hall->id }}"
                      @if($loop->first) checked @endif>
                <span class="conf-step__selector">{{ $hall->name }}</span>
              </li>
            @endforeach
          @else
            <li class="no-halls-message">Нет залов для настройки.</li>
          @endif
        </ul>
        
        <div class="prices-form-block"
            @if(!isset($halls) || !count($halls)) style="display:none;" @endif>
          <p class="conf-step__paragraph">Установите цены для типов кресел:</p>

          @if(isset($halls) && count($halls))
            @php $firstHall = $halls->first(); @endphp
            <form method="POST"
                  action="{{ route('admin.prices.update', $firstHall->id) }}"
                  class="hall-form"
                  data-hall-id="{{ $firstHall->id }}">
              @csrf

              <div class="conf-step__legend">
                <label class="conf-step__label">
                  Цена, рублей
                  <input type="number"
                        class="conf-step__input"
                        name="prices[standart]"
                        placeholder="0"
                        value="{{ $prices[$firstHall->id]['standart'] ?? '' }}"
                        data-base="{{ $prices[$firstHall->id]['standart'] ?? '' }}">
                </label>
                за <span class="conf-step__chair conf-step__chair_standart"></span> обычные кресла
              </div>

              <div class="conf-step__legend">
                <label class="conf-step__label">
                  Цена, рублей
                  <input type="number"
                        class="conf-step__input"
                        name="prices[vip]"
                        placeholder="0"
                        value="{{ $prices[$firstHall->id]['vip'] ?? '' }}"
                        data-base="{{ $prices[$firstHall->id]['vip'] ?? '' }}">
                </label>
                за <span class="conf-step__chair conf-step__chair_vip"></span> VIP кресла
              </div>

              <fieldset class="conf-step__buttons text-center">
                <button type="reset" class="conf-step__button conf-step__button-regular">Отмена</button>
                <input type="submit" value="Сохранить" class="conf-step__button conf-step__button-accent">
              </fieldset>
            </form>
          @else
            <form method="POST"
                  action="#"
                  class="hall-form"
                  data-hall-id=""
                  style="display:none;">
              @csrf

              <div class="conf-step__legend">
                <label class="conf-step__label">
                  Цена, рублей
                  <input type="number"
                        class="conf-step__input"
                        name="prices[standart]"
                        placeholder="0">
                </label>
                за <span class="conf-step__chair conf-step__chair_standart"></span> обычные кресла
              </div>

              <div class="conf-step__legend">
                <label class="conf-step__label">
                  Цена, рублей
                  <input type="number"
                        class="conf-step__input"
                        name="prices[vip]"
                        placeholder="0">
                </label>
                за <span class="conf-step__chair conf-step__chair_vip"></span> VIP кресла
              </div>

              <fieldset class="conf-step__buttons text-center">
                <button type="reset" class="conf-step__button conf-step__button-regular">Отмена</button>
                <input type="submit" value="Сохранить" class="conf-step__button conf-step__button-accent">
              </fieldset>
            </form>
          @endif
        </div>
      </div>
    </section>


    
    <section class="conf-step">
      <header class="conf-step__header conf-step__header_closed">
        <h2 class="conf-step__title">Сетка сеансов</h2>
      </header>
      <div class="conf-step__wrapper">
        <p class="conf-step__paragraph">
          <button id="create-movie-btn" class="conf-step__button conf-step__button-accent">Добавить фильм</button>
        </p>
        <div class="conf-step__movies">
          <p id="movies-empty-message" @if ($movies->count()) style="display:none;" @endif>Нет доступных фильмов.</p>
          @if ($movies->count())
            @foreach ($movies as $movie)
              <div class="conf-step__movie" data-id="{{ $movie->id }}">
                <img class="conf-step__movie-poster" alt="poster" src="{{ asset('assets/admin/i/poster.png') }}">
                <h3 class="conf-step__movie-title">{{ $movie->title }}</h3>
                <p class="conf-step__movie-duration">{{ $movie->duration }} минут</p>
              </div>
            @endforeach
          @endif        
        </div>
        
        <div class="conf-step__seances">
          @foreach($halls as $hall)
            <div class="conf-step__seances-hall" data-hall="{{ $hall->id }}">
              <h3 class="conf-step__seances-title">{{ $hall->name }}</h3>
              <div class="conf-step__seances-timeline">
            </div>
          @endforeach
        </div>
        
        <fieldset class="conf-step__buttons text-center">
          <button class="conf-step__button conf-step__button-regular">Отмена</button>
          <input type="submit" value="Сохранить" class="conf-step__button conf-step__button-accent">
        </fieldset>  
      </div>
    </section>
    
    <section class="conf-step">
      <header class="conf-step__header conf-step__header_closed">
        <h2 class="conf-step__title">Открыть продажи</h2>
      </header>
      <div class="conf-step__wrapper text-center">
        <p class="conf-step__paragraph">Всё готово, теперь можно:</p>
        <button class="conf-step__button conf-step__button-accent">Открыть продажу билетов</button>
      </div>
    </section>    
  </main>
@endsection

@section('modals')
  @include('admin.popup_add-hall')
  @include('admin.delete_hall_popup')
  @include('admin.popup_add-film')
  @include('admin.popup_edit-film')
  @include('admin.popup_delete-film')
@endsection

@push('scripts')
  <script> window.hallSchemeFromServer = @json($hallSchemes ?? [])</script>
  <script> window.pricesFromServer = @json($prices ?? [])</script>
  <script src="{{ asset('assets/admin/js/accordeon.js') }}"></script>
  <script src="{{ asset('assets/admin/js/popup-hall.js') }}"></script>
  <script src="{{ asset('assets/admin/js/popup-film.js') }}"></script>
  <script src="{{ asset('assets/admin/js/popup-film-editor.js') }}"></script>
  <script src="{{ asset('assets/admin/js/popup-film-remove.js') }}"></script>
  <script src="{{ asset('assets/admin/js/hall-seats-setup.js') }}"></script>
@endpush