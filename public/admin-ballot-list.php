<?php
$user = must_be_connected();
must_be_admin();
$fw->smarty->assign('menu_actif', "admin-ballot-list");
$fw->add_js('popper.min.js');
$fw->add_js('../plugins/sweetalert2/sweetalert2.min.js');
project_css_js($fw);
$fw->include_css('admin-ballot-list');
$fw->include_js('admin-ballot-list');
$fw->set_canonical('/admin-ballot-list');
inject_ballot_list($fw);
$fw->smarty->display('admin-ballot-list.tpl');
$fw->go();

function inject_ballot_list(&$fw) {
    $mpp = 10;
    $pagestart = 0;
    $page = 1;
    if (request_exists("page")) {
        $tmp = grequest("page");
        if (is_numeric($tmp) && $tmp > 0) {
            $page = $tmp;
        }
    }
    $pagestart = ($page - 1) * $mpp;

    $where = [];
    if(request_exists("search")){
        $search = grequest("search");
        if(is_string($search) && strlen($search)>0){
            $search = explode(' ',$search);
            foreach($search as $k => $v){
                $where[] = "
                    AND CONCAT(
                        ' ',
                        ballot_title,' ',
                        ballot_description,' ',
                        ballot_shortcode,' ',
                        ballot_engagement,' ',
                        asker_name,' ',
                        bstatus_lib,' ',
                        user_firstname,' ',
                        user_lastname,' ',
                        user_phone_national,' ',
                        user_phone_dial,' ',
                        user_phone_international,' ',
                        user_email,' '
                    ) LIKE CONCAT('%','".for_db($v)."','%')
                ";
            }
        }
    }
    if(request_exists("bstatus_id")) {
        $bstatus_id = grequest("bstatus_id");
        if(is_numeric($bstatus_id) && $bstatus_id>0) {
            $where[] = "
                AND ballot_bstatus_id = '" . for_db($bstatus_id) . "'
            ";
        }
    }
    if(!request_exists("ballot_active")){
        $where [] = "
            AND ballot_active = '1'
        ";
    }
    $list = sql($sql = "
        SELECT
            bal_ballot.*,
            ask_asker.*,
            usr_user.*,
            bal_status.bstatus_lib,
            NOW() > ballot_start AS ballot_started,
            NOW() > ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_finished,
            NOW() > ballot_start AND NOW() < ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_running,
            DATE_FORMAT(ballot_start,'%d/%m/%Y %H:%i') AS ballot_start_fr,
            DATE_FORMAT(ballot_start + INTERVAL ballot_duration_second SECOND,'%d/%m/%Y %H:%i') AS ballot_end_fr,
            COUNT(DISTINCT question_id) AS nb_question,
            COUNT(DISTINCT vote_user_id) AS participation
        FROM bal_ballot
        INNER JOIN ask_asker    ON ballot_asker_id = asker_id AND asker_active = '1'
        LEFT JOIN bal_status    ON bstatus_id = ballot_bstatus_id
        LEFT JOIN bal_question  ON question_ballot_id = ballot_id AND question_active = '1'
        LEFT JOIN bal_option    ON option_question_id = question_id AND option_active = '1'
        LEFT JOIN vot_vote      ON vote_option_id = option_id AND vote_active = '1'
        LEFT JOIN usr_user      ON user_id = asker_user_id AND user_active = '1' AND user_ban = '0'
        WHERE 1=1
        ".implode(" ",$where)."
        GROUP BY ballot_id
        ORDER BY
                ballot_start DESC,
                ballot_start + INTERVAL ballot_duration_second SECOND DESC
        LIMIT $pagestart,$mpp
    ");
    $fw->smarty->assign('ballot_list', $list);
    $fw->smarty->assign('bstatus_list',sql("
        SELECT *
        FROM bal_status
        ORDER BY bstatus_id ASC
    "));
}
?>