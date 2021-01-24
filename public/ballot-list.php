<?php
$user = must_be_connected();
local_action($user);
$fw->smarty->assign('menu_actif',"ballot-list");
$fw->include_css('ballot-list');
$fw->add_js('popper.min.js');
$fw->add_js('../plugins/sweetalert2/sweetalert2.min.js');
project_css_js($fw);
$fw->include_js('ballot-list');
$fw->set_canonical('/ballot-list');
$fw->smarty->assign('ballot_list',sql($sql = "
    SELECT
        bal_ballot.*,
        ask_asker.*,
        bal_status.bstatus_lib,
        NOW() > ballot_start AS ballot_started,
        NOW() > ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_finished,
        NOW() > ballot_start AND NOW() < ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_running,
        DATE_FORMAT(ballot_start,'%d/%m/%Y %H:%i') AS ballot_start_fr,
        DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%d/%m/%Y %H:%i') AS ballot_end_fr,
        COUNT(DISTINCT question_id) AS nb_question,
        COUNT(DISTINCT vote_user_id) AS participation
    FROM bal_ballot
    INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
    LEFT JOIN bal_status    ON bstatus_id = ballot_bstatus_id
    LEFT JOIN bal_question  ON question_ballot_id = ballot_id AND question_active = '1'
    LEFT JOIN bal_option    ON option_question_id = question_id AND option_active = '1'
    LEFT JOIN vot_vote      ON vote_option_id = option_id AND vote_active = '1'
    WHERE asker_user_id = '".for_db($user['user_id'])."'
    AND ballot_active = '1'
    GROUP BY ballot_id
    ORDER BY ballot_start DESC
"));
$fw->smarty->display('ballot-list.tpl');
$fw->go();

function local_action($user){
    if(gpost("action")=="delete_ballot"){
        $delete_id = null;
        $res = [
            "status" => "success",
            "message" => []
        ];
        if(!post_exists("ballot_id")){
            $res["message"][] = "Impossible d'identifier la consultation";
        }else{
            $ballot_id = gpost("ballot_id");
            $delete_id = $ballot_id;
            if(!is_numeric($ballot_id) || $ballot_id<=0){
                $res["message"][] = "Impossible d'identifier la consultation";
            }else{
                $ballot_nb = intval(sql_unique("
                    SELECT COUNT(*) AS nb
                    FROM bal_ballot
                    INNER JOIN ask_asker ON asker_id = ballot_asker_id AND asker_active = '1'
                    WHERE ballot_id = '".for_db($ballot_id)."'
                    AND asker_user_id = '".for_db($user["user_id"])."'
                    AND ballot_active = '1'
                "));
                if($ballot_nb<=0){
                    $res["message"][] = "Opération impossible";
                }
            }
        }
        if(count($res["message"])==0){
            if(!is_null($delete_id) && is_numeric($delete_id) && $delete_id>0) {
                cp_delete_ballot($delete_id);
            }
        }else{
            $res["status"] = "error";
        }
        echo json_encode($res);
        exit();
    }
}
?>