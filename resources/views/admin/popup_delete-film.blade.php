<div class="popup" id="delete-movie-popup" style="display: none;">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          Удаление фильма
          <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form id="delete-movie-form" action="" method="post" accept-charset="utf-8">
          @csrf
          @method('DELETE')
          <p class="conf-step__paragraph">
            Вы действительно хотите удалить фильм
            <span id="delete-movie-title"></span>?
          </p>
          <div class="conf-step__buttons text-center">
            <input type="submit" value="Удалить" class="conf-step__button conf-step__button-accent">
            <button class="conf-step__button conf-step__button-regular" type="button" id="delete-movie-cancel">Отменить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
