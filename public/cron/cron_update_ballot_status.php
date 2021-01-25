<?php
$no_fw=true;
require_once(dirname(__FILE__) . '/../../inc/outils.php');

if(gget("code") != $_ENV['PASSWORD_CRON']){
    exit();
}

local_update_ballot($_ENV['STATUS_BALLOT_VALIDE_EN_COURS'],"
    NOW() < ballot_start + INTERVAL ballot_duration_second SECOND
    AND ballot_start < NOW()
");
local_update_ballot($_ENV['STATUS_BALLOT_VALIDE_TERMINE'],"
    NOW() > ballot_start + INTERVAL ballot_duration_second SECOND
");

function local_update_ballot($status_id,$where) {
    $list = sql($sql = "
        SELECT *
        FROM bal_ballot
        WHERE ballot_bstatus_id>='".for_db($_ENV['STATUS_BALLOT_VALIDE_EN_ATTENTE'])."'
        AND $where
        AND ballot_bstatus_id <> '" . for_db($status_id) . "'
    ");
    printr2($sql);
    foreach ($list as $k => $v) {
        civicpower_update_ballot_status($v['ballot_id'], $status_id);
    }
}
?>