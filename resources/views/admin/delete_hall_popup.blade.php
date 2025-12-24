<div class="popup" id="delete-hall-popup" style="display:none">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          Удаление зала
          <a class="popup__dismiss" href="#" id="close-delete-popup"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form id="delete-hall-form" method="POST">
          @csrf
          <p class="conf-step__paragraph">Вы действительно хотите удалить зал <span id="delete-hall-name"></span>?</p>
          <div class="conf-step__buttons text-center">
            <input type="submit" value="Удалить" class="conf-step__button conf-step__button-accent">
            <button class="conf-step__button conf-step__button-regular" type="button" id="cancel-delete">Отменить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
