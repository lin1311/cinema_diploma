document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('add-movie-popup');
    const form = popup ? popup.querySelector('form') : null;
    const posterUrlInput = document.getElementById('add-movie-poster-url');
    const posterOriginalNameInput = document.getElementById('add-movie-poster-original-name');
    const posterNameLabel = document.getElementById('add-movie-poster-file-name');

    const updateMoviesEmptyState = () => {
        const moviesList = document.querySelector('.conf-step__movies');
        const emptyMessage = document.getElementById('movies-empty-message');
        if (!moviesList || !emptyMessage) {
            return;
        }
        const hasMovies = moviesList.querySelectorAll('.conf-step__movie').length > 0;
        emptyMessage.style.display = hasMovies ? 'none' : '';
    };

    window.updateMoviesEmptyState = updateMoviesEmptyState;
    updateMoviesEmptyState();

    const clearPosterSelection = () => {
        if (posterUrlInput) {
            posterUrlInput.value = '';
        }
        if (posterOriginalNameInput) {
            posterOriginalNameInput.value = '';
        }
        if (posterNameLabel) {
            posterNameLabel.textContent = '';
            posterNameLabel.hidden = true;
        }
    };

    const resetAddMovieForm = () => {
        form?.reset();
        clearPosterSelection();
    };

    window.updateAddMoviePosterSelection = (posterUrl, originalName) => {
        if (posterUrlInput) {
            posterUrlInput.value = posterUrl || '';
        }
        if (posterOriginalNameInput) {
            posterOriginalNameInput.value = originalName || '';
        }
        if (posterNameLabel) {
            const source = (originalName || '').trim();
            const trimmed = source.length > 30 ? `${source.slice(0, 30)}...` : source;
            posterNameLabel.textContent = trimmed;
            posterNameLabel.hidden = !trimmed;
        }
    };

    window.clearAddMoviePosterSelection = clearPosterSelection;

    // ===== Открытие попапа =====
    const openBtn = document.getElementById('create-movie-btn');
    if (openBtn && popup) {
        openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            resetAddMovieForm();
            popup.classList.add('active');
        });

        // Крестик
        const closeBtn = popup.querySelector('.popup__dismiss');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                resetAddMovieForm();
                popup.classList.remove('active');
            });
        }

        // Кнопка "отменить"
        const cancelBtn = popup.querySelector('.conf-step__button-regular[type="button"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                resetAddMovieForm();
                popup.classList.remove('active');
            });
        }
    }

    // ===== AJAX отправка формы =====
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!form.reportValidity()) {
                return;
            }

            const selectedPosterUrl = (posterUrlInput?.value || '').trim();
            if (!selectedPosterUrl) {
                alert('Загрузите постер перед добавлением фильма.');
                return;
            }

            const url = form.action;
            const formData = new FormData(form);
            const title = (formData.get('title') || '').toString().trim().toLowerCase();
            const duration = (formData.get('duration') || '').toString().trim();

            const moviesList = document.querySelector('.conf-step__movies');
            if (moviesList) {
                const existingMovies = Array.from(moviesList.querySelectorAll('.conf-step__movie'));
                const hasDuplicate = existingMovies.some((movie) => {
                    const movieTitle = movie.querySelector('.conf-step__movie-title')?.textContent?.trim().toLowerCase();
                    const movieDurationText = movie.querySelector('.conf-step__movie-duration')?.textContent?.trim();
                    const movieDuration = movieDurationText ? movieDurationText.split(' ')[0] : '';
                    return movieTitle === title && movieDuration === duration;
                });

                if (hasDuplicate) {
                    alert('Такой фильм уже есть в списке.');
                    return;
                }
            }

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(async (response) => {
                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: `HTTP ${response.status}` };

                if (!response.ok) {
                    let errorMessage = payload.message || `Ошибка HTTP ${response.status}`;
                    if (payload.errors && typeof payload.errors === 'object') {
                        const firstFieldErrors = Object.values(payload.errors)[0];
                        if (Array.isArray(firstFieldErrors) && firstFieldErrors[0]) {
                            errorMessage = firstFieldErrors[0];
                        }
                    }

                    return { success: false, message: errorMessage };
                }

                return payload;
            })
            .then(data => {
                if (data.success && data.movie) {
                    resetAddMovieForm();
                    popup.classList.remove('active');
                    // Динамически добавить новый фильм на страницу!
                    
                    if (moviesList) {
                        moviesList.insertAdjacentHTML('beforeend',
                        `<div class="conf-step__movie" data-id="${data.movie.id}">
                            <img class="conf-step__movie-poster" alt="poster" src="${data.movie.poster_url || '/assets/admin/i/poster.png'}">
                            <h3 class="conf-step__movie-title">${data.movie.title}</h3>
                            <p class="conf-step__movie-duration">${data.movie.duration} минут</p>
                        </div>`);
                        updateMoviesEmptyState();
                    }
                } else {
                    alert(data.message || 'Ошибка при добавлении фильма');
                }
            })
            .catch(() => alert('Ошибка соединения'));
        });
    }

    if (popup) {
        popup.addEventListener('click', function (event) {
            if (event.target === popup) {
                resetAddMovieForm();
                popup.classList.remove('active');
            }
        });
    }
});
