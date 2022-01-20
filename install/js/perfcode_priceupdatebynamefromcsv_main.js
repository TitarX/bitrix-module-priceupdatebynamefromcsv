'use strict';

function showMessage(url, type, text, args, elementId) {
    let ajaxResponse = BX.ajax({
        url: url,
        data: {
            mode: "message",
            type: type,
            text: text,
            args: args
        },
        method: "POST",
        dataType: "html",
        timeout: 30,
        async: false
    }).responseText;

    BX.adjust(BX(elementId), {html: ajaxResponse});
    scrollToElement(elementId);
}
