<div class="popup" id="add-movie-popup">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          Добавление фильма
          <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form action="{{ route('movies.store') }}" method="POST" accept-charset="utf-8">
          @csrf
          <label class="conf-step__label conf-step__label-fullsize">
            Название фильма
            <input class="conf-step__input" type="text" placeholder="Например, «Гражданин Кейн»" name="title" required>
          </label>
          <label class="conf-step__label conf-step__label-fullsize">
            Продолжительность фильма (мин.)
            <input class="conf-step__input" type="number" name="duration" min="1" required>
          </label>
          <label class="conf-step__label conf-step__label-fullsize">
            Описание фильма
            <textarea class="conf-step__input" name="description" required></textarea>
          </label>
          <label class="conf-step__label conf-step__label-fullsize">
            Страна
            <input class="conf-step__input" type="text" name="country" required>
          </label>
          <div class="conf-step__buttons text-center">
            <input type="submit" value="Добавить фильм" class="conf-step__button conf-step__button-accent" data-event="movie_add">
            <button class="conf-step__button conf-step__button-regular" type="button">Отменить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
