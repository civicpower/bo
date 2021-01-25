;(function ($, window, document, undefined) {
    var can_edit = false;
    $(document).ready(function () {
        bind_asker_list();
        form_ballot_submit();
        bind_sortable($(".todo-list"));
        bind_ballot();
        bind_question();
        bind_option();
        bind_publish();
        bind_edit();
        open_questions();
        bind_voters();
        bind_cities();
        bind_admin();
        bind_cb_asap();
    });

    function bind_cb_asap() {
        $(".cb_asap").on("change", function () {
            var value = $(this).val();
            var $div = $("#div_ballot_start");
            if (parseInt(value) === 0) {
                $div.slideDown("fast");
            } else {
                $div.slideUp("fast");
            }
        });
    }

    function bind_admin() {
        $("#btn-admin-validate").on("click", function () {
            var ballot_id = parseInt($("#ballot_id").val());
            $("#modal-ballot-accept").modal("show");
        });
        $("#btn-admin-accept-confirm").on("click", function () {
            var ballot_id = parseInt($("#ballot_id").val());
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "admin_validate",
                    acceptation_reason: $("#acceptation_reason").val(),
                    ballot_id: ballot_id
                },
                success: function (jsone) {
                    if (jsone.status == "success") {
                        window.location.replace("/ballot.php?ballot_id=" + encodeURIComponent(ballot_id));
                    }
                },
                error: function (res) {
                    try {
                        var jsone = res.responseJSON;
                        alert(jsone.message);
                    } catch (nerr) {
                        alert("Une erreur est survenue !");
                    }
                }
            });
        });
        $("#btn-admin-reject").on("click", function () {
            var ballot_id = parseInt($("#ballot_id").val());
            $("#modal-ballot-reject").modal("show");
        });
        $("#btn-admin-reject-confirm").on("click", function () {
            var ballot_id = parseInt($("#ballot_id").val());
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "admin_reject",
                    ballot_id: ballot_id,
                    rejection_reason: $("#rejection_reason").val()
                },
                success: function (jsone) {
                    if (jsone.status == "success") {
                        window.location.replace("/ballot.php?ballot_id=" + encodeURIComponent(ballot_id));
                    }
                },
                error: function (res) {
                    try {
                        var jsone = res.responseJSON;
                        alert(jsone.message);
                    } catch (nerr) {
                        alert("Une erreur est survenue !");
                    }
                }
            });
        });
    }

    function bind_asker_list() {
        var $ballot_asker_id = $("#ballot_asker_id");
        if ($ballot_asker_id.find("option").length == 2) {
            $ballot_asker_id.val($ballot_asker_id.find("option").last().attr("value"));
        }
    }

    function add_city(data) {
        if (typeof data == 'object') {
            var selector = ".item-city[data-bfilter_id='" + String(data.bfilter_id) + "']";
            var nb = $(selector).length;
            if (nb == 0) {
                var res = [];
                res.push('<div style="display:none;" class="col-xl-6 col-12 mt-2 item-city" data-bfilter_id="' + String(data.bfilter_id) + '">');
                if (!$("#input_zipcode").is(":disabled")) {
                    res.push('<button type="button" data-bfilter_id="' + String(data.bfilter_id) + '" class="btn btn-danger btn-delete-city"><i class="fa fa-trash"></i></button>');
                    res.push(' &nbsp; ');
                }
                res.push('<span class="btn">');
                res.push(String(data.code_postal));
                res.push(' ');
                res.push(String(data.nom_commune.toUpperCase()));
                res.push('</span>');
                res.push('</div>');
                $("#cities-list").append(res.join(''));
                $("#cities-list").find(selector).slideDown("fast");
            }
            project_blink($(selector), "bg-success", 4);
        }
    }

    function load_cities() {
        var ballot_id = parseInt($("#ballot_id").val());
        if (typeof ballot_id != "number" || isNaN(ballot_id) || ballot_id < 0) {
            return;
        }
        $.ajax({
            type: "POST",
            url: "/ballot.php",
            dataType: "json",
            data: {
                action: "load_cities",
                ballot_id: ballot_id
            },
            success: function (jsone) {
                if (jsone.status == "success") {
                    for (var i in jsone.data) {
                        add_city(jsone.data[i]);
                    }
                }
            },
            error: function (res) {
                try {
                    var jsone = res.responseJSON;
                    alert(jsone.message);
                } catch (nerr) {
                    alert("Une erreur est survenue !");
                }
            }
        });
    }

    function bind_cities() {
        load_cities();
        $(document).on("click", ".btn-delete-city", function () {
            var $btn = $(this);
            var bfilter_id = parseInt($btn.attr("data-bfilter_id"));
            if (typeof bfilter_id == "number" && bfilter_id > 0) {


                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "remove_city",
                        bfilter_id: bfilter_id
                    },
                    success: function (jsone) {
                        if (jsone.status == "success") {
                            var $span = $btn.closest("div");
                            project_blink($span, "bg-danger", 4, function () {
                                $span.slideUp("fast", function () {
                                    $(this).remove();
                                });
                            });
                        }
                    },
                    error: function (res) {
                        try {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        } catch (nerr) {
                            alert("Une erreur est survenue !");
                        }
                    }
                });


            }
        });
        $("#input_zipcode").on("keyup", function () {
            var $this = $(this);
            var zipcode = String($this.val());
            if (zipcode.length == 5) {
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "get_cities",
                        zipcode: zipcode
                    },
                    success: function (jsone) {
                        if (jsone.status == "success") {
                            var $select = $("#input_commune");
                            if (typeof jsone.data == "object") {
                                $select.find("option:not(.original)").remove();
                            }
                            for (var i in jsone.data) {
                                $select.append('<option value="' + String(jsone.data[i].city_id) + '">' + String(jsone.data[i].nom_commune) + '</option>');
                            }
                            if (jsone.data.length == 1) {
                                $select.find("option:not(.original)").first().prop("selected", true);
                            }
                        }
                    },
                    error: function (res) {
                        try {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        } catch (nerr) {
                            alert("Une erreur est survenue !");
                        }
                    }
                });
            }
        });
        $("#btn-city-add").on("click", function () {
            var $select = $("#input_commune");
            var city_id = $select.val();
            if (typeof city_id == "string" && city_id.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "add_city",
                        city_id: city_id,
                        ballot_id: $("#ballot_id").val(),
                    },
                    success: function (jsone) {
                        if (jsone.status == "success") {
                            add_city(jsone.data);
                        }
                    },
                    error: function (res) {
                        try {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        } catch (nerr) {
                            alert("Une erreur est survenue !");
                        }
                    }
                });
            } else {
                alert("Merci de choisir une commune");
            }
        });
    }

    function hide_voters_error() {
        $("#alert-voters-error").slideUp("fast");
    }

    function show_voters_error() {
        $("#alert-voters-error").slideDown("fast");
    }

    function update_voters(json_list, initial) {
        // console.log("update_voters",json_list,initial);
        if (json_list === true) {
            var ballot_id = parseInt($("#ballot_id").val());
            if (typeof ballot_id == "undefined" || isNaN(ballot_id)) {
                return;
            }
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "get_voters",
                    ballot_id: $("#ballot_id").val()
                },
                success: function (jsone) {
                    update_voters(jsone.data, true);
                },
                error: function (res) {
                    console.log(res);
                    try {
                        var jsone = res.responseJSON;
                        alert(jsone.message);
                    } catch (nerr) {
                        alert("Une erreur est survenue !");
                    }
                }
            });
        } else {
            if (json_list.unknown !== undefined && json_list.unknown.length > 0) {
                $("#txt-voters-error").val(json_list.unknown.join("\n"));
                show_voters_error();
            } else if (!initial && (json_list.voters === undefined || json_list.voters.length === 0 || (json_list.voters.email.length + json_list.voters.phone.length === 0))) {
                $("#txt-voters-error").val("Nous n'avons pas réussi à extraire de données. Merci de vérifier votre liste.");
                show_voters_error();
            } else {
                $.each(json_list.voters, function (type_lib, tab1) {
                    $.each(tab1, function (i, val) {
                        var $clone = $(".voter-item-stock .voter-item").clone();
                        var nb = $("#voters-list").find(".voter-item").filter("[data-bfilter_id='" + String(i) + "']").length;
                        if (nb > 0) {
                            return;
                        }
                        $clone.attr("data-bfilter_id", i);
                        $clone.find(".btn-remove-filter").attr("data-bfilter_id", i);
                        $clone.find(".info-box-icon").addClass(type_lib == "email" ? "bg-warning" : "bg-success");
                        $clone.find(".fas").addClass(type_lib == "email" ? "fa-envelope" : "fa-phone");
                        $clone.find(".voter-lib").text(val);
                        $clone.appendTo("#voters-list");
                    });
                });
            }
        }
    }

    function bind_voters() {
        update_voters(true);
        $("#voters-file").on("change", function () {
            hide_voters_error();
            var $input = $(this);
            var file_data = $input.prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('action', 'ballot_add_voters_file');
            form_data.append('ballot_id', $("#ballot_id").val());
            $.ajax({
                url: '/ballot.php', // point to server-side PHP script
                dataType: 'json',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (jsone) {
                    update_voters(jsone.data);
                },
                error: function (res) {
                    alert("Une erreur est survenue !");
                }
            });
            $input.val("");
            $input.prop("value", "");
        });
        $(document).on("click", ".btn-remove-filter", function () {
            var $btn = $(this);
            var $item = $btn.closest(".voter-item");
            project_blink($item, "bg-danger", 2, function () {
                $btn.closest(".voter-item").fadeOut("slow", function () {
                    var $i = $(this);
                    $.ajax({
                        type: "POST",
                        url: "/ballot.php",
                        dataType: "json",
                        data: {
                            action: "ballot_remove_voter",
                            bfilter_id: $btn.attr("data-bfilter_id")
                        },
                        success: function (jsone) {

                        },
                        error: function (res) {
                            alert("Une erreur est survenue !");
                            $i.fadeIn("fast");
                        }
                    });
                });
            });
        });
        $("#btn-voters-write-save").on("click", function () {
            hide_voters_error();
            //$("#modal-voters-write").modal("hide");
            var voters_text = $("#txt-voters-list").val().trim();
            if (voters_text.length <= 0) {
                alert("Veuillez renseigner le texte");
            } else {
                voters_text = voters_text.toLowerCase().replace(/\s+/g, " ").split(" ").join("\n");
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "ballot_add_voters",
                        ballot_id: $("#ballot_id").val(),
                        text: voters_text
                    },
                    success: function (jsone) {
                        $("#modal-voters-write").find("#txt-voters-list").val("");
                        $("#modal-voters-write").modal("hide");
                        update_voters(jsone.data);
                    },
                    error: function (res) {
                        console.log(res);
                        try {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        } catch (nerr) {
                            alert("Une erreur est survenue !");
                        }
                    }
                });
            }
        });
        $("#modal-voters-write").on("shown.bs.modal", function () {
            $(this).find(".form-control:first").focus();
        });
        $("#btn-import-file").on("click", function () {
            hide_voters_error();
        });
        $("#btn-voters-write").on("click", function () {
            hide_voters_error();
            $("#modal-voters-write").modal("show");
        });
        $("#btn-submit-user-name").on("click", function () {
            var firstname = $("#user_firstname").val().trim();
            var lastname = $("#user_lastname").val().trim();
            if (firstname.length <= 0 || lastname.length <= 0) {
                cp_alert("Veuillez renseigner votre prénom et votre nom")
            } else {
                var api_endpoint = String(location.host).replace(/^bo/, "api");
                $.ajax({
                    type: "POST",
                    url: "https://"+String(api_endpoint)+"/update_user",
                    dataType: "json",
                    data: {
                        token: civicpower_user_token(),
                        firstname: firstname,
                        lastname: lastname
                    },
                    success: function (jsone) {
                        if(jsone.status=="success") {
                            $("#div-user-name-missing").slideUp("slow");
                            $("#div-user-name-filled").slideDown("slow");
                        }else{
                            cp_alert(jsone.message);
                        }
                    },
                    error: function (res) {
                        try {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        } catch (nerr) {
                            alert("Une erreur est survenue !");
                        }
                    }
                });
            }
        });
    }

    function bind_edit() {
        $(".btn#btn-edit").on("click", function () {
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "ballot_edit",
                    ballot_id: $("#ballot_id").val()
                },
                success: function (jsone) {
                    window.location.replace("/ballot.php?ballot_id=" + encodeURIComponent($("#ballot_id").val()));
                },
                error: function (res) {
                    var jsone = res.responseJSON;
                    console.log(jsone);
                    alert(jsone.message);
                }
            });
        });
    }

    function bind_publish() {
        $('#modal-publish').on('hidden.bs.modal', function () {
            $("#modal-body-publish-loading").slideDown("fast");
            $("#modal-body-publish-ok").slideUp("fast");
            $("#modal-body-publish-quota").slideUp("fast");
        });
        $("#btn-open-publish").on("click", function () {
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "check_ballot_integrity",
                    ballot_id: $("#ballot_id").val()
                },
                success: function (jsone) {
                    $("#modal-publish").modal("show");
                    $.ajax({
                        type: "POST",
                        url: "/ballot.php",
                        dataType: "json",
                        data: {
                            action: "check_quota"
                        },
                        success: function (jsone) {
                            // return;
                            setTimeout(function () {
                                $("#modal-body-publish-loading").slideUp("fast");
                                if (jsone.data.nb_active + 1 <= jsone.data.nb_allowed) {
                                    $("#modal-body-publish-ok").slideDown("fast");
                                } else {
                                    $("#modal-body-publish-quota").slideDown("fast");
                                }
                            }, 500);
                            // $("#modal-publish").modal("hide");
                            // window.location.replace("/ballot.php?ballot_id=" + encodeURIComponent($("#ballot_id").val()));
                        },
                        error: function (res) {
                            var jsone = res.responseJSON;
                            console.log(jsone);
                            alert(jsone.message);
                        }
                    });
                },
                error: function (res) {
                    var jsone = res.responseJSON;
                    console.log(jsone);
                    alert(jsone.message);
                }
            });

        });
        $("#btn-publish").on("click", function () {
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "ballot_publish",
                    ballot_id: $("#ballot_id").val()
                },
                success: function (jsone) {
                    $("#modal-publish").modal("hide");
                    window.location.replace("/ballot.php?ballot_id=" + encodeURIComponent($("#ballot_id").val()));
                },
                error: function (res) {
                    var jsone = res.responseJSON;
                    console.log(jsone);
                    alert(jsone.message);
                }
            });
        });
    }

    function bind_sortable($elem) {
        $elem.sortable({
            placeholder: 'sort-highlight',
            handle: '.handle',
            forcePlaceholderSize: true,
            zIndex: 999999,
            update: function (event, ui) {
                manage_option_ranking($(ui.item[0]));
            }
        });
    }

    function manage_question_ranking() {
        var data = [];
        var rank = 1;
        $("#question_list").find(".card-question").each(function () {
            var $question = $(this);
            console.log($question);
            data.push({
                question_id: $question.attr("data-question_id"),
                rank: rank
            });
            rank++;
        });
        if (data.length === 0) {
            return;
        }
        console.log(data);
        $.ajax({
            type: "POST",
            url: "/ballot.php",
            dataType: "json",
            data: {
                action: "question_rank",
                data: data
            },
            success: function (jsone) {
            },
            error: function () {
                alert("Une erreur est survenue");
            }
        });
    }

    function manage_option_ranking($option) {
        var data = [];
        var rank = 1;
        $option.closest(".option-list-div").find("li.option-item").each(function () {
            var $option = $(this);
            data.push({
                option_id: $option.attr("data-option_id"),
                rank: rank
            });
            rank++;
        });
        console.log(data);
        $.ajax({
            type: "POST",
            url: "/ballot.php",
            dataType: "json",
            data: {
                action: "option_rank",
                data: data.length > 0 ? data : 0
            },
            success: function (jsone) {
            },
            error: function () {
                alert("Une erreur est survenue");
            }
        });
    }

    function open_questions() {
        $("#question_list .card-question").show();
        $("#question_list .card-question li.option-item").show();
    }

    function local_refresh_option(option_id) {
        $.ajax({
            type: "POST",
            url: "/ballot.php",
            dataType: "json",
            data: {
                action: "get_option",
                option_id: option_id
            },
            success: function (jsone) {
                console.log(jsone);
                $(".option-item[data-option_id='" + String(option_id) + "'] input.option-title").val(jsone.data);
                // console.log(jsone.data.option_id);
            },
            error: function (res) {
                local_refresh_option(option_id);
                var jsone = res.responseJSON;
                alert(jsone.message);
            }
        });
    }

    function bind_option() {
        $(document).on("change", ".card-question .option-item input.option-title", function () {
            var $input = $(this);
            var option_id = $input.closest(".option-item").attr("data-option_id");
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "update_option",
                    option_id: option_id,
                    value: $input.val()
                },
                success: function (jsone) {
                    project_blink($input, "bg-success", 2);
                    console.log(jsone.data.option_id);
                },
                error: function (res) {
                    local_refresh_option(option_id);
                    var jsone = res.responseJSON;
                    alert(jsone.message);
                }
            });
        });
        $(document).on("click", ".btn-add-option", function () {
            var $btn = $(this);
            var $empty_options = $btn.closest(".option-list-div").find("input.option-title").filter(function () {
                return this.value.trim() == "";
            });
            if ($empty_options.length > 0) {
                project_blink($empty_options, "bg-danger", 2, function () {
                    $empty_options.first().select().focus();
                });
                return;
            }
            var $question_target = $(this).closest(".card-option-list").find(".todo-list");
            var $init = $(".option-item-stock li.option-item").first();
            var $clone = $init.clone();
            $clone.appendTo($question_target);
            bind_sortable($question_target);
            $clone.slideDown("fast", function () {
                var $option = $(this);
                $option.find("input:first").focus();
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "add_option",
                        question_id: $option.closest(".card-question").attr("data-question_id"),
                        rank: $question_target.find("li.option-item").length,
                    },
                    success: function (jsone) {
                        project_blink($option, "bg-warning", 2);
                        console.log(jsone.data.option_id);
                        $option.attr("data-option_id", jsone.data.option_id);
                    },
                    error: function () {
                        $option.slideUp("fast", function () {
                            alert("Une erreur est survenue");
                            $(this).remove()
                        });
                    }
                });
            });
        });
        $(document).on("click", ".btn-remove-option", function () {
            var $btn = $(this);
            var $option_list_div = $btn.closest("ul.option-list");
            $(this).closest("li.option-item").slideUp("fast", function () {
                var $option = $(this);
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "remove_option",
                        option_id: $option.attr("data-option_id")
                    },
                    success: function () {
                        $option.remove();
                        manage_option_ranking($option_list_div);
                    },
                    error: function (res) {
                        $option.slideDown("fast", function () {
                            var jsone = res.responseJSON;
                            alert(jsone.message);
                        });
                    }
                });
            });
        });
    }

    function bind_ballot() {
        $(".btn.btn-remove-ballot").on("click", function () {
            var $btn = $(this);
            if (!confirm("Êtes-vous sûr(e) de vouloir supprimer cette consultation ?\nOpération irréversible !")) {
                return;
            }
            var ballot_id = $("#ballot_id").val();
            if (isNaN(ballot_id) || ballot_id <= 0) {
                alert("Une erreur est survenue !");
                return;
            }
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "remove_ballot",
                    ballot_id: ballot_id,
                },
                success: function (jsone) {
                    window.location.replace("/ballot-list");
                },
                error: function () {
                    alert("Une erreur est survenue");
                }
            });
        });
    }

    function empty_questions() {
        return $("#question_list input[data-field='question_title']").filter(function () {
            return this.value.trim() == "";
        });
    }

    function check_question_filled() {
        var res = true;
        var $question_empty = empty_questions();
        if ($question_empty.length > 0) {
            project_blink($question_empty, "bg-danger", 2, function () {
                $question_empty.find("input").first().focus();
            });
            res = false;
        }
        return res;
    }


    function bind_question() {
        $("#btn-add-question").on("click", function () {
            if (!check_question_filled()) {
                return;
            }

            var $init = $(".question-item-stock .card-question").first();
            var $clone = $init.clone();
            var $question_list = $("#question_list");
            $clone.appendTo($question_list);
            $clone.slideDown("fast", function () {
                var $this_question = $(this);
                $this_question.find("input:first").focus();
                $('html').animate({
                    scrollTop: $this_question.offset().top
                }, 250, function () {
                    console.log("ajax add question");
                    $.ajax({
                        type: "POST",
                        url: "/ballot.php",
                        dataType: "json",
                        data: {
                            action: "add_question",
                            ballot_id: $("#ballot_id").val(),
                            rank: $question_list.find(".card-question").length,
                        },
                        success: function (jsone) {
                            console.log(jsone.data.question_id);
                            $this_question.attr("data-question_id", jsone.data.question_id);
                            project_blink($this_question, "bg-warning", 2);
                            if (true) {
                                var $init_option = $(".option-item-stock li.option-item").first();
                                var $clone_option = $init_option.clone();
                                $clone_option.appendTo($this_question.find(".option-list-div .option-list-fixe"));
                                $clone_option.slideDown("fast", function () {
                                    var $option = $(this);
                                    $option.find("input:first").val("Ne se prononce pas").attr("disabled", true);
                                    project_blink($option, "bg-warning", 2);
                                    $option.attr("data-option_id", jsone.data.option_id);
                                });

                            }
                        },
                        error: function () {
                            $this_question.slideUp("fast", function () {
                                alert("Une erreur est survenue");
                                $(this).remove()
                            });
                        }
                    });

                });
            });
        });
        $(document).on("click", ".btn-save-question", function () {
            var $btn = $(this);
            $btn.parent().find("input[data-field]").each(function () {
                var $input = $(this);
                setTimeout(function () {
                    project_blink($input, "bg-success", 2);
                }, Math.random() * 200);
            });
        });
        $(document).on("click", "button.btn-question-up,button.btn-question-down", function () {
            if (!check_question_filled()) {
                return false;
            }
            var $btn = $(this);
            var order = "up";
            if ($btn.hasClass("btn-question-down")) {
                order = "down";
            }
            $cur = $btn.closest(".card-question");
            $sibling = null;
            $cur.slideUp("fast", function () {
                if (order == "up") {
                    $cur.insertBefore($cur.prev());
                } else {
                    $cur.insertAfter($cur.next());
                }
                manage_question_ranking();
                $cur.slideDown("fast", function () {
                    $('html').animate({
                        scrollTop: $cur.offset().top
                    }, 250, function () {
                        project_blink($cur, "bg-warning", 2);
                    });
                });
            });

        });
        $(document).on("change", ".card-question input[data-field^='question_']", function () {
            var $input = $(this);
            $question = $input.closest(".card-question");
            $.ajax({
                type: "POST",
                url: "/ballot.php",
                dataType: "json",
                data: {
                    action: "update_question",
                    question_id: $question.attr("data-question_id"),
                    field: $input.attr("data-field"),
                    value: $input.val()
                },
                success: function (jsone) {
                    project_blink($input, "bg-success", 2);
                },
                error: function () {
                    alert("Une erreur est survenue");
                }
            });
        });
        $(document).on("click", ".btn-remove-question", function () {
            var $btn = $(this);
            if (!confirm("Êtes-vous sûr(e) de vouloir supprimer cette question ?\nOpération irréversible !")) {
                return;
            }
            $(this).closest(".card-question").slideUp("fast", function () {
                var $question = $(this);
                $.ajax({
                    type: "POST",
                    url: "/ballot.php",
                    dataType: "json",
                    data: {
                        action: "remove_question",
                        question_id: $question.attr("data-question_id")
                    },
                    success: function () {
                        $question.remove();
                        manage_question_ranking()
                    },
                    error: function () {
                        $question.slideDown("fast", function () {
                            alert("Une erreur est survenue");
                        });
                    }
                });
            });
        });
    }


    function form_ballot_submit() {
        $("form#form_ballot").on("submit", function (e) {
            var $this = $(this);
            var data = $this.serialize();
            $.ajax({
                type: $this.attr("method"),
                url: $this.attr("action"),
                dataType: "json",
                data: data,
                success: function (data) {
                    console.log(data);
                    console.log(data.status);
                    if (data.status == "success") {
                        $("#modal_ballot_success").modal();
                        if (data.ballot_id !== undefined) {
                            $("#modal_ballot_success").on("hidden.bs.modal", function () {
                                window.location.replace("/ballot?ballot_id=" + String(encodeURIComponent(data.ballot_id)));
                            });
                            $("input#ballot_id").val(data.ballot_id);
                        }
                    } else {
                        $("#modal_ballot_error_list").empty();
                        for (var i in data.message) {
                            $("#modal_ballot_error_list").append("<li>" + String(data.message[i]) + "</li>");
                        }
                        $("#modal_ballot_error").modal();
                    }
                }
            });
            e.preventDefault();
            return false;
        });
    }


})(jQuery, window, document);

