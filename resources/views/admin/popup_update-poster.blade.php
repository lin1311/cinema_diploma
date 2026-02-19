<div class="popup" id="update-movie-poster-popup" style="display: none;">
  <div class="popup__container">
    <div class="popup__content">
      <div class="popup__header">
        <h2 class="popup__title">
          <span id="movie-poster-popup-title">Загрузка постера</span>
          <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
        </h2>
      </div>
      <div class="popup__wrapper">
        <form id="update-movie-poster-form" action="" method="POST" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="id" id="poster-movie-id">
          <label class="conf-step__label conf-step__label-fullsize">
            Выберите изображение
            <input class="conf-step__input" type="file" name="poster" accept="image/*" required>
          </label>
          <div class="conf-step__buttons text-center">
            <input type="submit" id="movie-poster-submit-btn" class="conf-step__button conf-step__button-accent" value="Загрузить постер">
            <button type="button" class="conf-step__button conf-step__button-regular">Отменить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
