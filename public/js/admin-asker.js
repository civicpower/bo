;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_select_ask_type();
    });

    function bind_select_ask_type() {
        $("#select-ask_type").on("change", function () {
            var $select = $(this);
            var astyp_id = $select.val();
            var asker_id = $("#asker_id").val();

            $.ajax({
                type: "POST",
                url: "/admin-asker.php",
                dataType: "json",
                data: {
                    action: "update_asker_type",
                    asker_id: asker_id,
                    astyp_id: astyp_id
                },
                success: function (jsone) {
                    project_blink($select, "bg-success", 2);
                },
                error: function (res) {
                    var jsone = res.responseJSON;
                    alert(jsone.message);
                }
            });

            project_blink($select, "bg-success", 2);
        });
    }
})(jQuery, window, document);

