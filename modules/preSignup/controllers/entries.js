function initTooltips() {
    const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipList.map(function (el) {
        return new bootstrap.Tooltip(el, {
            customClass: 'my-tooltip'
        });
    });
}

// Delegated, because DataTables replaces the rows on every draw.
function initCopyLinks() {
    $('#entriesTable').on('click', '.copyLink', function () {
        const button = this;
        const link = $(button).data('link');

        const done = () => {
            $(button).html('<i class="bi bi-check2 text-success"></i>');
            window.setTimeout(() => {
                $(button).html('<i class="bi bi-clipboard"></i>');
            }, 2000);
        };

        // navigator.clipboard needs a secure context; this page may be served
        // over plain HTTP internally, so fall back to a temporary input.
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link).then(done, () => copyViaInput(link, done));
        } else {
            copyViaInput(link, done);
        }
    });
}

function copyViaInput(text, done) {
    const $tmp = $('<input>').val(text).appendTo('body');
    $tmp.trigger('select');
    document.execCommand('copy');
    $tmp.remove();
    done();
}
