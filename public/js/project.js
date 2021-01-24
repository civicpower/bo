;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_links_iframe();
    });
})(jQuery, window, document);

function bind_links_iframe() {
    $("a[data-target][target]").on("click", function () {
        var $this = $(this);
        var href = $this.attr("href");
        $("iframe[name='iframe-modal']").attr("src", href);
    });
}

function cp_alert(text) {
    var $modal = $("#modal-alert");
    $modal.find("#p-text").html(text);
    $modal.modal("show");
}

function project_blink($elm, blink_class, nb, func_final) {
    $elm.addClass(blink_class);
    setTimeout(function () {
        $elm.removeClass(blink_class);
        if (nb > 0) {
            setTimeout(function () {
                project_blink($elm, blink_class, nb - 1, func_final)
            }, 100);
        } else {
            if (func_final !== null && func_final !== undefined) {
                setTimeout(func_final, 10)
            }
        }
    }, 100);
}

function civicpower_mainfunc(func) {
    func.apply(null, Array.prototype.slice.call(arguments, 1));
}
function civicpower_cookie_exists(name) {
    var kooky = civicpower_get_cookie(name);
    return typeof kooky != "undefined" && kooky !== null;
}

function civicpower_set_cookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/;domain=civicpower.io";
}

function civicpower_get_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function civicpower_erase_cookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;domain=civicpower.io';
}

function civicpower_user_token() {
    return civicpower_get_cookie(user_cookie_name());
}
function user_cookie_name() {
    return "usertoken";
}
