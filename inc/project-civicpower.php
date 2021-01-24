<?php
function civicpower_load_salt(){
    if ( isset($_ENV['EGREGORE']) && ($_ENV['EGREGORE']==TRUE) ) {
        if ( ( $f=fopen($_ENV['EGREGORE'],'r') ) <> FALSE ) {
            while (($line = fgets($f)) !== false) {
                $tmp = explode("=", $line);
                $val = $tmp[1];
                $val = trim($val);
                $val = preg_replace("~^\"~","",$val);
                $val = preg_replace("~\"$~","",$val);
                $_ENV[ $tmp[0] ] = $val;
            }
        }
    }
}
function must_be_disconnected() {
    if (bo_is_connected()) {
        rediriger_vers('/');
        exit();
    }
}
function bo_is_connected() {
    $res = false;
    if (session_exists('user')) {
        $login = gsession('user');
        if (isset($login['user_id']) && is_numeric($login['user_id']) && $login['user_id'] > 0) {
            $res = true;
        }
    }
    if (!$res) {
        if (cookie_exists($_ENV['LOGIN_COOKIE_NAME'])) {
            $user_token = gcookie($_ENV['LOGIN_COOKIE_NAME']);
            if (is_string($user_token) && strlen($user_token) > 0) {
                $user = sql_shift($sql = "
					SELECT *
					FROM usr_user
					WHERE " . civicpower_hash_db(true, "user_salt", $_ENV['SALT_USER']) . " = '" . for_db($user_token) . "'
					AND user_active = '1' AND user_ban = '0'
				");
                if (isset($user) && is_array($user) && count($user) > 0 && isset($user['user_id']) && is_numeric($user['user_id']) && $user['user_id'] > 0) {
                    $_SESSION['user'] = $user;
                    $res = true;
                }
            }
        }
    }
    return $res;
}
function cp_asker_token($asker_id = null) {
    $res = "";
    if (bo_is_connected()) {
        $res = civicpower_hash_db(false, $asker_id, $_ENV['SALT_ASKER']);
    }
    return $res;
}
function must_be_connected() {
    if (!bo_is_connected()) {
        rediriger_vers('/login');
        exit();
    }
    $user = cp_session_user();
    if (strlen(trim($user['user_password'])) <= 0) {
        rediriger_vers("/alert-password");
    }
    return $user;
}
function must_be_admin() {
    if (!cp_user_is_admin()) {
        rediriger_vers('/');
        exit();
    }
}
function cp_user_is_admin() {
    $res = false;
    if (bo_is_connected()) {
        $res = $_SESSION["user"]["user_is_admin"] == 1;
    }
    return $res;
}
function cp_db_user() {
    $user = $_SESSION["user"];
    $user_id = $user["user_id"];
    $user = sql_shift("
        SELECT *
        FROM usr_user
        WHERE user_id = '" . for_db($user_id) . "'
        AND user_active = '1' AND user_ban = '0'
    ");
    $_SESSION["user"] = $user;
    return $user;
}
function cp_session_user() {
    return cp_db_user();
}
function cp_user_name() {
    return $_SESSION["user"]["user_firstname"] . " " . $_SESSION["user"]["user_lastname"];
}
function ajax_return_object() {
    return ["status" => "success", "code" => "success", "message" => [], "data" => []];
}
function ajax_error($message, $code = "", $data = [], $error_http_code = "405 Not Allowed") {
    $ajax_return = ajax_return_object();
    $ajax_return["status"] = "error";
    $ajax_return["code"] = $code;
    $ajax_return["message"] = $message;
    $ajax_return["data"] = $data;
    header('HTTP/1.0 ' . $error_http_code);
    echo json_encode($ajax_return);
    exit();
}
function ajax_success($message, $code = "", $data = []) {
    $ajax_return = ajax_return_object();
    $ajax_return["code"] = $code;
    $ajax_return["message"] = $message;
    $ajax_return["data"] = $data;
    echo json_encode($ajax_return);
    exit();
}
function ajax_assert_exists($field, $check_numeric = false, $check_positive = false, $mode = "post") {
    $tab = $_POST;
    if (strtolower(trim($mode)) == "get") {
        $tab = $_GET;
    }
    if (!isset($tab[$field])) {
        ajax_error("mandatory parameter [" . $field . "]");
        return null;
    } else {
        if ($check_numeric && !is_numeric($tab[$field])) {
            ajax_error("parameter [" . $field . "] non numeric");
            if ($check_positive && $tab[$field] <= 0) {
                ajax_error("parameter [" . $field . "] non positive");
            }
        }
        return $tab[$field];
    }
}
function cp_is_editable($table, $id, $user) {
    if ($user['user_is_admin']) {
        return true;
    }
    $res = false;
    if ($table == "bal_ballot") {
        $status_id = intval(sql_unique("
            SELECT ballot_bstatus_id
            FROM bal_ballot
            WHERE ballot_id = '" . for_db($id) . "'
        "));
        $res = in_array($status_id, cp_editable_status_list());
    } else if ($table == "bal_question") {
        $ballot_id = intval(sql_unique("
            SELECT question_ballot_id
            FROM bal_question
            WHERE question_id = '" . for_db($id) . "'
        "));
        $res = cp_is_editable("bal_ballot", $ballot_id, $user);
    } else if ($table == "bal_option") {
        $question_id = intval(sql_unique("
            SELECT option_question_id
            FROM bal_option
            WHERE option_id = '" . for_db($id) . "'
        "));
        $res = cp_is_editable("bal_question", $question_id, $user);
    }
    return $res;
}
function ajax_assert_publishable_ballot($ballot_id) {
    $ballot = sql_shift("
        SELECT *
        FROM bal_ballot
        WHERE ballot_id = '" . for_db($ballot_id) . "'
        AND ballot_active = '1'
    ");
    $questions = sql("
        SELECT *
        FROM bal_question
        WHERE question_ballot_id = '" . for_db($ballot_id) . "'
        AND question_active = '1'
    ");
    $question_title_filled = true;
    $option_title_filled = true;
    foreach ($questions as $k => $v) {
        if (strlen(trim($v['question_title'])) <= 0) {
            $question_title_filled = false;
        }
        $options = sql("
            SELECT *
            FROM bal_option
            WHERE option_question_id = '" . for_db($v["question_id"]) . "'
            AND option_active = '1'
            ORDER BY option_rank ASC
        ");
        if (count($options) <= 2) {
            ajax_error("Chaque question doit avoir au minimum 2 réponses possibles.", "too_few_options", $questions);
        }
        $nspp = false;
        foreach ($options as $k => $v) {
            if ($v["option_can_be_deleted"] == '1') {
                $nspp = true;
            }
            if (strlen(trim($v['option_title'])) <= 0) {
                $option_title_filled = false;
            }
        }
        if ($nspp === false) {
            ajax_error("Chaque question doit avoir une réponse 'ne se prononce pas'", "nspp_option_missing");
        }
        $questions[$k]["options"] = $options;
    }
    if (!$option_title_filled) {
        ajax_error("Certaines réponses ne contiennent pas de libellé", "option_without_title");
    }
    if (!$question_title_filled) {
        ajax_error("Chaque question doit avoir un libellé", "question_without_title");
    }
}
function ajax_assert_option_not_duplicate($option_id, $value) {
    $question_id = intval(sql_unique("
        SELECT option_question_id
        FROM bal_option
        WHERE option_id = '" . for_db($option_id) . "'
    "));
    //    exit("".$question_id);
    if (is_numeric($question_id) && $question_id > 0) {
        $nb = intval(sql_unique("
            SELECT COUNT(*) AS nb
            FROM bal_option
            WHERE option_question_id = '" . for_db($question_id) . "'
            AND TRIM(option_title) LIKE '" . for_db(trim($value)) . "'
        "));
        if ($nb > 0) {
            ajax_error("Cette réponse existe déja pour cette question", "option_already_exists");
        }
    } else {
        ajax_error("Question introuvable", "question_not_found");
    }
}
function ajax_assert_option_editable($id, $user) {
    ajax_assert_editable("bal_option", $id, $user);
    $can_be_deleted = intval(sql_unique("
        SELECT option_can_be_deleted
        FROM bal_option
        WHERE option_id = '" . for_db($id) . "'
    "));
    if ($can_be_deleted != '1') {
        ajax_error("Cette option ne peut pas être éditée", "not_editable");
    }
}
function ajax_assert_editable($table, $id, $user) {
    if (!cp_is_editable($table, $id, $user)) {
        ajax_error("Object is not editable", "not_editable");
    }
}
function ajax_assert_ballot_is_waiting_validation($ballot_id) {
    $bstatus_id = intval(sql_unique("
        SELECT ballot_bstatus_id
        FROM bal_ballot
        WHERE ballot_id = '" . for_db($ballot_id) . "'
    "));
    if(false) {
        if (!in_array($bstatus_id, [$_ENV['STATUS_BALLOT_EN_ATTENTE_DE_VALIDATION'], $_ENV['STATUS_BALLOT_REFUSE']])) {
            ajax_error("Cette consultation n'est pas en attente de validation", "ballot_not_waiting_validation");
        }
    }
}
function ajax_assert_user_is_admin($user) {
    if (!$user['user_is_admin']) {
        ajax_error("Seuls les administrateurs sont autorisés", "user_not_admin");
    }
}
function ajax_assert_user_can_manage($table, $id, $user) {
    if (!check_user_can_manage($table, $id, $user)) {
        ajax_error("Operation not permited", "operation_not_permited");
    }
}
function get_nb_active_ballot_asker($asker_id) {
    return sql_unique("
        SELECT COUNT(*) AS nb
        FROM bal_ballot
        WHERE ballot_asker_id = '".for_db($asker_id)."'
        AND ballot_bstatus_id IN ('".implode("','", [
            $_ENV['STATUS_BALLOT_VALIDE_EN_ATTENTE'],
            $_ENV['STATUS_BALLOT_VALIDE_EN_COURS'],
            $_ENV['STATUS_BALLOT_EN_ATTENTE_DE_VALIDATION'],
        ] )."')
        AND ballot_active = '1'
    ");
}
function get_nb_active_ballot($user_id) {
    $nb = intval(sql_unique($sql = "
        SELECT COUNT(*) AS nb
        FROM bal_ballot
        INNER JOIN ask_asker ON asker_id = ballot_asker_id AND asker_active = '1'
        WHERE ballot_bstatus_id NOT IN (
            '" . for_db($_ENV['STATUS_BALLOT_VALIDE_TERMINE']) . "',
            '" . for_db($_ENV['STATUS_BALLOT_REFUSE']) . "',
            '" . for_db($_ENV['STATUS_BALLOT_EN_COURS_DE_CREATION']) . "'
        )
        AND asker_user_id = '" . for_db($user_id) . "'
        AND ballot_active = '1'
    "));
    return $nb;
}
function check_user_can_manage($table, $id, $user) {
    $res = false;
    if ($user['user_is_admin'] == 1) {
        return true;
    }
    if (is_numeric($id) && $id >= 0) {
        if ($table == "bal_question") {
            $nb = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM bal_question
                INNER JOIN bal_ballot ON ballot_id = question_ballot_id AND ballot_active = '1'
                INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
                WHERE question_id = '" . for_db($id) . "'
                AND asker_user_id = '" . for_db($user["user_id"]) . "'
                AND question_active = '1'
            "));
            if ($nb > 0) {
                $res = true;
            }
        } else if ($table == "bal_ballot") {
            $nb = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM bal_ballot
                INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
                WHERE ballot_id = '" . for_db($id) . "'
                AND asker_user_id = '" . for_db($user["user_id"]) . "'
                AND ballot_active = '1'
            "));
            if ($nb > 0) {
                $res = true;
            }
        } else if ($table == "bal_option") {
            $nb = intval(sql_unique("
                SELECT COUNT(*) AS nb
                FROM bal_option
                INNER JOIN bal_question ON question_id = option_question_id AND question_active = '1'
                INNER JOIN bal_ballot ON ballot_id = question_ballot_id AND ballot_active = '1'
                INNER JOIN ask_asker ON ballot_asker_id = asker_id AND asker_active = '1'
                WHERE option_id = '" . for_db($id) . "'
                AND asker_user_id = '" . for_db($user["user_id"]) . "'
                AND option_active = '1'
            "));
            if ($nb > 0) {
                $res = true;
            }
        }
    }
    return $res;
}
function cp_delete_ballot($ballot_id) {
    $ballot_id = intval($ballot_id);
    $questions = sql("
        SELECT question_id
        FROM bal_question
        WHERE question_ballot_id = '" . for_db($ballot_id) . "'
        AND question_active = '1'
    ");
    foreach ($questions as $k => $v) {
        cp_delete_question($v["question_id"]);
    }
    sql("
        UPDATE bal_ballot
        SET ballot_active = '0'
        WHERE ballot_id = '" . for_db($ballot_id) . "'
    ");
}
function cp_delete_question($question_id) {
    $question_id = intval($question_id);
    $options = sql("
        SELECT option_id
        FROM bal_option
        WHERE option_question_id = '" . for_db($question_id) . "'
        AND option_active = '1'
    ");
    foreach ($options as $k => $v) {
        cp_delete_option($v['option_id']);
    }
    sql("
        UPDATE bal_question
        SET question_active = '0'
        WHERE question_id = '" . for_db($question_id) . "'
    ");
}
function cp_delete_option($option_id) {
    $option_id = intval($option_id);
    sql("
        UPDATE vot_vote
        SET vote_active = '0'
        WHERE vote_option_id = '" . for_db($option_id) . "'
    ");
    sql("
        UPDATE bal_option
        SET option_active = '0'
        WHERE option_id = '" . for_db($option_id) . "'
    ");
}
function cp_ballot_status($ballot) {
    $res = "";
    if (is_array($ballot) && count($ballot) > 0) {
        $status_id = $ballot["ballot_bstatus_id"];
        if ($status_id == $_ENV['STATUS_BALLOT_EN_COURS_DE_CREATION']) {
            $res = "En cours de création";
        } else if ($status_id == $_ENV['STATUS_BALLOT_EN_ATTENTE_DE_VALIDATION']) {
            $res = "En attente de validation";
        } else if ($status_id == $_ENV['STATUS_BALLOT_REFUSE']) {
            $res = "Consultation refusée";
        } else if ($status_id >= $_ENV['STATUS_BALLOT_VALIDE_EN_ATTENTE']) {
            if ($ballot["ballot_running"] == 1) {
                $res = "Votes en cours";
            } else if ($ballot["ballot_finished"] == 1) {
                $res = "Terminé";
            } else if ($ballot["ballot_started"] == 1) {
                $res = "Consultation prête";
            } else {
                $res = "Consultation prête";
            }
        }
    }
    return $res;
}
function cp_editable_status_list() {
    return [$_ENV['STATUS_BALLOT_EN_COURS_DE_CREATION']];
}
function civicpower_hash_db($sql_language, $string, $salt = "") {
    if ($sql_language) {
        return "SHA1(CONCAT($string,'" . for_db($_ENV['GLOBAL_SALT']) . "','" . for_db($salt) . "'))";
    } else {
        return sha1("" . $string . $_ENV['GLOBAL_SALT'] . $salt);
    }
}
function cp_new_ballot_shortcode() {
    $length = 6;
    $possible = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $possible_len = strlen($possible);
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $possible[rand(0, $possible_len - 1)];
    }
    $nb_already = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM bal_ballot
        WHERE ballot_shortcode LIKE '" . for_db($res) . "'
    "));
    if ($nb_already > 0) {
        $res = cp_new_ballot_shortcode();
    }
    return $res;
}
function civicpower_is_email($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL);
}
function civicpower_is_french_phone($str) {
    return preg_match("~^(?:(?:\+|00)33|0)[1-9](?:\d{2}){4}$~", $str);
}
function civicpower_international_phone($str) {
    $res = $str;
    if (preg_match("~^00~", $str)) {
        $res = preg_replace("~^00~", "+", $str);
    } else if (preg_match("~^0~", $str)) {
        $res = preg_replace("~^0~", "+33", $str);
    }
    return $res;
}
function civicpower_type_mime($file) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $res = finfo_file($finfo, $file);
    finfo_close($finfo);
    return $res;
}
function civicpower_resize_image($imagePath = '', $newPath = '', $newWidth = 0, $newHeight = 0, $outExt = 'DEFAULT') {
    if (!$newPath or !file_exists($imagePath)) {
        return null;
    }
    $types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_BMP, IMAGETYPE_WEBP];
    $type = exif_imagetype($imagePath);
    if (!in_array($type, $types)) {
        return null;
    }
    list ($width, $height) = getimagesize($imagePath);
    $outBool = in_array($outExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            if (!$outBool) $outExt = 'jpg';
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            if (!$outBool) $outExt = 'png';
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($imagePath);
            if (!$outBool) $outExt = 'gif';
            break;
        case IMAGETYPE_BMP:
            $image = imagecreatefrombmp($imagePath);
            if (!$outBool) $outExt = 'bmp';
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($imagePath);
            if (!$outBool) $outExt = 'webp';
    }
    $w = $width;
    $h = $height;
    while ($w > $newWidth && $h > $newHeight) {
        $w = 0.9999 * $w;
        $h = 0.9999 * $h;
    }
    $max = max($newWidth, $newHeight);
    $newImage = imagecreatetruecolor($max, $max);
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    $color = imagecolorallocatealpha($newImage, 255, 255, 255, 255);
    imagefill($newImage, 0, 0, $color);
    imagesavealpha($newImage, true);
    imagecopyresampled($newImage, $image, 0 - (-1 * $newWidth + $w) / 2, 0 - (-1 * $newHeight + $h) / 2, 0, 0, $w + 1, $h + 1, $width, $height);
    try {
        if (false && function_exists('exif_read_data') && $exif = @exif_read_data($imagePath, 'IFD0')) {
            if (isset($exif['Orientation']) && isset($exif['Make']) && !empty($exif['Orientation']) && preg_match('/(apple|ios|iphone)/i', $exif['Make'])) {
                switch ($exif['Orientation']) {
                    case 8:
                        if ($width > $height) $newImage = imagerotate($newImage, 90, 0);
                        break;
                    case 3:
                        $newImage = imagerotate($newImage, 180, 0);
                        break;
                    case 6:
                        $newImage = imagerotate($newImage, -90, 0);
                        break;
                }
            }
        }
    } catch (Exception $ex) {
    }
    $outExt = "png";
    switch (true) {
        case in_array($outExt, ['jpg', 'jpeg']):
            $success = imagejpeg($newImage, $newPath);
            break;
        case $outExt === 'png':
            $success = imagepng($newImage, $newPath);
            break;
        case $outExt === 'gif':
            $success = imagegif($newImage, $newPath);
            break;
        case  $outExt === 'bmp':
            $success = imagebmp($newImage, $newPath);
            break;
        case  $outExt === 'webp':
            $success = imagewebp($newImage, $newPath);
    }
    if (!$success) {
        return null;
    }
    return $newPath;
}
function civicpower_send_email($email, $subject, $html,$sender_name="Civicpower",$sender_email="contact@civicpower.io", $user_id="") {
    $curl = curl_init();
    $text = strip_tags(str_replace(array('<br>', '<br/>', '<br />'), "\r\n", $html));;
    $post = [
        "sender" => [
            "name" => $sender_name,
            "email" => $sender_email
        ],
        "to" => [
            ["email" => $email,]
        ],
        "subject" => $subject,
        "htmlContent" => $html,
        "textContent" => $text
    ];
    curl_setopt_array(
        $curl,
        [
            CURLOPT_URL             => "https://api.sendinblue.com/v3/smtp/email",
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "POST",
            CURLOPT_POSTFIELDS      => json_encode($post),
            CURLOPT_HTTPHEADER      => [
                "Accept: application/json",
                "Content-Type: application/json",
                "api-key: " . $_ENV['SIB_API_TOKEN'],
            ],
        ]
    );
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if (curl_getinfo($curl)["http_code"]<>"200") {
        debugMailer(
            array('variables'   => get_defined_vars()
                 ,'subject'     => "Can't reach curl call: ".curl_getinfo($curl)["http_code"]
                 ,'message'     => "Request: email to: ".$email
                    ."<BR>"."function: ".__FUNCTION__
                    ."<BR>"."from url: ".$GLOBALS['URL']
            ) 
        );
    }
    curl_close($curl);
    if ($err) {
        $res = ["status" => "error", "message" => $err,];
    } else {
        $res = ["status" => "success", "message" => $response,];
    }
    return $res;
}
function civicpower_send_sms($mobile_phone_number, $text, $sender = "Civicpower", $user_id="") {
    $curl = curl_init();
    $post = [];
    $post["type"] = "transactional";
    $post["sender"] = $sender;
    $post["recipient"] = $mobile_phone_number;
    $post["content"] = $text;
    curl_setopt_array($curl, [CURLOPT_URL => "https://api.sendinblue.com/v3/transactionalSMS/sms", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => json_encode($post), CURLOPT_HTTPHEADER => ["accept: application/json", "api-key: " . $_ENV['SIB_API_TOKEN'], "content-type: application/json"],]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if (curl_getinfo($curl)["http_code"]<>"200") {
        debugMailer(
            array('variables'   => get_defined_vars()
                 ,'subject'     => "Can't reach curl call: ".curl_getinfo($curl)["http_code"]
                 ,'message'     => "Request: sms to: ".$mobile_phone_number
                    ."<BR>"."function: ".__FUNCTION__
                    ."<BR>"."from url: ".$GLOBALS['URL']
            ) 
        );
    }
    curl_close($curl);
    $res = ["status" => "success", "message" => "",];
    if ($err) {
        $res = ["status" => "error", "message" => $err,];
    } else {
        $res = ["status" => "success", "message" => $response,];
    }
    return $res;
}
function civicpower_ballot_and_asker($ballot_id){
    return sql_shift("
        SELECT *
        FROM bal_ballot
        INNER JOIN ask_asker ON asker_id = ballot_asker_id AND ballot_active = '1'
        WHERE ballot_id = '" . for_db($ballot_id) . "'
        LIMIT 1
    ");
}
function civicpower_voters($ballot_id,$has_voted = null){
    if(!is_null($has_voted)){
        $where = "
            AND LOWER(bfilter_email) ".($has_voted?"IN":"NOT IN")." (
                SELECT LOWER(user_email)
                FROM usr_user
                INNER JOIN vot_vote ON vote_user_id = user_id AND vote_active = '1'
                INNER JOIN bal_option ON option_id = vote_option_id AND option_active = '1'
                INNER JOIN bal_question ON question_id = option_question_id AND question_active = '1'
                WHERE user_active = '1' AND user_ban = '0'
                AND LENGTH(user_email)>4
                AND question_ballot_id = '".for_db($ballot_id)."'
            )
        ";
    }
    return sql("
        SELECT *
        FROM bal_filter
        WHERE bfilter_ballot_id = '" . for_db($ballot_id) . "'
        AND (
            LENGTH(bfilter_email)>0
            OR LENGTH(bfilter_phone_international)>0
        )
        AND bfilter_active = '1'
    ");
}
function civicpower_get_smarty_text($tpl_name, $tpl_params, $header = 'mail/mail-header', $footer = 'mail/mail-footer', $append = 'mail/blank', $strip_br = true) {
    $fw2 = new htmlpage(false, false, dirname(__FILE__) . '/../html');
    $fw2->set_header_file($header . '.tpl');
    if ($header == 'blank') {
        $fw2->remove_search_header = true;
    }
    $fw2->set_footer_file($footer . '.tpl');
    if (is_array($tpl_params) && count($tpl_params) > 0) {
        foreach ($tpl_params as $k => $v) {
            $fw2->smarty->assign($k, $v);
        }
    }
    $fw2->set_append_file($append . '.tpl');
    $fw2->smarty->display($tpl_name);
    $message = $fw2->fetch();
    $message = preg_replace("~[\n\r]+~", "BACKSLASHENNE", $message);
    $message = preg_replace("~<script.*?</script>~", "", $message);
    $message = preg_replace("~[ \t]+~", " ", $message);
    $message = trim($message);
    $message = preg_replace("~BACKSLASHENNE~", "\n", $message);
    $message = preg_replace("~[\n\r]+~", "\n", $message);
    if ($strip_br) {
        $message = preg_replace("~[\n\r]+~", " ", $message);
    }
    $message = preg_replace("~^.*?\<\!\-\-body\-\-\>~", "", $message);
    $message = preg_replace("~\<\!\-\-\/body\-\-\>.*?$~", "", $message);
    $message = preg_replace("~[\n\r]+~", "\n", $message);
    $message = preg_replace("~[ \t]+~", " ", $message);
    $message = trim($message);
    $message = preg_replace("~[\n\r]+~", "\n", $message);
    $message = trim($message);
    return $message;
}
function civicpower_enqueue_email(
    $to,
    $subject,
    $html,
    $unicite,
    $hashtag,
    $delai_minute,
    $sender_name="",
    $sender_email=""
) {
    if($sender_name="") { $sender_name=$_ENV['EMAIL_NAME']; }
    if($sender_email="") { $sender_email=$_ENV['EMAIL_EMAIL']; }
    sql("
        INSERT INTO mail_queue SET
            queue_email = '" . for_db($to) . "',
            queue_email_subject = '" . for_db($subject) . "',
            queue_email_html = '" . for_db($html) . "',
            queue_hashtag = '" . for_db($unicite) . " " . for_db($hashtag) . "',
            queue_unicite = '" . for_db($unicite) . "',
            queue_sending_date = NOW() + INTERVAL $delai_minute MINUTE
        ");
}
function civicpower_enqueue_sms($to, $text, $unicite, $hashtag, $delai_minute=0) {
    sql("
        INSERT INTO mail_queue SET
            queue_numero = '" . for_db($to) . "',
            queue_sms_content = '" . for_db($text) . "',
            queue_hashtag = '" . for_db($unicite) . " " . for_db($hashtag) . "',
            queue_unicite = '" . for_db($unicite) . "',
            queue_sending_date = NOW() + INTERVAL $delai_minute MINUTE
    ");
}
function cp_mail_shortcode($to, $link, $ballot_id) {
    $length = 9;
    $possible = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $possible_len = strlen($possible);
    $res = '';
    for ($i = 0; $i < $length; $i++) {
        $res .= $possible[rand(0, $possible_len - 1)];
    }
    $res = 'ML-' . $res;
    $nb_already = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM mail_link
        WHERE mlink_shortcode LIKE '" . for_db($res) . "'
    "));
    if ($nb_already > 0) {
        $res = cp_mail_shortcode($to, $link, $ballot_id);
    }
    sql("
        INSERT INTO mail_link SET
        mlink_shortcode = '" . for_db($res) . "',
        mlink_to = '" . for_db($to) . "',
        mlink_ballot_id = '" . for_db($ballot_id) . "',
        mlink_destination = '" . for_db($link) . "'
        
    ");
    return $res;
}
function snippet_iframe_modal() {
    return 'target="iframe-modal" data-toggle="modal" data-target="#modal-link"';
}
function civicpower_html_text($tpl,$data){
    return trim(nl2br(stripslashes(civicpower_get_smarty_text($tpl, $data))));
}
function civicpower_sms_text($tpl,$data){
    $sms_text = civicpower_get_smarty_text($tpl, $data, 'mail/blank', 'mail/blank');
    $sms_text = trim(html_entity_decode(strip_tags(str_replace(array('<br />', '<br>'), BN, $sms_text))));
    return $sms_text;
}
function civicpower_enqueue_ballot_message($ballot_id, $tpl_email, $tpl_sms, $email_subject, $email_preview, $prefix,$has_voted=null, $delai_minute=0) {
    $ballot = civicpower_ballot_and_asker($ballot_id);
    $voters = civicpower_voters($ballot_id,$has_voted);
    $already = civicpower_already_invited($ballot_id,$prefix);
    foreach ($voters as $k => $v) {
        $hashtag = [];
        $hashtag[] = "asker_" . $ballot["ballot_asker_id"];
        $hashtag = trim(implode(" ", $hashtag));
        $email = trim($v['bfilter_email']);
        $phone = trim($v['bfilter_phone_international']);
        $to = strlen($email) ? $email : $phone;
        $data = [
            'ballot' => $ballot,
            'to' => $to,
            'mail_preview' => $email_preview,
            'footer_include' => 'mail-footer-text-asker.tpl'
        ];
        if (strlen($email) > 0 && !in_array($email, $already)) {
            $email_html = civicpower_html_text($tpl_email,$data);
            $email_subject = str_replace("__ballot_title__",$ballot['ballot_title'],$email_subject);
            $email_subject = str_replace("__asker_name__",$ballot['asker_name'],$email_subject);
            civicpower_enqueue_email(
                $email,
                $email_subject,
                $email_html,
                $prefix . $ballot_id,
                $hashtag,
                $delai_minute,
                "Civicpower pour ".$ballot['asker_name']
            );
        }
        if (strlen($phone) > 0 && !in_array($phone, $already)) {
            if(!is_null($tpl_sms) && is_string($tpl_sms) && strlen($tpl_sms)>0) {
                $sms_text = civicpower_sms_text($tpl_sms, $data);
                civicpower_enqueue_sms($phone, $sms_text, $prefix . $ballot_id, $hashtag, $delai_minute);
            }
        }
    }
}
function civicpower_invoke_ballot_rejected($ballot_id) {
    $ballot = civicpower_ballot_and_asker($ballot_id);
    $user = sql_shift("
        SELECT usr_user.*
        FROM usr_user
        WHERE user_id = '".for_db($ballot['asker_user_id'])."'
    ");
    $unicite = "ballot_rejected_".$ballot_id;
    $hashtag = "REJECTION REJECTION_BALLOT_".$ballot_id;
    $data = [
        'ballot' => $ballot,
        'mail_preview' => $ballot['ballot_title'],
    ];
    if(strlen($user['user_email'])>2){
        civicpower_enqueue_email(
            $user['user_email'],
            "Rejet ou demande de modification de votre consultation",
            civicpower_html_text("mail/consultation-refusee.tpl",$data),
            $unicite,
            $hashtag,
            0
        );
    }else if(strlen($user['user_phone_international'])>2){
        $sms_text = civicpower_sms_text('mail/consultation-refusee-sms.tpl',$data);
        civicpower_enqueue_sms($user['user_phone_international'], $sms_text, $unicite, $hashtag);
    }
}
function civicpower_remove_options_aucun_if_not_qcm($ballot_id) {
    $qtab = sql("
        SELECT *
        FROM bal_question
        WHERE question_ballot_id = '".for_db($ballot_id)."'
        AND question_active = '1'
    ");
    $options_aucun = [];
    foreach($qtab as $k => $v){
        if($v['question_nb_vote_min']==1 &&  $v['question_nb_vote_max']==1){
            $options = sql("
                SELECT *
                FROM bal_option
                WHERE option_question_id = '".for_db($v['question_id'])."'
                AND (
                    (option_can_be_deleted = '0' AND option_title LIKE 'Aucun')
                )
                AND option_active = '1'
            ");
            foreach($options as $kk => $vv){
                $options_aucun[] = $vv['option_id'];
            }
        }
    }
    sql("
        UPDATE bal_option SET
        option_active = '0'
        WHERE option_id IN ('".implode("','", $options_aucun )."')
    ");
}
function civicpower_invoke_ballot_accepted($ballot_id) {
    $ballot = civicpower_ballot_and_asker($ballot_id);
    civicpower_remove_options_aucun_if_not_qcm($ballot_id);
    $user = sql_shift("
        SELECT usr_user.*
        FROM usr_user
        WHERE user_id = '".for_db($ballot['asker_user_id'])."'
    ");
    $unicite = "ballot_accepted_".$ballot_id;
    $hashtag = "ACCEPTATION ACCEPTATION_BALLOT_".$ballot_id;
    $data = [
        'ballot' => $ballot,
        "mail_preview" => $ballot['ballot_title']
    ];
    if(strlen($user['user_email'])>2){
        civicpower_enqueue_email(
            $user['user_email'],
            "Validation de votre consultation ",
            civicpower_html_text("mail/consultation-acceptee.tpl",$data),
            $unicite,
            $hashtag,
            0
        );
    }else if(strlen($user['user_phone_international'])>2){
        $sms_text = civicpower_sms_text('mail/consultation-acceptee-sms.tpl',$data);
        civicpower_enqueue_sms($user['user_phone_international'], $sms_text, $unicite, $hashtag);
    }
}
function civicpower_invoke_ballot_en_attente($ballot_id) {
    $ballot = civicpower_ballot_and_asker($ballot_id);
    civicpower_enqueue_email(
        $_ENV['ADMIN_EMAIL'],
        "Consultation de ".$ballot['asker_name']." à valider : ".$ballot['ballot_title'],
        civicpower_html_text("mail/admin-consultation-a-valider.tpl",[
            'ballot' => $ballot
        ]),
        "ballot_to_validate_".$ballot_id,
        "ADMIN VALIDATION_BALLOT_".$ballot_id,
        0
    );
}
function civicpower_invoke_end_ballot($ballot_id) {
    civicpower_enqueue_ballot_message(
        $ballot_id,
        'mail/ballot-end.tpl',
        'mail/ballot-end-sms.tpl',
        "Les résultats de la consultation __asker_name__ sont disponibles",
        "Connectez-vous pour connaître les résultats",
        "ballot_end_",
        null,
        5
    );
}
function civicpower_already_invited($ballot_id,$prefix){
    $already_sql = sql("
        SELECT *
        FROM mail_queue
        WHERE queue_unicite = '" . $prefix . for_db($ballot_id) . "'
    ");
    $already = [];
    foreach ($already_sql as $k => $v) {
        $already[] = $v['queue_email'];
        $already[] = $v['queue_numero'];
    }
    $already = array_filter($already);
    return $already;
}
function civicpower_update_ballot_status($ballot_id, $status_id) {
    sql("
        UPDATE bal_ballot SET
            ballot_bstatus_id = '" . for_db($status_id) . "'
        WHERE
            ballot_id = '" . for_db($ballot_id) . "'
            AND ballot_bstatus_id <> '" . for_db($status_id) . "'
    ");
    if ($status_id == $_ENV['STATUS_BALLOT_VALIDE_TERMINE']) {
        civicpower_invoke_end_ballot($ballot_id);
    }
}
function civicpower_datetime_to_date($str){
    return strftime('%Y-%m-%d', strtotime($str));
}
function civicpower_datetime_to_hour($str){
    return strftime('%H:%M', strtotime($str));
}
function civicpower_datetime_rfc($str){
    return strftime('%Y-%m-%dT%H:%M', strtotime($str));
}
function civicpower_free_user_salt() {
    $salt   = sha1(uniqid().mt_rand(0,9999).time());
    $nb     = intval(sql_unique("
        SELECT COUNT(*) AS nb
        FROM usr_user
        WHERE user_salt = '".for_db($salt)."'
    "));
    if($nb==0){
        return $salt;
    }else{
        return civicpower_free_user_salt();
    }
}
?>
