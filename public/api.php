<?php
local_action();
function local_action() {
    if (request_exists("action")) {
        $action = grequest("action");
        if (is_string($action) && strlen($action) > 0) {
            $func = "local_action_" . $action;
            if (function_exists($func)) {
                $func(cp_session_user());
            }
        }
        exit();
    }
}
function local_action_dashboard($user){
    $res = [];
    $res["time"] = time();
    $res["nb_ballot"] = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM bal_ballot
        INNER JOIN ask_asker ON asker_id = ballot_asker_id AND asker_active = '1'
        WHERE asker_user_id = '".for_db($user['user_id'])."'
        AND ballot_active = '1'
    "));
    $res["nb_vote"] = intval(sql_unique("
        SELECT COUNT(DISTINCT question_id) AS nb
        FROM ask_asker
        INNER JOIN bal_ballot ON asker_id = ballot_asker_id AND ballot_active = '1'
        INNER JOIN bal_question ON question_ballot_id = ballot_id AND question_active = '1'
        INNER JOIN bal_option ON option_question_id = question_id AND option_active = '1'
        INNER JOIN vot_vote ON vote_option_id = option_id AND vote_active = '1'
        WHERE asker_user_id = '".for_db($user['user_id'])."'
        AND asker_active = '1'
    "));
    if($user["user_is_admin"]==1){
        $res["nb_users"] = intval(sql_unique("
            SELECT COUNT(*) AS nb
            FROM usr_user
            WHERE user_active = '1'
            AND user_ban = '0'
        "));
    }
    ajax_success("dashboard","dashboard",$res);
}
?>