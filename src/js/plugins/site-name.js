window.showSiteName = function(siteName) {
    $(".activateTopMenu").filter(':last').before('<span id="site-name" class="">' + siteName + '</span>');
}
