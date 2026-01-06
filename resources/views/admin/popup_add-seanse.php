<div class="popup active">
<div class="popup__container">
  <div class="popup__content">
    <div class="popup__header">
      <h2 class="popup__title">
        Добавление сеанса
        <a class="popup__dismiss" href="#"><img src="i/close.png" alt="Закрыть"></a>
      </h2>
    </div>
    <div class="popup__wrapper">
      <form action="add_movie" method="post" accept-charset="utf-8">
        <label class="conf-step__label conf-step__label-fullsize" for="hall">
          Название зала
          <select class="conf-step__input" name="hall" required>
          </select>
        </label>
        <label class="conf-step__label conf-step__label-fullsize" for="hall">
          Название фильма
          <select class="conf-step__input" name="film" required>
          </select>
        </label>
        <label class="conf-step__label conf-step__label-fullsize" for="name">
          Время начала
          <input class="conf-step__input" type="time" value="00:00" name="start_time" required>
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