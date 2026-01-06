document.addEventListener('DOMContentLoaded', function () {
    // Элементы редактора
    const editPopup = document.getElementById('edit-movie-popup');
    const editForm = document.getElementById('edit-movie-form');
    const closeBtn = editPopup.querySelector('.popup__dismiss');
    const deleteBtn = document.getElementById('delete-movie-btn');

    // Элементы попапа удаления
    const deletePopup = document.getElementById('delete-movie-popup');
    const deleteForm = document.getElementById('delete-movie-form');
    const deleteTitleSpan = document.getElementById('delete-movie-title');
    const deleteCancelBtn = document.getElementById('delete-movie-cancel');
    const deleteCloseBtn = deletePopup.querySelector('.popup__dismiss');

    // Показать попап удаления, подставить название фильма
    deleteBtn.addEventListener('click', function (e) {
        e.preventDefault();
        // Получаем данные из редактора
        const movieTitle = editForm.querySelector('[name="title"]').value;
        const movieId = editForm.querySelector('[name="id"]').value;
        deleteTitleSpan.textContent = `"${movieTitle}"`;
        deleteForm.dataset.movieId = movieId;
        deletePopup.style.display = '';
        deletePopup.classList.add('active');

        // Закрыть редактор
        editPopup.classList.remove('active');
        editPopup.style.display = 'none';
    });

    // Крестик закрытия в редакторе
    closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        editPopup.classList.remove('active');
        editPopup.style.display = 'none';
    });

    // Кнопки "Отменить/Крестик" - закрытие попапа удаления
    deleteCancelBtn.addEventListener('click', function(e) {
        e.preventDefault();
        deletePopup.classList.remove('active');
        deletePopup.style.display = 'none';
    });
    deleteCloseBtn.addEventListener('click', function(e) {
        e.preventDefault();
        deletePopup.classList.remove('active');
        deletePopup.style.display = 'none';
    });

    // Сабмит на удаление ("Удалить") через AJAX
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const movieId = deleteForm.dataset.movieId;
        const csrf = deleteForm.querySelector('input[name="_token"]').value;

        fetch(`/admin/movies/${movieId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            }
        })
        .then(resp => resp.json())
        .then(data => {
            if (data.success) {
                // Удалить карточку фильма из DOM
                const card = document.querySelector(`.conf-step__movie[data-id="${movieId}"]`);
                if (card) card.remove();
                deletePopup.classList.remove('active');
                deletePopup.style.display = 'none';
                if (window.updateMoviesEmptyState) {
                    window.updateMoviesEmptyState();
                }
            } else {
                alert('Ошибка удаления');
            }
        });
    });
});
