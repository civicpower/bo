<?php
$user = must_be_connected();
local_action($user);
project_css_js($fw);
$fw->smarty->assign('menu_actif',"asker");
$fw->smarty->assign('mode_update',get_exists("asker_id"));
$fw->smarty->assign('asker',$asker = sql_shift($sql = "
    SELECT *
    FROM ask_asker
    WHERE asker_user_id = '".for_db($user['user_id'])."'
    AND asker_active = '1'
    AND asker_id = '".for_db(gget("asker_id"))."'
"));
if(isset($asker['asker_id'])) {
    $fw->smarty->assign('nb_active_ballots', get_nb_active_ballot_asker($asker['asker_id']));
}
$fw->include_css('asker');
$fw->include_js('asker');
$fw->set_canonical('/asker');
$fw->smarty->display('asker.tpl');
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
function local_action_save_asker($user) {
    $ajax_return = ajax_return_object();
    $mode = "insert";
    $asker_id = '';
    if(post_exists("asker_id") && is_numeric(gpost("asker_id")) && gpost("asker_id")>0){
        $mode = "update";
        $asker_id = gpost("asker_id");
    }
    if (!post_exists("asker_name")) {
        $ajax_return["message"][] = "Le libellé du profil est obligatoire";
    }
    if (count($ajax_return['message']) > 0) {
        $ajax_return["status"] = "error";
    } else {
        if($mode=="update") {
            if(get_nb_active_ballot_asker($asker_id)>0){
            $ajax_return["status"] = "error";
                $ajax_return["message"][] = "Vous avez des consultations actives. Veuillez attendre qu'elles se terminent pour pouvoir modifier vos informations";
            }else {
                sql($sql = "
                    UPDATE ask_asker SET
                        asker_name = '" . for_db(gpost("asker_name")) . "'
                    WHERE asker_id = '" . for_db($asker_id) . "'
                    AND asker_user_id = '" . for_db($user['user_id']) . "'
                ");
            }
        }else{
            $asker_id = sql($sql = "
                INSERT INTO ask_asker SET
                asker_name = '" . for_db(gpost("asker_name")) . "',
                asker_user_id = '".for_db($user['user_id'])."'
            ");
            $ajax_return["data"] = $asker_id;
        }
    }
    echo json_encode($ajax_return);
}
function local_action_change_photo($user) {
    if (files_exists("file") && post_exists("asker_id")) {
        $asker_id = gpost("asker_id");
        if(!is_numeric($asker_id) || $asker_id<=0 || intval(sql_unique("
            SELECT COUNT(*) AS nb
            FROM ask_asker
            WHERE asker_user_id = '".for_db($user['user_id'])."'
            AND asker_id = '".for_db($asker_id)."'
        "))==0){
            ajax_error("Opération non autorisée", "operation_not_permitted");
        }
        if(get_nb_active_ballot_asker($asker_id)>0){
            ajax_error("Vous avez des consultations actives. Veuillez attendre qu'elles se terminent pour pouvoir modifier vos informations", "operation_not_permitted");
        }
        $file = gfile("file");
        if(isset($file['error']) && $file['error']>0){
            $err = $file['error'];
            if($err == 1 || $err == 2){
                ajax_error("Votre photo est trop volumineuse.\nMerci d'en choisir une plus petite", "file_not_uploaded_correctly");
            }else if($err == 3){
                ajax_error("Votre photo ne s'est pas totalement téléchargée.\nMerci de recommencer", "file_not_uploaded_correctly");
            }else if($err == 4){
                ajax_error("Aucune photo ne s'est chargée.\nMerci de recommencer.", "file_not_uploaded_correctly");
            }else if($err == 6){
                ajax_error("Dossier temporaire manquant", "file_not_uploaded_correctly");
            }else if($err == 7){
                ajax_error("Échec de l'écriture du fichier sur le disque", "file_not_uploaded_correctly");
            }else if($err == 8){
                ajax_error("Une extension PHP a arrêté l'envoi de fichier.\nPHP ne propose aucun moyen de déterminer quelle extension est en cause.", "file_not_uploaded_correctly");
            }else{
                ajax_error("Impossible de charger votre photo.\nErreur inconnue", "file_not_uploaded_correctly");
            }
        }
        if (!isset($file["tmp_name"])) {
            ajax_error("Votre photo ne s'est pas chargée correctement (".__LINE__.")", "file_not_uploaded_correctly");
        }
        $tmp_name = $file["tmp_name"];
        if (!file_exists($tmp_name)) {
            ajax_error("Votre photo ne s'est pas chargée correctement (".__LINE__.") (".print_r($file,true).")", "file_not_uploaded_correctly");
        }
        $type_mime = civicpower_type_mime($tmp_name);
        $allowed = ["image/jpeg", "image/gif", "image/png",];
        if (!in_array($type_mime, $allowed)) {
            ajax_error("Format de fichier non autorisé", "file_format_not_allowed");
        }
        $hash = civicpower_hash_db(false, $asker_id, $_ENV['SALT_ASKER']);
        $relative = "/uploads/pp/" . $hash . ".png";
        $dest = dirname(__FILE__) . $relative;
        civicpower_resize_image($tmp_name, $dest, 250, 250);
        ajax_success("Photo téléchargée avec succès","",$relative);
    } else {
        ajax_error("Votre photo ne s'est pas chargée correctement (".__LINE__.")", "file_not_uploaded_correctly");
    }
}
