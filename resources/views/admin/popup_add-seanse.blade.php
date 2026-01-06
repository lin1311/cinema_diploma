<div class="popup" id="add-seance-popup">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          Добавление сеанса
          <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form action="#" method="post" accept-charset="utf-8">
          <label class="conf-step__label conf-step__label-fullsize" for="seance-hall">
            Название зала
            <select class="conf-step__input" name="hall" id="seance-hall" required>
              @foreach($halls as $hall)
                <option value="{{ $hall->id }}">{{ $hall->name }}</option>
              @endforeach
            </select>
          </label>
          <label class="conf-step__label conf-step__label-fullsize" for="seance-film">
            Название фильма
            <select class="conf-step__input" name="film" id="seance-film" required>
              @foreach($movies as $movie)
                <option value="{{ $movie->id }}">{{ $movie->title }}</option>
              @endforeach
            </select>
          </label>
          <label class="conf-step__label conf-step__label-fullsize" for="seance-start-time">
            Время начала
            <input class="conf-step__input" type="time" value="00:00" name="start_time" id="seance-start-time" required>
          </label>

          <div class="conf-step__buttons text-center">
            <input type="submit" value="Добавить" class="conf-step__button conf-step__button-accent" data-event="seance_add">
            <button class="conf-step__button conf-step__button-regular" type="button">Отменить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

