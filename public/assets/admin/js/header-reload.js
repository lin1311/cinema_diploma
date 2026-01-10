document.addEventListener('DOMContentLoaded', () => {
    const headerTitle = document.querySelector('.page-header__title');
    if (!headerTitle) {
        return;
    }
    headerTitle.addEventListener('click', () => {
        window.location.reload();
    });
});
