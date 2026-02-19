document.addEventListener('DOMContentLoaded', function () {
    const editPopup = document.getElementById('edit-movie-popup');
    const editForm = document.getElementById('edit-movie-form');
    const closeBtn = editPopup?.querySelector('.popup__dismiss');
    const uploadPosterLink = document.getElementById('edit-movie-upload-poster-link');
    const posterUrlInput = document.getElementById('edit-movie-poster-url');
    const posterOriginalNameInput = document.getElementById('edit-movie-poster-original-name');
    const posterNameLabel = document.getElementById('edit-movie-poster-file-name');

    const trimFileName = (fileName) => {
        const source = (fileName || '').trim();
        if (!source) {
            return '';
        }
        return source.length > 30 ? `${source.slice(0, 30)}...` : source;
    };

    const extractNameFromPosterUrl = (posterUrl) => {
        if (!posterUrl) {
            return '';
        }

        const path = (posterUrl.split('?')[0] || '').trim();
        const chunks = path.split('/');
        return decodeURIComponent(chunks[chunks.length - 1] || '');
    };

    const setEditPosterSelection = (posterUrl, fileName) => {
        if (posterUrlInput) {
            posterUrlInput.value = posterUrl || '';
        }
        if (posterOriginalNameInput) {
            posterOriginalNameInput.value = fileName || '';
        }
        if (posterNameLabel) {
            const trimmed = trimFileName(fileName || '');
            posterNameLabel.textContent = trimmed;
            posterNameLabel.hidden = !trimmed;
        }
    };

    const openMovieEditor = (movieId) => {
        if (!movieId || !editPopup || !editForm) {
            return;
        }

        fetch(`/admin/movies/${movieId}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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
                setEditPosterSelection(data.movie.poster_url || '', extractNameFromPosterUrl(data.movie.poster_url || ''));
                editPopup.style.display = '';
                editPopup.classList.add('active');
            });
    };

    // Открытие редактора по клику на карточку фильма
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.conf-step__movie-poster')) {
            return;
        }

        const card = e.target.closest('.conf-step__movie');
        openMovieEditor(card?.dataset.id);
    });

    uploadPosterLink?.addEventListener('click', function (e) {
        e.preventDefault();
        const movieId = editForm?.querySelector('[name="id"]')?.value;
        if (!movieId) {
            return;
        }

        editPopup.classList.remove('active');
        editPopup.style.display = 'none';
        window.openMoviePosterPopup?.({
            mode: 'edit',
            movieId,
            returnPopupId: 'edit-movie-popup',
        });
    });

    document.addEventListener('moviePosterUploaded', function (event) {
        const detail = event.detail || {};
        if (detail.mode !== 'edit' || !detail.movie) {
            return;
        }

        const currentMovieId = editForm?.querySelector('[name="id"]')?.value;
        if (!currentMovieId || String(currentMovieId) !== String(detail.movie.id)) {
            return;
        }

        setEditPosterSelection(detail.movie.poster_url || '', detail.originalName || extractNameFromPosterUrl(detail.movie.poster_url || ''));
    });

    // Сохранение изменений в фильме через AJAX
    editForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const movieId = editForm.querySelector('[name="id"]').value;
        const formData = new FormData(editForm);

        fetch(`/admin/movies/${movieId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': editForm.querySelector('input[name="_token"]').value
            },
            body: new URLSearchParams([...formData, ['_method', 'PUT']])
        })
        .then(async (resp) => {
            const data = await resp.json();
            if (!resp.ok) {
                throw data || new Error('save_failed');
            }
            return data;
        })
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
                alert(data.message || 'Ошибка сохранения');
            }
        })
        .catch((error) => {
            const message = error?.message
                || (error?.errors && Object.values(error.errors)[0]?.[0])
                || 'Ошибка сохранения';
            alert(message);
        });
    });

    // Закрытие редактора по крестику
    closeBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        editPopup.classList.remove('active');
        editPopup.style.display = 'none';
    });
});
