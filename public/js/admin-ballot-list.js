;(function ($, window, document, undefined) {
    $(document).ready(function () {
        bind_delete();
        bind_select_shortcode();
        bind_tooltip();

    });

    function bind_tooltip() {
        $('[data-toggle="tooltip"]').tooltip();
        $(".btn-copy-shortcode").on("click", function () {
            $text = $(this).parent().find("input").select();
            document.execCommand("copy");
            var $span = $(this).parent().find(".shortcode-url-copied");
            $span.show();
            project_blink($span, "bg-warning", 3, function () {
                $span.hide();
            });
        });
    }

    function bind_select_shortcode() {
        $(".control-shortcode").on("focus", function () {
            $(this).select();
        });
    }

    function bind_delete() {
        $("button.btn-remove").on("click", function () {
            if (!confirm("Êtes-vous sûr(e) de vouloir supprimer cette consultation ?\nL'Opération est irréversible !")) {
                return;
            }
            var $this = $(this);
            var ballot_id = $(this).attr("data-ballot_id");
            if (ballot_id == undefined || ballot_id == null) {
                return;
            }
            $.ajax({
                type: "post",
                url: "/ballot-list",
                dataType: "json",
                data: {
                    action: "delete_ballot",
                    ballot_id: ballot_id
                },
                success: function (data) {
                    console.log(data);
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

