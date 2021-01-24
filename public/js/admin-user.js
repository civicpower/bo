;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_form_user();
    });
    function bind_form_user(){
        $("#form-user").on("submit",function(e){
            var $form = $(this);
            var data = $form.serialize();
            data += "&action=update_user";
            console.log(data);
            $.ajax({
                action:"/admin-user.php",
                method:"post",
                data:data,
                datatype:"json",
                success:function(jsone){
                    project_blink($form.find("input:not(:disabled)"),"bg-success",2);
                },
                error:function(jsone){

                },
            });
            e.preventDefault();
            return false;
        });
    }

})(jQuery, window, document);

