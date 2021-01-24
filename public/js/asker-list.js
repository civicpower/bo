;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_delete();
    });


    function bind_delete() {
        $("button.btn-remove").on("click", function () {
            if (!confirm("Êtes-vous sûr(e) de vouloir supprimer ce profil organisateur ?\nL'Opération est irréversible !")) {
                return;
            }
            var $this = $(this);
            var asker_id = $(this).attr("data-asker_id");
            if (asker_id == undefined || asker_id == null) {
                return;
            }
            $.ajax({
                type: "post",
                url: "/asker-list",
                dataType: "json",
                data: {
                    action: "delete_asker",
                    asker_id: asker_id
                },
                success: function (data) {
                    if (data.status == "success") {
                        var $tr = $this.closest("tr");
                        project_blink($tr, "bg-danger", 2, function () {
                            $tr.fadeOut("slow", function () {
                                $(this).remove();
                            });
                        });
                    } else {
                        alert(data.message.join("\n"));
                    }
                }
            });
        });
    }

})(jQuery, window, document);

