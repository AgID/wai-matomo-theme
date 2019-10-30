$(() => {
    if (piwik.idSite) {
        const ajaxHelper = require('ajaxHelper');
        const ajax = new ajaxHelper();
        ajax.setUrl('index.php?module=API&method=SitesManager.getSiteFromId&idSite=' + piwik.idSite + '&format=JSON');
        ajax.setCallback(function (response) {
            if (response.length > 0) {
                $('#pa-name').text(response[0].group);
            }
        });
        ajax.setFormat('json');
        ajax.send();
    }
})
