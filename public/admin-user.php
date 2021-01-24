<?php
$user = must_be_connected();
must_be_admin();
local_action($user);
$fw->smarty->assign('menu_actif',"admin-user");
$fw->include_css('admin-user');
project_css_js($fw);
$fw->include_js('admin-user');
$fw->set_canonical('/admin-user');
inject_data($fw);
$fw->smarty->display('admin-user.tpl');
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
function local_action_update_user($user) {
    if (post_exists("user_id")) {
        $user_id = gpost("user_id");
        sql("
            UPDATE usr_user SET
            user_nb_active_ballot_allowed = '".for_db(gpost("user_nb_active_ballot_allowed"))."'
            WHERE user_id = '".for_db($user_id)."'
        ");
        ajax_success("User mis à jour", "user");
    }
}
function inject_data(&$fw) {
    $user = null;
    if(request_exists("user_id")){
        $user_id = grequest("user_id");
        $user = sql_shift("
            SELECT *
            FROM usr_user
            WHERE user_id = '".for_db($user_id)."'
        ");
        $user['asker_list'] = sql("
            SELECT
                ask_asker.*,
                COUNT(ballot_id) AS nb_ballot
            FROM ask_asker
            LEFT JOIN bal_ballot ON ballot_asker_id = asker_id AND ballot_active = '1'
            WHERE asker_user_id = '".for_db($user_id)."'
            GROUP BY asker_id
        ");
        $user['ballot_list'] = sql("
            SELECT
                *,
                COUNT(DISTINCT vote_id) AS nb_participation
            FROM ask_asker
            INNER JOIN bal_ballot ON ballot_asker_id = asker_id AND ballot_active = '1'
            LEFT JOIN bal_question ON question_ballot_id = ballot_id AND question_active = '1'
            LEFT JOIN bal_option ON option_question_id = question_id AND option_active = '1'
            LEFT JOIN vot_vote ON vote_option_id = option_id AND vote_active = '1'
            WHERE asker_user_id = '".for_db($user_id)."'
            GROUP BY ballot_id
        ");
    }
    $fw->smarty->assign("user",$user);
}
?>