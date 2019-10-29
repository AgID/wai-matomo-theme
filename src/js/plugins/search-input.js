$(() => {
    let lastElement = $('.top_controls span.icon.icon-arrowup');
    let searchElement = $('.top_controls div[piwik-quick-access=""]');
    if (lastElement.length) {
        searchElement.insertBefore(lastElement);
        return;
    }
    $('.top_controls').append(searchElement);
});
