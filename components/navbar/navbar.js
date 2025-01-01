document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.navbar__toggle');
    const navbarLinks = document.querySelector('.navbar__links');

    toggleButton.addEventListener('click', function () {
        navbarLinks.classList.toggle('show');
        toggleButton.classList.toggle('active');
    });
});