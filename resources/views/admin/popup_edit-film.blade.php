<div class="popup" id="edit-movie-popup" style="display: none;">
    <div class="popup__container">
        <div class="popup__content">
            <div class="popup__header">
                <h2 class="popup__title">
                    Редактировать фильм
                    <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
                </h2>
            </div>
            <div class="popup__wrapper">
                <form id="edit-movie-form" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit-movie-id">
                    <label class="conf-step__label conf-step__label-fullsize">
                        Название фильма
                        <input class="conf-step__input" type="text" name="title" id="edit-movie-title" required>
                    </label>
                    <label class="conf-step__label conf-step__label-fullsize">
                        Продолжительность фильма (мин.)
                        <input class="conf-step__input" type="number" name="duration" id="edit-movie-duration" min="1" required>
                    </label>
                    <label class="conf-step__label conf-step__label-fullsize">
                        Описание
                        <textarea class="conf-step__input" name="description" id="edit-movie-description"></textarea>
                    </label>
                    <label class="conf-step__label conf-step__label-fullsize">
                        Страна
                        <input class="conf-step__input" type="text" name="country" id="edit-movie-country">
                    </label>
                    <div class="conf-step__buttons text-center">
                        <input type="submit" class="conf-step__button conf-step__button-accent" value="Сохранить">
                        <button type="button" class="conf-step__button conf-step__button-regular" id="delete-movie-btn">
                            Удалить фильм
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
