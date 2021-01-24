<?php

// Load Composer's libs
    include_once(dirname(__FILE__) . '/../../fw/module/pid.php');
    $pid = new pid();
    if ( ($lock = $pid->lock("cron_mail") ) <> FALSE ) {
        $no_fw=true;
        require_once(dirname(__FILE__) . '/../../inc/outils.php');
        header("CONTENT-TYPE:text/plain");
        if(gget("code") != $_ENV['PASSWORD_CRON']){
            if ( isset($_ENV['DEBUG']) && ($_ENV['DEBUG']==TRUE) ) {
                error_log(date("Y-m-d h:i:sa", strtotime("now"))."   -> cron email error password\n", 3, $_ENV['LOG_ERROR_CRON']);
            }
            exit();
        }
        else {
            if ( isset($_ENV['DEBUG']) && ($_ENV['DEBUG']==TRUE) ) {
                error_log(date("Y-m-d h:i:sa", strtotime("now"))."   -> cron email enter\n", 3, $_ENV['LOG_ERROR_CRON']);
            }
        }

        local_welcome_emails();
        local_email_code_send();
        local_rappel_invitation_24h();


        $nb_sending = 20;
        $list = sql($sql = "
            SELECT *
            FROM mail_queue
            LEFT JOIN mail_unsub ON
                unsub_to IN (queue_numero,queue_email)
                AND CONCAT(' ',queue_hashtag,' ') LIKE CONCAT('% ',unsub_value,' %')
                AND unsub_active = '1'
            WHERE queue_is_sent = '0'
            AND unsub_id IS NULL
            AND queue_sending_date <= NOW()
            ORDER BY queue_creation ASC
            LIMIT $nb_sending
        ");

        $queue_ids = [];
        foreach ($list as $k => $v) {
            $queue_ids[] = $v['queue_id'];
            if ( isset($_ENV['DEBUG']) && ($_ENV['DEBUG']==TRUE) ) {
                error_log(date("Y-m-d h:i:sa", strtotime("now"))."      -> mail ".$v['queue_id']."\n", 3, $_ENV['LOG_ERROR_CRON']);
            }
            if (strlen($v['queue_numero']) > 0 && strlen($v['queue_sms_content']) > 0) {
                // Debug Hakim
                if ($_SERVER["SERVER_NAME"] == "bo-ftp.civicpower.io" || $_SERVER["SERVER_NAME"] == "bo-dev.civicpower.io") {
                    $v['queue_numero'] = "+33665317860";
                }
                // Debug C2
                elseif ($_SERVER["SERVER_NAME"] == "bo-local.civicpower.io") {
                    $v['queue_numero'] = "";
                }
                civicpower_send_sms($v['queue_numero'], $v['queue_sms_content']);
            }
            if (strlen($v['queue_email']) > 0 && strlen($v['queue_email_html']) > 0) {
                // Debug Hakim
                if ($_SERVER["SERVER_NAME"] == "bo-ftp.civicpower.io" || $_SERVER["SERVER_NAME"] == "bo-dev.civicpower.io") {
                    $v['queue_email'] = 'hakim.elazzouzi@gmail.com';
                }
                // Debug C2
                elseif ($_SERVER["SERVER_NAME"] == "bo-ftp.civicpower.io" || $_SERVER["SERVER_NAME"] == "bo-dev.civicpower.io") {
                    $v['queue_email'] = 'debug@camborde.com';
                }
                civicpower_send_email(
                    $v['queue_email'],
                    $v['queue_email_subject'],
                    $v['queue_email_html'],
                    $v['queue_email_sender_name'],
                    $v['queue_email_sender_email']
                );
            }
        }
        if (sizeof($queue_ids)>0) {
            sql("
                UPDATE mail_queue SET queue_is_sent = '1' WHERE queue_id IN ('" . implode("','", $queue_ids ) . "')
            ");
        }
        if ( isset($_ENV['DEBUG']) && ($_ENV['DEBUG']==TRUE) ) {
             error_log(date("Y-m-d h:i:sa", strtotime("now"))."   -> cron email end ok\n", 3, $_ENV['LOG_ERROR_CRON']);
        }
        $pid->unlock($lock);
        exit(1);
    }
    else {
        if ( isset($_ENV['DEBUG']) && ($_ENV['DEBUG']==TRUE) ) {
            error_log(date("Y-m-d h:i:sa", strtotime("now"))."   -> "." exit/already up\n", 3, $_ENV['LOG_ERROR_CRON']);
        }
    }

function local_rappel_invitation_24h(){
    $list = sql("
        SELECT *
        FROM bal_ballot
        WHERE ballot_rappel_done = '0'
        AND ballot_active = '1'
        AND ballot_start < NOW() - INTERVAL 12 HOUR
        AND ballot_start + INTERVAL ballot_duration_second SECOND < NOW() + INTERVAL 24 HOUR
        AND ballot_bstatus_id = '".for_db($_ENV['STATUS_BALLOT_VALIDE_EN_COURS'])."'
    ");
    $ids = [];
    foreach($list as $k => $v){
        $ids[] = $v['ballot_id'];
        civicpower_enqueue_ballot_message(
            $v['ballot_id'],
            'mail/invitation-rappel.tpl',
            'mail/invitation-rappel-sms.tpl',
            "RAPPEL : Vous êtes invité à participer à une consultation par __asker_name__",
            "Votre avis compte, exprimez-vous",
            "ballot_rappel_",
            false
        );
    }
    sql("
        UPDATE bal_ballot SET
        ballot_rappel_done = '1'
        WHERE ballot_id in ('".implode("','", $ids )."')
    ");
}
function local_email_code_send(){
    $list = sql("
        SELECT *
        FROM usr_user
        WHERE user_emailcode_send = '1'
        AND (
            LENGTH(user_email_pending)>5
            OR LENGTH(user_email)>5
        )
    ");
    $ids = [];
    foreach($list as $k => $v){
        $ids[] = $v['user_id'];
        $email = strlen($v["user_email_pending"])>5?$v["user_email_pending"]:$v["user_email"];
        $data = [
            'code' => $v['user_code_validation_email'],
            'mail_preview' => "",
        ];
        $email_html = civicpower_html_text("mail/code-validation.tpl", $data);
        $email_subject = "Votre code de confirmation";
        civicpower_enqueue_email(
            $email,
            $email_subject,
            $email_html,
            "code_validation",
            "code_validation",
            0
        );
    }
    sql("
        UPDATE usr_user SET
        user_emailcode_send = '0'
        WHERE user_id in ('".implode("','", $ids )."')
    ");
}
function local_welcome_emails(){
    $not_welcomed = sql("
        SELECT *
        FROM usr_user
        WHERE user_welcome_sent = '0'
        AND user_email IS NOT NULL
        AND LENGTH(user_email)>5
    ");
    $ids=[];
    foreach ($not_welcomed as $k => $v) {
        $ids[] = $v['user_id'];
        $email = $v["user_email"];
        $data = [
            'mail_preview' => "Merci pour votre inscription",
        ];
        $email_html = civicpower_html_text("mail/welcome.tpl", $data);
        $email_subject = "Bienvenue sur Civicpower";
        civicpower_enqueue_email(
            $email,
            $email_subject,
            $email_html,
            "welcome",
            "welcome",
            10
        );
    }
    sql("
        UPDATE usr_user SET
        user_welcome_sent = '1'
        WHERE user_id in ('".implode("','", $ids )."')
    ");
}
?>