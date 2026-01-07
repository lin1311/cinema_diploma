document.addEventListener('DOMContentLoaded', function () {
    const editPopup = document.getElementById('edit-movie-popup');
    const editForm = document.getElementById('edit-movie-form');
    const closeBtn = editPopup.querySelector('.popup__dismiss');

    // Открытие редактора по клику на карточку фильма
    document.body.addEventListener('click', function(e) {
        const card = e.target.closest('.conf-step__movie');
        if (card && card.dataset.id) {
            fetch(`/admin/movies/${card.dataset.id}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(resp => resp.json())
                .then(data => {
                    if (!data.movie) return;
                    editForm.reset();
                    editForm.action = `/admin/movies/${data.movie.id}`;
                    editForm.querySelector('[name="id"]').value = data.movie.id;
                    editForm.querySelector('[name="title"]').value = data.movie.title || '';
                    editForm.querySelector('[name="duration"]').value = data.movie.duration || '';
                    editForm.querySelector('[name="description"]').value = data.movie.description || '';
                    editForm.querySelector('[name="country"]').value = data.movie.country || '';
                    editPopup.style.display = '';
                    editPopup.classList.add('active');
                });
        }
    });

    // Сохранение изменений в фильме через AJAX
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const movieId = editForm.querySelector('[name="id"]').value;
        const formData = new FormData(editForm);

        fetch(`/admin/movies/${movieId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': editForm.querySelector('input[name="_token"]').value
            },
            body: new URLSearchParams([...formData, ['_method', 'PUT']])
        })
        .then(resp => resp.json())
        .then(data => {
            if (data.success && data.movie) {
                const card = document.querySelector(`.conf-step__movie[data-id="${data.movie.id}"]`);
                if (card) {
                    card.querySelector('.conf-step__movie-title').textContent = data.movie.title;
                    card.querySelector('.conf-step__movie-duration').textContent = data.movie.duration + ' минут';
                }
                document
                    .querySelectorAll(`.conf-step__seances-movie[data-movie-id="${data.movie.id}"] .conf-step__seances-movie-title`)
                    .forEach((titleNode) => {
                        titleNode.textContent = data.movie.title;
                    });
                editPopup.classList.remove('active');
                editPopup.style.display = 'none';
            } else {
                alert('Ошибка сохранения');
            }
        });
    });

    // Закрытие редактора по крестику
    closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        editPopup.classList.remove('active');
        editPopup.style.display = 'none';
    });
});
