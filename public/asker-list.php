<?php
$user = must_be_connected();
local_action($user);
project_css_js($fw);
$fw->smarty->assign('menu_actif',"asker");
$fw->include_css('asker-list');
$fw->include_js('asker-list');
$fw->set_canonical('/asker-list');
$fw->smarty->assign('asker_list',$list = sql("
    SELECT ask_asker.*,
    COUNT(DISTINCT ballot_id) AS nb_ballot
    FROM ask_asker
    LEFT JOIN bal_ballot ON ballot_asker_id = asker_id AND ballot_active = '1'
    WHERE asker_user_id = '".for_db($user['user_id'])."'
    AND asker_active = '1'
    GROUP BY asker_id
    ORDER BY asker_name ASC
"));
$fw->smarty->display('asker-list.tpl');
$fw->go();

function local_action($user){
    if(gpost("action")=="delete_asker"){
        $delete_id = null;
        $res = [
            "status" => "success",
            "message" => []
        ];
        if(!post_exists("asker_id")){
            $res["message"][] = "Impossible d'identifier le profil organisateur";
        }else{
            $asker_id = gpost("asker_id");
            $delete_id = $asker_id;
            if(!is_numeric($asker_id) || $asker_id<=0){
                $res["message"][] = "Impossible d'identifier le profil organisateur";
            }else{
                $asker_nb = intval(sql_unique("
                    SELECT COUNT(*) AS nb
                    FROM ask_asker
                    WHERE asker_id = '".for_db($asker_id)."'
                    AND asker_user_id = '".for_db($user["user_id"])."'
                    AND asker_active = '1'
                "));
                if($asker_nb<=0){
                    $res["message"][] = "Opération impossible";
                }else if(get_nb_active_ballot_asker($asker_id)>0){
                    $res["message"][] = "Vous avez des consultations actives. Veuillez attendre qu'elles se terminent pour pouvoir supprimer cet organisateur";
                }
            }
        }
        if(count($res["message"])==0){
            if(!is_null($delete_id) && is_numeric($delete_id) && $delete_id>0) {
                sql("
                    UPDATE ask_asker SET
                    asker_active = '0'
                    WHERE asker_id = '".for_db($asker_id)."'
                ");
            }
        }else{
            $res["status"] = "error";
        }
        echo json_encode($res);
        exit();
    }
}
?>