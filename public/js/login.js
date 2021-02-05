;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_submit();
    });
    function bind_submit(){
        $("#form_login").on("submit",function(e){
            var username = String($("#input_login").val());
            var password = String($("#input_password").val());
            if(username.length<=5){
                local_error("Adresse email ou N° de mobile incorrect");
                e.preventDefault();
                return false;
            }else if((username.match(/ /g) || []).length > 0){
                local_error("L'Adresse email ou le N° de mobile ne peuvent pas contenir d'espace");
                e.preventDefault();
                return false;
            }else{
                $("#input_password").val(sha1(password));
                return true;
            }
        });
    }
    function local_error(text){
        $("#div-error").text(text);
        $("#div-error").slideDown("fast");
        setTimeout(function(){
            $("#div-error").slideUp("fast");
        },2500);
    }
})(jQuery, window, document);
