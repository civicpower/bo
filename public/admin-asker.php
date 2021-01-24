<?php
$user = must_be_connected();
must_be_admin();
local_action($user);
$fw->smarty->assign('menu_actif',"admin-user");
$fw->include_css('admin-asker');
project_css_js($fw);
$fw->include_js('admin-asker');
$fw->set_canonical('/admin-asker');
inject_data($fw);
$fw->smarty->display('admin-asker.tpl');
$fw->go();
function local_action($user) {
    if (post_exists("action")) {
        $action = gpost("action");
        if (is_string($action) && strlen($action) > 0) {
            $func = "local_action_" . $action;
            if (function_exists($func)) {
                $func($user);
            }
        }
        exit();
    }
}
function local_action_update_asker_type($user) {
    if (post_exists("astyp_id") && post_exists("asker_id")) {
        $asker_id = gpost("asker_id");
        $astyp_id = gpost("astyp_id");
        sql("
            UPDATE ask_asker SET
            asker_astyp_id = '".for_db($astyp_id)."'
            WHERE asker_id = '".for_db($asker_id)."'
        ");
        ajax_success("Type de asker mis à jour", "cities");
    }
}

function inject_data(&$fw) {
    $asker = null;
    if(request_exists("asker_id")) {
        $asker_id = grequest("asker_id");
        if(is_numeric($asker_id) && $asker_id>0) {
            $asker = sql_shift("
                SELECT *
                FROM ask_asker
                WHERE asker_id = '" . for_db($asker_id) . "'
            ");
            $asker["user"] = sql_shift("
                SELECT *
                FROM usr_user
                WHERE user_id = '".for_db($asker["asker_user_id"])."'
            ");
            $asker["ballot_list"] = sql("
                SELECT *
                FROM bal_ballot
                WHERE ballot_asker_id = '".for_db($asker_id)."'
                AND ballot_active  = '1'
            ");
        }

    }
    $fw->smarty->assign("asker",$asker);
    $fw->smarty->assign("ask_type",sql("
        SELECT *
        FROM ask_type
        ORDER BY astyp_lib ASC
    "));
}
?>