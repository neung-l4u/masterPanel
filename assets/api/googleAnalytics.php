<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');

    // Auto-track clicks on elements with data-ga attribute
    // Usage: <button data-ga="click_login" data-ga-label="Sign In">
    document.addEventListener('click', function(e) {
        var el = e.target.closest('[data-ga]');
        if (el) {
            var eventName = el.getAttribute('data-ga');
            var eventLabel = el.getAttribute('data-ga-label') || el.textContent.trim().substring(0, 50);
            var eventCategory = el.getAttribute('data-ga-category') || 'button';
            gtag('event', eventName, {
                event_category: eventCategory,
                event_label: eventLabel
            });
        }
    });
</script>