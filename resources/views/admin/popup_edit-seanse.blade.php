<div class="popup" id="edit-seance-popup">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          Редактирование сеанса
          <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form action="#" method="post" accept-charset="utf-8">
          <label class="conf-step__label conf-step__label-fullsize" for="edit-seance-hall">
            Название зала
            <select class="conf-step__input" name="hall" id="edit-seance-hall" required></select>
          </label>
          <label class="conf-step__label conf-step__label-fullsize" for="edit-seance-film">
            Название фильма
            <select class="conf-step__input" name="film" id="edit-seance-film" required></select>
          </label>
          <label class="conf-step__label conf-step__label-fullsize" for="edit-seance-start-time">
            Время начала
            <input class="conf-step__input" type="time" value="00:00" name="start_time" id="edit-seance-start-time" required>
          </label>

          <div class="conf-step__buttons text-center">
            <input type="submit" value="Сохранить" class="conf-step__button conf-step__button-accent">
            <button class="conf-step__button conf-step__button-regular" type="button">Отменить</button>
            <button class="conf-step__button conf-step__button-regular" type="button" data-action="delete">Удалить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
