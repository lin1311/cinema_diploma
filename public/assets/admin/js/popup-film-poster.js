document.addEventListener('DOMContentLoaded', function () {
    const addMoviePopup = document.getElementById('add-movie-popup');
    const addMoviePosterLink = document.getElementById('add-movie-upload-poster-link');

    const posterPopup = document.getElementById('update-movie-poster-popup');
    const posterForm = document.getElementById('update-movie-poster-form');
    const posterInput = posterForm?.querySelector('input[name="poster"]');
    const posterTitle = document.getElementById('movie-poster-popup-title');
    const posterSubmitBtn = document.getElementById('movie-poster-submit-btn');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || posterForm?.querySelector('input[name="_token"]')?.value;
    const closeBtn = posterPopup?.querySelector('.popup__dismiss');
    const cancelBtn = posterPopup?.querySelector('.conf-step__button-regular[type="button"]');

    const state = {
        mode: 'edit',
        movieId: null,
        returnPopupId: null,
    };

    const setPopupMode = (mode, movieId = null, returnPopupId = null) => {
        state.mode = mode;
        state.movieId = movieId;
        state.returnPopupId = returnPopupId;

        if (!posterForm) {
            return;
        }

        if (mode === 'create') {
            posterForm.action = '/admin/movies/poster-temp';
            posterForm.querySelector('[name="id"]').value = '';
            if (posterTitle) {
                posterTitle.textContent = 'Загрузка постера для нового фильма';
            }
            if (posterSubmitBtn) {
                posterSubmitBtn.value = 'Загрузить постер';
            }
            return;
        }

        posterForm.action = `/admin/movies/${movieId}/poster`;
        posterForm.querySelector('[name="id"]').value = movieId || '';
        if (posterTitle) {
            posterTitle.textContent = 'Загрузка постера';
        }
        if (posterSubmitBtn) {
            posterSubmitBtn.value = 'Загрузить постер';
        }
    };

    const showReturnPopupIfNeeded = () => {
        if (!state.returnPopupId) {
            return;
        }

        const popupToReturn = document.getElementById(state.returnPopupId);
        if (popupToReturn) {
            popupToReturn.classList.add('active');
            popupToReturn.style.display = '';
        }
    };

    const closePosterPopup = () => {
        posterForm?.reset();
        if (posterPopup) {
            posterPopup.classList.remove('active');
            posterPopup.style.display = 'none';
        }
        showReturnPopupIfNeeded();
    };

    const openPosterPopup = (mode, movieId = null, returnPopupId = null) => {
        if (!posterPopup || !posterForm) {
            return;
        }

        setPopupMode(mode, movieId, returnPopupId);
        posterForm.reset();
        posterPopup.style.display = '';
        posterPopup.classList.add('active');
    };

    window.openMoviePosterPopup = ({ mode, movieId = null, returnPopupId = null } = {}) => {
        openPosterPopup(mode, movieId, returnPopupId);
    };

    document.body.addEventListener('click', function (e) {
        const poster = e.target.closest('.conf-step__movie-poster');
        if (!poster) {
            return;
        }

        const card = poster.closest('.conf-step__movie');
        if (!card?.dataset.id) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        openPosterPopup('edit', card.dataset.id, null);
    });

    addMoviePosterLink?.addEventListener('click', function (e) {
        e.preventDefault();
        addMoviePopup?.classList.remove('active');
        openPosterPopup('create', null, 'add-movie-popup');
    });

    closeBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        closePosterPopup();
    });

    cancelBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        closePosterPopup();
    });

    posterPopup?.addEventListener('click', function (e) {
        if (e.target === posterPopup) {
            closePosterPopup();
        }
    });

    posterForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const file = posterInput?.files?.[0];
        if (!file) {
            return;
        }

        const endpoint = state.mode === 'create'
            ? '/admin/movies/poster-temp'
            : `/admin/movies/${state.movieId}/poster`;

        if (state.mode === 'edit' && !state.movieId) {
            alert('Не удалось определить фильм для обновления постера.');
            return;
        }

        const formData = new FormData();
        formData.append('poster', file);

        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: formData
        })
            .then(async (resp) => {
                const contentType = resp.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await resp.json()
                    : { success: false, message: `HTTP ${resp.status}` };

                if (!resp.ok) {
                    return {
                        success: false,
                        message: payload.message || `Ошибка HTTP ${resp.status}`,
                    };
                }

                return payload;
            })
            .then((data) => {
                if (!data.success) {
                    alert(data.message || 'Не удалось загрузить постер');
                    return;
                }

                if (state.mode === 'create') {
                    window.updateAddMoviePosterSelection?.(data.poster_url || '', data.original_name || '');
                    closePosterPopup();
                    return;
                }

                if (!data.movie) {
                    alert('Сервер не вернул обновлённые данные фильма.');
                    return;
                }

                document
                    .querySelectorAll(`.conf-step__movie[data-id="${data.movie.id}"] .conf-step__movie-poster`)
                    .forEach((img) => {
                        img.src = data.movie.poster_url || '/assets/admin/i/poster.png';
                    });

                document.dispatchEvent(new CustomEvent('moviePosterUploaded', {
                    detail: {
                        mode: 'edit',
                        movie: data.movie,
                        originalName: file.name || '',
                    },
                }));

                closePosterPopup();
            })
            .catch(() => alert('Ошибка соединения'));
    });
});
