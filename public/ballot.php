<?php
$user = must_be_connected();
local_action($user);
$ballot = local_check_ballot_exists($fw, $user);
project_css_js($fw);
$fw->smarty->assign('menu_actif', "ballot-list");
local_inject_infos($fw, $user, $ballot);
$fw->include_css('ballot');
$fw->include_js('ballot');
$fw->add_js('/plugins/jquery-ui/jquery-ui.min.js');
$fw->add_js('/plugins/moment/moment.min.js');
$fw->add_js('/plugins/daterangepicker/daterangepicker.js');
$fw->add_css('../../plugins/daterangepicker/daterangepicker.css');
$fw->add_css('label-placeholder');
$fw->add_css('../../plugins/icheck-bootstrap/icheck-bootstrap.min.css');
$fw->set_canonical('/ballot');
$fw->smarty->display('ballot.tpl');
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
function local_inject_infos(&$fw, $user, $ballot) {
    if ($user['user_is_admin'] && $ballot['ballot_id']) {
        $where = "
            AND asker_id = '" . for_db($ballot['ballot_asker_id']) . "'
        ";
    } else {
        $where = "
            AND asker_user_id = '" . for_db($user['user_id']) . "'
        ";
    }
    $fw->smarty->assign('asker_list', sql("
        SELECT *
        FROM ask_asker
        WHERE asker_active = '1'
        $where
        ORDER BY asker_name ASC
    "));
    $fw->smarty->assign('user', $user);
    if ($user['user_is_admin']) {
        $fw->smarty->assign('bal_status', sql("
            SELECT *
            FROM bal_status
            ORDER BY bstatus_id ASC
        "));
    }
}
function local_check_form() {
    $error = [];
    if (!post_exists("ballot_asker_id")) {
        $error[] = "Compte organisateur obligatoire.";
    } else {
        $tmp = gpost("ballot_asker_id");
        if (!is_numeric($tmp) || $tmp <= 0) {
            $error[] = "Compte organisateur obligatoire";
        }
    }
    if (!post_exists("ballot_title")) {
        $error[] = "Titre de la consultation obligatoire.";
    } else {
        $tmp = gpost("ballot_title");
        if (!is_string($tmp) || strlen($tmp) <= 0) {
            $error[] = "Titre de la consultation obligatoire";
        } else if (strlen($tmp) > 250) {
            $error[] = "Le titre de la consultation est trop long";
        }
    }
    if (post_exists("ballot_description")) {
        // facultatif
        //$error[] = "Description de la consultation obligatoire.";
    } else {
        $tmp = gpost("ballot_description");
        if (!is_string($tmp) || strlen($tmp) <= 0) {
            //$error[] = "Description de la consultation obligatoire";
        } else if (strlen($tmp) > 999) {
            $error[] = "La description de la consultation est trop longue";
        }
    }
    if (!post_exists("ballot_engagement")) {
        $error[] = "Engagement de la consultation obligatoire.";
    } else {
        $tmp = gpost("ballot_engagement");
        if (!is_string($tmp) || strlen($tmp) <= 0) {
            $error[] = "Engagement de la consultation obligatoire";
        }
    }
    if(gpost("ballot_asap")==1) {
        $date_start = $_POST["ballot_start"] = civicpower_datetime_rfc(date("Y-m-d H:i:s"));
    }else{
        if (!post_exists("ballot_start_date") || !post_exists("ballot_start_time")) {
            $error[] = "Date de début de la consultation obligatoire";
        } else {
            $tmp = gpost("ballot_start_date")." ".gpost("ballot_start_time");
            $date_start = sql_unique("
                SELECT '" . for_db($tmp) . "' + INTERVAL 0 SECOND AS nb
            ");
            if (!is_string($date_start) || strlen($date_start) <= 0) {
                $error[] = "Le format de la date de début de la consultation est incorrect";
            } else {
                $_POST["ballot_start"] = $date_start;
            }
        }
    }
    if (!post_exists("ballot_end_date") || !post_exists("ballot_end_time")) {
        $error[] = "Date de fin de la consultation obligatoire";
    } else {
        $tmp = gpost("ballot_end_date")." ".gpost("ballot_end_time");
        $date_end = sql_unique("
            SELECT '" . for_db($tmp) . "' + INTERVAL 0 SECOND AS nb
        ");
        if (!is_string($date_end) || strlen($date_end) <= 0) {
            $error[] = "Le format de la date de fin de la consultation est incorrect";
        } else {
            $_POST["ballot_end"] = $date_end;
        }
    }
    if (count($error) <= 0) {
        $time_start = strtotime($date_start);
        $time_end = strtotime($date_end);
        if ($time_end < $time_start) {
            $error[] = "Vous avez choisi une fin de consultation avant le début";
        } else {
            $duree_sec = $time_end - $time_start;
            if ($duree_sec<10*60) {
                $error[] = "Votre consultation ne dure pas assez longtemps. Merci de rallonger sa durée.";
            }
        }
    }
    return $error;
}
function local_action_update_username($user) {
    $firstname = "";
    $lastname = "";
    if (post_exists("firstname")) {
        $tmp = gpost("firstname");
        if (is_string($tmp) && strlen($tmp) > 1 && strlen($tmp) < 50) {
            $firstname = $tmp;
        }
    }
    if (post_exists("lastname")) {
        $tmp = gpost("lastname");
        if (is_string($tmp) && strlen($tmp) > 1 && strlen($tmp) < 50) {
            $lastname = $tmp;
        }
    }
    if ($firstname == "") {
        ajax_error("Merci d'indiquer un prénom");
    }
    if ($lastname == "") {
        ajax_error("Merci d'indiquer un nom de famille");
    }
    sql("
        UPDATE usr_user SET
            user_firstname = '" . for_db($firstname) . "',
            user_lastname = '" . for_db($lastname) . "'
        WHERE user_id = '" . for_db($user['user_id']) . "'
    ");
    $_SESSION['user']['user_firstname'] = $firstname;
    $_SESSION['user']['user_lastname'] = $lastname;
    ajax_success("Infos mises à jour", "user updated");
}
function local_action_get_cities($user) {
    if (post_exists("zipcode")) {
        $zipcode = gpost("zipcode");
        $communes = sql("
            SELECT city_id,nom_commune
            FROM (SELECT DISTINCT * FROM geo_fr_cities WHERE Code_postal = '" . for_db($zipcode) . "' ORDER BY city_id) t
            GROUP BY city_id
            ORDER BY nom_commune ASC
        ");
        ajax_success("Liste des communes", "cities", $communes);
    }
}
function local_action_question_rank($user) {
    if (!post_exists("data")) {
        ajax_error("Données de tri manquantes", "rank_data_not_found_1");
    }
    $data = gpost("data");
    if (!isset($data) || !is_array($data) || count($data) <= 0) {
        ajax_error("Données de tri manquantes", "rank_data_not_found_2");
    }
    foreach ($data as $k => $v) {
        if (!isset($v['question_id']) || !isset($v['rank']) || !is_numeric($v['question_id']) || !is_numeric($v['rank'])) {
            ajax_error("Données incorrectes");
        }
    }
    foreach ($data as $k => $v) {
        ajax_assert_user_can_manage("bal_question", $v['question_id'], $user);
        ajax_assert_editable("bal_question", $v['question_id'], $user);
    }
    foreach ($data as $k => $v) {
        sql("
            UPDATE bal_question SET
            question_rank = '" . for_db($v['rank']) . "'
            WHERE question_id = '" . for_db($v['question_id']) . "'
        ");
    }
    ajax_success("Ordre de Tri mis à jour");
}
function local_action_option_rank($user) {
    if (!post_exists("data")) {
        ajax_error("Données de tri manquantes", "rank_data_not_found_1");
    }
    $data = gpost("data");
    if ($data == 0) {
        $data = [];
    }
    if (is_array($data) && count($data) > 0) {
        foreach ($data as $k => $v) {
            if (!isset($v['option_id']) || !isset($v['rank']) || !is_numeric($v['option_id']) || !is_numeric($v['rank'])) {
                ajax_error("Données incorrectes");
            }
        }
    }
    foreach ($data as $k => $v) {
        ajax_assert_user_can_manage("bal_option", $v['option_id'], $user);
        ajax_assert_editable("bal_option", $v['option_id'], $user);
    }
    foreach ($data as $k => $v) {
        sql("
            UPDATE bal_option SET
            option_rank = '" . for_db($v['rank']) . "'
            WHERE option_id = '" . for_db($v['option_id']) . "'
        ");
    }
    ajax_success("Ordre de Tri mis à jour");
}
function local_action_admin_validate($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_is_admin($user);
    ajax_assert_ballot_is_waiting_validation($ballot_id);
    civicpower_invoke_ballot_accepted($ballot_id);
    if (true) {
        $ballot = civicpower_ballot_and_asker($ballot_id);
        $set = "";
        if($ballot['ballot_asap']==1){
            $set = ",
                ballot_start = NOW()
            ";
        }
        $acceptation_reason = "";
        if (request_exists("acceptation_reason")) {
            $tmp = grequest("acceptation_reason");
            if (is_string($tmp) && strlen($tmp) > 0) {
                $acceptation_reason = $tmp;
            }
        }
        sql("
            UPDATE bal_ballot
            SET ballot_bstatus_id = '" . for_db($_ENV['STATUS_BALLOT_VALIDE_EN_ATTENTE']) . "',
            ballot_acceptation_reason = '".for_db($acceptation_reason)."'
            $set
            WHERE ballot_id = '" . for_db($ballot_id) . "'
        ");
    }
    ajax_success("Consultation validée", "ballot_validated");
}
function local_action_admin_reject($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_is_admin($user);
    ajax_assert_ballot_is_waiting_validation($ballot_id);
    $rejection_reason = "";
    if (request_exists("rejection_reason")) {
        $tmp = grequest("rejection_reason");
        if (is_string($tmp) && strlen($tmp) > 0) {
            $rejection_reason = $tmp;
        }
    }
    sql("
        UPDATE bal_ballot SET
            ballot_bstatus_id = '" . for_db($_ENV['STATUS_BALLOT_REFUSE']) . "',
            ballot_rejection_reason = '" . for_db($rejection_reason) . "'
        WHERE ballot_id = '" . for_db($ballot_id) . "'
    ");
    civicpower_invoke_ballot_rejected($ballot_id);
    ajax_success("Consultation rejetée", "ballot_rejected");
}
function local_action_load_cities($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    $cities = sql("
        SELECT
            bfilter_id,
            code_postal,
            nom_commune
        FROM bal_filter
        INNER JOIN geo_fr_cities ON city_id = bfilter_city_id
        WHERE bfilter_ballot_id = '" . for_db($ballot_id) . "'
        AND bfilter_active = '1'
    ");
    ajax_success("Liste des communes", "cities_list", $cities);
}
function local_action_remove_city($user) {
    $bfilter_id = ajax_assert_exists("bfilter_id", true, true);
    $ballot_id = intval(sql_unique("
        SELECT bfilter_ballot_id
        FROM bal_filter
        WHERE bfilter_id = '" . for_db($bfilter_id) . "'
    "));
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    sql("
        UPDATE bal_filter
        SET bfilter_active = '0'
        WHERE bfilter_id = '" . for_db($bfilter_id) . "'
    ");
    ajax_success("Commune supprimée", "city_removed");
}
function local_action_check_quota($user) {
    $nb_active = get_nb_active_ballot($user['user_id']);
    $nb_allowed = $user['user_nb_active_ballot_allowed'];
    if ($user['user_is_admin']) {
        $nb_allowed = 999;
    }
    ajax_success("Quota", "quota", ['nb_active' => $nb_active, 'nb_allowed' => $nb_allowed,]);
}
function local_action_check_ballot_integrity($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    $questions = sql("
        SELECT *, COUNT(option_id) AS nb_option
        FROM bal_question
        LEFT JOIN bal_option ON option_question_id = question_id AND option_active = '1' AND option_can_be_deleted = '1' AND LENGTH(TRIM(option_title))>0
        WHERE question_ballot_id = '".for_db($ballot_id)."'
        AND question_active = '1'
        GROUP BY question_id
    ");
    if(count($questions)==0){
        ajax_error("Votre consultation doit comporter au moins une question","ballot_fail",$questions);
    }
    foreach($questions as $k => $v){
        if($v["nb_option"]<2){
            ajax_error("Chaque question posée doit avoir au moins deux réponses possibles","ballot_fail",$questions);
        }
        if(strlen(trim($v["question_title"]))==0){
            ajax_error("Chaque question posée doit avoir un libellé","ballot_fail",$questions);
        }
    }
    ajax_success("Consultation conforme", "ballot_granted",$questions);
}
function local_action_add_city($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    $city_id = ajax_assert_exists("city_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    $nb_city = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM geo_fr_cities
        WHERE city_id = '" . for_db($city_id) . "'
    "));
    if ($nb_city <= 0) {
        ajax_error("Cette commune est inconnue");
    }
    $id = intval(sql_unique("
        SELECT bfilter_id
        FROM bal_filter
        WHERE bfilter_ballot_id = '" . for_db($ballot_id) . "'
        AND bfilter_city_id = '" . for_db($city_id) . "'
    "));
    if ($id <= 0) {
        $id = sql("
            INSERT INTO bal_filter SET
            bfilter_ballot_id = '" . for_db($ballot_id) . "',
            bfilter_city_id = '" . for_db($city_id) . "'
        ");
    }
    sql("
        UPDATE bal_filter SET
        bfilter_active = '1'
        WHERE bfilter_id = '" . for_db($id) . "'
    ");
    $city = sql_shift("
        SELECT
            bfilter_id,
            code_postal,
            nom_commune
        FROM bal_filter
        INNER JOIN geo_fr_cities ON city_id = bfilter_city_id
        WHERE bfilter_id = '" . for_db($id) . "'
        LIMIT 1
    ");
    ajax_success("Commune ajoutée", "city_added", $city);
}
function local_action_ballot_publish($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    ajax_assert_publishable_ballot($ballot_id);
    sql("
        UPDATE bal_ballot SET
        ballot_bstatus_id = '" . for_db($_ENV['STATUS_BALLOT_EN_ATTENTE_DE_VALIDATION']) . "'
        WHERE ballot_id = '" . for_db($ballot_id) . "'
    ");
    civicpower_invoke_ballot_en_attente($ballot_id);
    ajax_success("Statut modifié", "status_updated");
}
function local_action_ballot_edit($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    //    ajax_assert_editable("bal_ballot",$ballot_id, $user);
    sql("
        UPDATE bal_ballot SET
        ballot_bstatus_id = '" . for_db($_ENV['STATUS_BALLOT_EN_COURS_DE_CREATION']) . "'
        WHERE ballot_id = '" . for_db($ballot_id) . "'
    ");
    ajax_success("Statut modifié", "status_updated");
}
function local_action_remove_ballot($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    cp_delete_ballot($ballot_id);
    ajax_success("Consultation supprimée", "ballot_removed", ["ballot_id" => $ballot_id]);
}
function local_action_ballot_remove_voter($user) {
    $bfilter_id = ajax_assert_exists("bfilter_id", true, true);
    $ballot_id = sql_unique("
        SELECT bfilter_ballot_id
        FROM bal_filter
        WHERE bfilter_id = '" . for_db($bfilter_id) . "'
    ");
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    sql("
        UPDATE bal_filter SET
        bfilter_active = '0'
        WHERE bfilter_id = '" . for_db($bfilter_id) . "'
    ");
    ajax_success("Voter removed", "voter_removed");
}
function local_action_get_voters($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    //    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    ajax_success("Voters list", "voters_list", ['voters' => get_voters($ballot_id)]);
}
function local_action_remove_question($user) {
    $question_id = ajax_assert_exists("question_id", true, true);
    ajax_assert_user_can_manage("bal_question", $question_id, $user);
    ajax_assert_editable("bal_question", $question_id, $user);
    cp_delete_question($question_id);
    ajax_success("Question supprimée");
}
function local_action_ballot_add_voters_file($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    if (files_exists("file")) {
        $file = gfile("file");
        if (!isset($file["tmp_name"])) {
            ajax_error("File not uploaded correctly", "file_not_uploaded_correctly");
        }
        $tmp_name = $file["tmp_name"];
        if (!file_exists($tmp_name)) {
            ajax_error("File not uploaded correctly", "file_not_uploaded_correctly");
        }
        $md5 = md5_file($tmp_name);
        $dest = dirname(__FILE__) . "/uploads/voters/" . $md5;
        if (!file_exists($dest)) {
            if (!move_uploaded_file($tmp_name, $dest)) {
                ajax_error("File not uploaded correctly", "file_not_uploaded_correctly");
            }
        }
        sql("
            INSERT INTO usr_file_upload SET
            uupload_user_id = '" . for_db($user['user_id']) . "',
            uupload_creation = NOW(),
            uupload_filename = '" . for_db($dest) . "',
            uupload_md5 = '" . for_db($md5) . "',
            uupload_context = '" . for_db("voters") . "'
        ");
        $_POST["text"] = file_get_contents($dest);
        local_action_ballot_add_voters($user);
    } else {
        ajax_error("File not uploaded", "file_not_uploaded");
    }
}
function local_good_email_tld($str) {
    $res = false;
    static $tld = null;
    if (is_null($tld)) {
        $filename = dirname(__FILE__) . "/../inc/extra/email-tlds.txt";
        $tld = file($filename);
        $tld = array_unique($tld);
        $tld = array_map("trim", $tld);
        foreach ($tld as $k => $v) {
            if (preg_match("~^#~", $v) || strlen($v) == 0) {
                unset($tld[$k]);
            }
        }
    }
    foreach ($tld as $k => $v) {
        if (preg_match("~" . preg_quote("." . $v, "~") . "$~i", $str)) {
            $res = true;
        }
    }
    return $res;
}
function local_action_ballot_add_voters($user) {
    $text = ajax_assert_exists("text");
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    $text = strtolower($text);
    $text = preg_replace("~\s+~", " ", $text);
    $text = explode(" ", $text);
    $text = array_map("trim", $text);
    $text = array_unique($text);
    $res = ["email" => [], "phone" => [], "unknown" => [],];
    foreach ($text as $k => $v) {
        if (civicpower_is_email($v)) {
            if (local_good_email_tld($v)) {
                $res["email"][] = $v;
            } else {
                $res["unknown"][] = $v;
            }
        } else {
            $tmp = $v;
            $tmp = preg_replace("~[\.\-,;]+~", "", $tmp);
            if (civicpower_is_french_phone($tmp)) {
                $res["phone"][] = civicpower_international_phone($tmp);
            } else {
                if (substr_count($v, '@') == 1 && strlen($v) < 50) {
                    $res["unknown"][] = $v;
                } else {
                    $tmp = preg_replace("~\D~", "", $v);
                    if (strlen($tmp) > 6) {
                        $res["unknown"][] = $v;
                    }
                }
            }
        }
    }
    $already = get_voters($ballot_id);
    foreach ($res["email"] as $k => $v) {
        if (in_array($v, $already['email'])) {
            continue;
        }
        sql("
            INSERT INTO bal_filter SET
            bfilter_ballot_id = '" . for_db($ballot_id) . "',
            bfilter_email = '" . for_db($v) . "'
        ");
    }
    foreach ($res["phone"] as $k => $v) {
        if (in_array($v, $already['phone'])) {
            continue;
        }
        sql("
            INSERT INTO bal_filter SET
            bfilter_ballot_id = '" . for_db($ballot_id) . "',
            bfilter_phone_international = '" . for_db($v) . "'
        ");
    }
    $res = array_map("array_unique", $res);
    ajax_success("Liste filtrée", "filtred_list", ['voters' => get_voters($ballot_id), 'unknown' => $res["unknown"]]);
}
function get_voters($ballot_id) {
    $res = ["phone" => [], "email" => [],];
    $list = sql("
        SELECT *
        FROM bal_filter
        WHERE bfilter_ballot_id = '" . for_db($ballot_id) . "'
        AND bfilter_active = '1'
    ");
    foreach ($list as $k => $v) {
        $res['email'][$v['bfilter_id']] = $v['bfilter_email'];
        $res['phone'][$v['bfilter_id']] = $v['bfilter_phone_international'];
    }
    $res = array_map("array_unique", $res);
    foreach ($res as $k => $v) {
        $res[$k] = array_filter($res[$k], "strlen");
    }
    return $res;
}
function local_action_add_question($user) {
    $ballot_id = ajax_assert_exists("ballot_id", true, true);
    $rank = ajax_assert_exists("rank", true, true);
    ajax_assert_user_can_manage("bal_ballot", $ballot_id, $user);
    ajax_assert_editable("bal_ballot", $ballot_id, $user);
    $question_id = sql("
        INSERT INTO bal_question
        SET question_ballot_id = '" . for_db($ballot_id) . "',
        question_rank = '" . for_db($rank) . "'
    ");
    $option_id = sql("
        INSERT INTO bal_option SET
        option_question_id = '" . for_db($question_id) . "',
        option_title = 'Ne se prononce pas',
        option_rank = '999',
        option_can_be_deleted = '0'
    ");
    ajax_success("Question ajoutée", "question_added", ["question_id" => $question_id, "option_id" => $option_id]);
}
function local_action_add_option($user) {
    $question_id = ajax_assert_exists("question_id", true, true);
    $rank = ajax_assert_exists("rank", true, true);
    ajax_assert_user_can_manage("bal_question", $question_id, $user);
    ajax_assert_editable("bal_question", $question_id, $user);
    $option_id = sql("
        INSERT INTO bal_option SET
        option_question_id = '" . for_db($question_id) . "',
        option_rank = '" . for_db($rank) . "'
    ");
    ajax_success("Option ajoutée", "option_added", ["option_id" => $option_id]);
}
function local_action_update_question($user) {
    $question_id = ajax_assert_exists("question_id", true, true);
    $field = ajax_assert_exists("field");
    if (!in_array($field, ['question_title', 'question_description'])) {
        ajax_error("Champ inconnu", "unknown_field");
    }
    $value = ajax_assert_exists("value");
    ajax_assert_user_can_manage("bal_question", $question_id, $user);
    ajax_assert_editable("bal_question", $question_id, $user);
    sql("
        UPDATE bal_question SET
        $field = '" . for_db($value) . "'
        WHERE question_id = '" . for_db($question_id) . "'
    ");
    ajax_success("Question mise à jour");
}
function local_action_get_option($user) {
    $option_id = ajax_assert_exists("option_id", true, true);
    ajax_assert_user_can_manage("bal_option", $option_id, $user);
    $option_title = sql_unique("
        SELECT option_title FROM bal_option
        WHERE option_id = '" . for_db($option_id) . "'
    ");
    ajax_success("Option", "option", $option_title);
}
function local_action_update_option($user) {
    $option_id = ajax_assert_exists("option_id", true, true);
    $value = ajax_assert_exists("value");
    ajax_assert_user_can_manage("bal_option", $option_id, $user);
    ajax_assert_option_editable($option_id, $user);
    ajax_assert_option_not_duplicate($option_id, $value);
    sql("
        UPDATE bal_option
        SET option_title = '" . for_db($value) . "'
        WHERE option_id = '" . for_db($option_id) . "'
    ");
    ajax_success("Option modifiée");
}
function local_action_remove_option($user) {
    $option_id = ajax_assert_exists("option_id", true, true);
    ajax_assert_user_can_manage("bal_option", $option_id, $user);
    ajax_assert_option_editable($option_id, $user);
    cp_delete_option($option_id);
    ajax_success("Option supprimée");
}
function local_action_save_ballot($user) {
    $ajax_return = ["status" => "success", "message" => []];
    $mode = "update";
    if (!post_exists("ballot_id")) {
        $mode = "insert";
    } else {
        $ballot_id = gpost("ballot_id");
        $ballot = local_get_ballot($ballot_id, $user);
        if (!is_array($ballot) || count($ballot) <= 0 || !isset($ballot['asker_id'])) {
            $ajax_return["message"][] = "Opération non autorisée ! (err_" . __LINE__ . ")";
        }
        if (!cp_is_editable("bal_ballot", $ballot_id, $user)) {
            $ajax_return["message"][] = "Consultation non éditable";
        }
    }

    $ajax_return["message"] = array_merge($ajax_return["message"], local_check_form());
    if (count($ajax_return['message']) > 0) {
        $ajax_return["status"] = "error";
    } else {
        $sql = [];
        if ($mode == "update") {
            $sql[] = "UPDATE bal_ballot SET";
        } else {
            $sql[] = "INSERT " . " INTO bal_ballot SET";
            $sql[] = "ballot_shortcode = '" . for_db(cp_new_ballot_shortcode()) . "',";
        }
        $ballot_duration_second = strtotime($_POST["ballot_end"]) - strtotime($_POST["ballot_start"]);
        $sql[] = "ballot_asker_id = '" . for_db($_POST["ballot_asker_id"]) . "',";
        $sql[] = "ballot_title = '" . for_db($_POST["ballot_title"]) . "',";
        $sql[] = "ballot_description = '" . for_db($_POST["ballot_description"]) . "',";
        $sql[] = "ballot_engagement = '" . for_db($_POST["ballot_engagement"]) . "',";
        $sql[] = "ballot_start = '" . for_db($_POST["ballot_start"]) . "',";
        $sql[] = "ballot_duration_second = '" . for_db($ballot_duration_second) . "',";
        $sql[] = "ballot_asap = '" . for_db($_POST["ballot_asap"]) . "',";
        $sql[] = "ballot_can_change_vote = '" . for_db(post_exists("ballot_can_change_vote") ? 1 : 0) . "',";
        $sql[] = "ballot_share = '" . for_db(post_exists("ballot_share") ? 1 : 0) . "',";
        $sql[] = "ballot_see_results_live = '" . for_db(post_exists("ballot_see_results_live") ? 1 : 0) . "'";
        if ($mode == "update") {
            $sql[] = "WHERE ballot_id = '" . for_db(gpost("ballot_id")) . "'";
        }
        $sql = implode("\n", $sql);
        $ret = sql($sql);
        if ($mode == "insert") {
            $ballot_id = $ret;
            $ajax_return["ballot_id"] = $ballot_id;
            $sql2 = "INSERT" . " " . "INTO bal_filter SET bfilter_ballot_id = '" . for_db($ballot_id) . "', ";
            if (isset($user['user_phone_international']) && is_string($user['user_phone_international']) && strlen($user['user_phone_international']) > 5) {
                $sql2 .= " bfilter_phone_international = '" . for_db($user['user_phone_international']) . "'";
            } else if (isset($user['user_email']) && is_string($user['user_email']) && strlen($user['user_email']) > 5) {
                $sql2 .= " bfilter_email = '" . for_db($user['user_email']) . "'";
            }
            sql($sql2);
        }else{
            $ballot_id = gpost("ballot_id");
        }
        if(post_exists("ballot_open")) {
            $bfilter = sql_shift("
                SELECT *
                FROM bal_filter
                WHERE bfilter_all = '1'
                AND bfilter_ballot_id = '".for_db($ballot_id)."'
            ");
            if(!is_array($bfilter) || count($bfilter)<=0){
                sql("
                    INSERT INTO bal_filter SET
                    bfilter_all = '1',
                    bfilter_ballot_id = '".for_db($ballot_id)."'
                ");
            }else{
                if($bfilter["bfilter_active"]==0){
                    sql("
                        UPDATE bal_filter SET
                        bfilter_active = '1'
                        WHERE bfilter_id = '".for_db($bfilter["bfilter_id"])."'
                    ");
                }
            }
        }else{
            sql("
                UPDATE bal_filter
                SET bfilter_active = '0'
                WHERE bfilter_ballot_id = '".for_db($ballot_id)."'
                AND bfilter_all = '1'
            ");
        }

    }
    $ajax_return["post"] = gpost();
    echo json_encode($ajax_return);
}
function local_check_ballot_exists(&$fw, $user) {
    $ballot = [];
    if (get_exists("ballot_id")) {
        $ballot_id = gget("ballot_id");
        $ballot = local_get_ballot($ballot_id, $user);
        if (is_array($ballot) && count($ballot) > 0) {
            $ballot["open"] = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM bal_filter
                WHERE bfilter_all = 1
                AND bfilter_ballot_id = '".for_db($ballot_id)."'
                AND bfilter_active = 1
            "))==1;
            $ballot['question'] = sql("
                SELECT *
                FROM bal_question
                WHERE question_ballot_id = '" . for_db($ballot_id) . "'
                AND question_active = '1'
                ORDER BY question_rank ASC
            ");
            foreach ($ballot["question"] as $k => &$v) {
                $list = sql($sql = "
                    SELECT
                        *,
                        COUNT(vote_user_id) AS nb_vote
                    FROM bal_option
                    LEFT JOIN vot_vote ON vote_option_id = option_id AND vote_active = '1'
                    WHERE option_question_id = '" . for_db($v["question_id"]) . "'
                    AND option_active = '1'
                    GROUP BY option_id
                    ORDER BY option_rank ASC
                ");
                $total = 0;
                foreach ($list as $kk => $vv) {
                    $total += $vv['nb_vote'];
                }
                foreach ($list as $kk => $vv) {
                    $list[$kk]['prc_vote'] = $total == 0 ? 0 : floatval($vv['nb_vote']) * 100 / $total;
                }
                $v["option"] = $list;
            }
            $ballot["ballot_participation"] = intval(sql_unique("
                SELECT COUNT(DISTINCT vote_user_id) AS participation
                FROM bal_ballot
                INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
                LEFT JOIN bal_status    ON bstatus_id = ballot_bstatus_id
                LEFT JOIN bal_question  ON question_ballot_id = ballot_id AND question_active = '1'
                LEFT JOIN bal_option    ON option_question_id = question_id AND option_active = '1'
                LEFT JOIN vot_vote      ON vote_option_id = option_id AND vote_active = '1'
                WHERE ballot_id = '" . for_db($ballot_id) . "'
                AND ballot_active = '1'
                AND (asker_user_id = '" . for_db($user['user_id']) . "' OR '" . ($user['user_is_admin']) . "')
                GROUP BY ballot_id
                ORDER BY ballot_start DESC
            "));
        }
    }
    if (!isset($ballot['ballot_start'])) {
        $nb_hour_start = 48;
        $nb_second_duration = 3600 * 48;
        if ($user['user_is_admin']) {
            $nb_hour_start = 0;
        }
        $ballot = sql_shift("
            SELECT
                NOW() + INTERVAL $nb_hour_start HOUR AS ballot_start,
                NOW() + INTERVAL $nb_hour_start HOUR + INTERVAL $nb_second_duration SECOND AS ballot_end
        ");
    }
    $nb_hour_min = 3;
    if ($user['user_is_admin']) {
        $nb_hour_min = -24*30;
    }
    $ballot['dt_start'] = civicpower_datetime_rfc($ballot['ballot_start']);
    $ballot['dt_end'] = civicpower_datetime_rfc($ballot['ballot_end']);
    $ballot['dt_min'] = civicpower_datetime_rfc(date("Y-m-d H:i:s", time() + $nb_hour_min * 3600));
    $fw->smarty->assign('ballot', $ballot);
    $fw->smarty->assign('user', $user);
    return $ballot;
}
function local_get_ballot($ballot_id, $user) {
    $res = sql_shift($sql2 = "
        SELECT
            *,
            ballot_start + INTERVAL ballot_duration_second SECOND AS ballot_end
        FROM bal_ballot
        INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
        INNER JOIN ask_type ON asker_astyp_id = astyp_id
        WHERE (asker_user_id = '" . for_db($user['user_id']) . "' OR '" . for_db($user['user_is_admin']) . "')
        AND ballot_id = '" . for_db($ballot_id) . "'
        AND ballot_active = '1'
        ORDER BY ballot_start DESC
    ");
    return $res;
}
?>
