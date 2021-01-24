;(function ($, window, document, undefined) {
    $(document).ready(function () {
        form_profile_submit();
        bind_photo();
    });

    function form_profile_submit() {
        $("form#form_profile").on("submit", function (e) {
            var $this = $(this);
            var data = $this.serialize();
            $.ajax({
                type: $this.attr("method"),
                url: $this.attr("action"),
                dataType: "json",
                data: data,
                success: function (data) {
                    if (data.status == "success") {
                        cp_alert("Profil organisateur enregistré avec succès");
                        if(typeof data.data == "number"){
                            setTimeout(function(){
                                window.location.replace("/asker?asker_id="+String(parseInt(data.data)));
                            },1000);
                        }
                    } else {
                        $("#modal_account_error_list").empty();
                        for (var i in data.message) {
                            $("#modal_account_error_list").append("<li>" + String(data.message[i]) + "</li>");
                        }
                        $("#modal_account_error").modal();
                    }
                }
            });
            e.preventDefault();
            return false;
        });
    }

    function bind_photo() {
        $("#pp-file").on("change", function () {
            var $input = $(this);
            var file_data = $input.prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('action', 'change_photo');
            form_data.append('asker_id', $("#asker_id").val());
            $.ajax({
                url: '/asker.php', // point to server-side PHP script
                dataType: 'json',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (jsone) {
                    if (typeof jsone != 'undefined' && typeof jsone.status != 'undefined' && jsone.status == "success") {
                        if (typeof jsone.data != "undefined") {
                            $(".profile-user-img").attr("src", String(jsone.data)+"?rand="+String(Math.random()*9999)+"_"+String(Math.random()*9999));
                            cp_alert("Votre photo de profil a été modifiée avec succès !");
                        }
                    } else {
                        cp_alert("Une erreur est survenue !");
                    }
                },
                error: function (res) {
                    if (typeof res.responseJSON !== 'undefined') {
                        var jsone = res.responseJSON;
                        if (typeof jsone.message !== 'undefined' && jsone.message !== null && jsone.message.length > 0) {
                            cp_alert(jsone.message);
                        } else {
                            cp_alert("Une erreur est survenue !");
                        }
                    } else {
                        cp_alert("Une erreur est survenue !");
                    }
                }
            });
            $input.val("");
            $input.prop("value", "");
        });
    }
})(jQuery, window, document);

