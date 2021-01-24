;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_get_data();
    });

    function bind_get_data() {
        invoke_get_data();
        setInterval(function () {
            invoke_get_data();
        }, 30000);
    }

    function invoke_get_data() {
        $.ajax({
            type: "post",
            url: "/api",
            dataType: "json",
            data: {
                action: "dashboard"
            },
            success: function (jsone) {
                $("#nb_ballot").text(jsone.data.nb_ballot);
                $("#nb_vote").text(jsone.data.nb_vote);
                if(typeof jsone.data.nb_users != "undefined") {
                    $("#nb_users").text(jsone.data.nb_users);
                }
            }
        });
    }
})(jQuery, window, document);
