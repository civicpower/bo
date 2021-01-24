<?php
define("SEP_INNER"," __SEP__INNER__ ");
define("SEP_OUTER"," __SEP__OUTER__ ");
$user = must_be_connected();
must_be_admin();
$fw->smarty->assign('menu_actif',"admin-user");
$fw->include_css('admin-user-list');
project_css_js($fw);
$fw->include_js('admin-user-list');
$fw->set_canonical('/admin-user-list');
inject_data($fw);
$fw->smarty->display('admin-user-list.tpl');
$fw->go();


function inject_data(&$fw) {
    $mpp = 50;
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
                    AND ".local_search_fieldlist()." LIKE '%".for_db(trim($v))."%'
                ";
            }
        }
    }
    $list = sql($sql = "
        SELECT
            usr_user.*,
            geo_fr_cities.code_postal,
            geo_fr_cities.nom_commune,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    asker_id,'".SEP_INNER."',asker_name
                ) SEPARATOR '".SEP_OUTER."'
            ) AS asker_list,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    ballot_id,'".SEP_INNER."',ballot_title,'".SEP_INNER."',asker_name
                ) SEPARATOR '".SEP_OUTER."') AS ballot_list
        FROM usr_user
        LEFT JOIN ask_asker    ON asker_user_id = user_id AND asker_active = '1'
        LEFT JOIN bal_ballot   ON ballot_asker_id = asker_id AND ballot_active = '1'
        LEFT JOIN geo_fr_cities ON city_id = user_city_id
        WHERE 1=1
        AND user_active = '1' AND user_ban = '0'
        GROUP BY user_id
        HAVING 1=1
        ".implode(" ",$where)."
        ORDER BY user_creation DESC
        LIMIT $pagestart,$mpp
    ");
    foreach($list as $k => &$v){
        $ballot_list = $v['ballot_list'];
        $ballot_list = explode(SEP_OUTER,$ballot_list);
        $res=[];
        foreach($ballot_list as $kk => &$vv){
            $vv = explode(SEP_INNER,$vv);
            if(is_array($vv) && count($vv)==3){
                $res[] = [
                    'ballot_id'=>$vv[0],
                    'ballot_title'=>$vv[1],
                    'asker_name'=>$vv[2],
                ];
            }
        }
        $v['ballot_list'] = $res;
        $asker_list = $v['asker_list'];
        $asker_list = explode(SEP_OUTER,$asker_list);
        $res=[];
        foreach($asker_list as $kk => &$vv){
            $vv = explode(SEP_INNER,$vv);
            if(is_array($vv) && count($vv)==2){
                $res[] = [
                    'asker_id'=>$vv[0],
                    'asker_name'=>$vv[1],
                ];
            }
        }
        $v['asker_list'] = $res;
    }
    $fw->smarty->assign('user_list', $list);
}
function local_search_fieldlist(){
    static $res=null;
    if(!is_null($res)){
        return $res;
    }
    $fieldlist = [
        'user_firstname',
        'user_lastname',
        'user_phone_national',
        'user_phone_dial',
        'user_phone_international',
        'user_phone_national',
        'user_email',
        'nom_commune',
        'code_postal',
        'asker_list',
        'ballot_list',
    ];
    $str=[];
    $str[] = 'CONCAT(\' \',';
    foreach($fieldlist as $k => $v){
        $str[] = 'COALESCE('.$v.',\'\'),\' \',';
    }
    $str[] = '\' \')';
    $res = implode(BN,$str);
    return $res;
}
?>