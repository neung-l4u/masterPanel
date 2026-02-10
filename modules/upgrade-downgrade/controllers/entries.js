function initTooltips() {
    const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.map(function (el) {
        return new bootstrap.Tooltip(el, {
            customClass: 'my-tooltip'
        });
    });
}