<?php
function project_literal($str){
    return '{literal}'.$str.'{/literal}';
}
function file_get_contents_async($url, $params=null){
    if(!is_array($params)){
    	$params = array();
    }
    $post_params = array();
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);
    switch ($parts['scheme']) {
        case 'https':
            $scheme = 'ssl://';
            $port = 443;
            break;
        case 'http':
        default:
            $scheme = '';
            $port = 80;
    }
    try{
        $fp = @fsockopen($scheme.$parts['host'],$port,$errno,$errstr,30) or die("unable to connect fsockopen");
        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out .= "Host: ".$parts['host']."\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: ".strlen($post_string)."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if(isset($post_string)){
            $out .= $post_string;
        }

        $res = array();
        fwrite($fp,$out);
        while(!feof($fp)){
            $res[] = fgets($fp,128);
        }
        fclose($fp);
        return implode(BN,$res);
    }catch(Exception $ex){
        return '';
    }
}
function no_php_ext(){
	$curfile_name = str_replace('.php','',basename($_SERVER['SCRIPT_NAME']));
	if(preg_match("~^/".$curfile_name."\.php~",$_SERVER['REQUEST_URI'])){
		$dest = preg_replace("~^/".$curfile_name."\.php~","/".$curfile_name,$_SERVER['REQUEST_URI']);
		header("Status: 301 Moved Permanently", false, 301);
		header("LOCATION:" . $dest);exit();
	}
}
function error_404(&$fw){
	$fw->smarty->display('404.tpl');
	mise_en_cache();
}
function project_referer(){
	$res = '';
	if(isset($_SERVER['HTTP_REFERER'])){
		$tmp = $_SERVER['HTTP_REFERER'];
		if(is_string($tmp) && strlen($tmp) > 0 && strlen($tmp) < 500){
			$tmp = parse_url($tmp);
			if(isset($tmp['host'])){
				$host = $tmp['host'];
				if(is_string($host) && strlen($host) > 0 && strlen($host) < 500){
					if(!preg_match("~".$_ENV['SITE_URL_SHORT']."$~",$host)){
						$res = $_SERVER['HTTP_REFERER'];
						setcookie('referer',$res,time() + 3600 * 24 * 365 * 2,'/');
					}
				}
			}
		}
	}
	if($res == ''){
		if(cookie_exists('referer')){
			$res = gcookie('referer');
		}
	}
	return $res;
}
function project_canonical($q){
	$canonical = $q;
	$canonical = strtolower($canonical);
	$canonical = str_replace(' ','_',$canonical);
	$canonical = stripslashes($canonical);
	return $canonical;
}
function project_t($word){
    return $word;
}
function project_css_js(&$fw){
    $fw->add_css("https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback");
    $fw->add_css("plugins/fontawesome-free/css/all.min.css");
    $fw->add_css("dist/css/adminlte.min.css");
	$fw->add_css('project.css?j='.date('dmyH'));
	$fw->add_js('project.js?j='.date('dmyH'));
}
function project_add_bootstrap(&$fw){
    if(false) {
        $fw->css_tab[] = '/css/bootstrap-min.css';
        $fw->add_js('https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js');
    }else{
        $fw->css_tab[] = 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css';
        $fw->add_js('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js');
    }
}
function project_add_jquery_cdn(&$fw){
	$fw->add_js( 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
}
function project_crypt($string,$salt=""){
	return sha1($_ENV['GLOBAL_SALT'].$string.$salt);
}
function project_md5_db($sql_language,$string,$salt=""){
	if($sql_language){
	    return "MD5(CONCAT($string,'".for_db($_ENV['GLOBAL_SALT'])."','".for_db($salt)."'))";
    }else{
	    return md5("".$string.$_ENV['GLOBAL_SALT'].$salt);
    }
}
function project_datetimeen2fr($str){
    $str = strtotime($str);
    $str = date("j M Y - H:i ",$str);
    return $str;
}
function project_date_fr($str){
	$str = strtotime($str);
	$str = date("d/m/Y",$str);
	return $str;
}
function project_minidate_fr($str,$sep=" "){
	$str = strtotime($str);
	$str = date("d".$sep."m".$sep."Y",$str);
	return $str;
}
function project_random_reference(){
    $nb1=2;
    $nb2=3;
    $annee = date('y');
    $mois = date('m');
    $str = $annee.$mois;
    $chars1 = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $chars2 = '23456789';
    for($i=0;$i<$nb1;$i++){
        $str.=$chars1[rand(0, strlen($chars1)-1)];
    }
    for($i=0;$i<$nb2;$i++){
        $str.=$chars2[rand(0, strlen($chars2)-1)];
    }
    return $str;
}
function project_deny_referer_list(){
	$list = array(
		".*?traffic\-cash\.xyz",
	);
	$referer = $_SERVER['HTTP_REFERER'];
	foreach($list as $k => $v){
		if(preg_match("~".$v."~",$referer)){
			exit();
		}
	}
}
function project_get_lang(){
	$lang = $_ENV['SITELANG'];
	$host = $_SERVER['HTTP_HOST'];
	if(is_string($host) && strlen($host)>0){
		$tmp = str_ireplace($_ENV['SITE_URL_SHORT'], "", $host);
		$tmp = simplize($tmp);
		if(!in_array($tmp,array("www","",$_ENV['SITELANG']))){
			$lang = $tmp;
		}
	}
	return $lang;
}
function project_get_user_id(){
    return intval(trim($_SESSION['user']['user_id']));
}
function project_get_ua(){
	$res = "";
	try{
		$tmp = $_SERVER['HTTP_USER_AGENT'];
		if(is_string($tmp) && strlen($tmp)>2){
			$res = $tmp;
		}
	}catch(Exception $ex){}
	return $res;
}
function project_clean_cachefile(&$str){
    $str = preg_replace("~([\n\r])[\t ]+~","$1",$str);
    $str = preg_replace("~>[ \t]+<~","><",$str);
    $str = preg_replace("~>[\n\r]+<~",">\n<",$str);
    $linkable = array("option","li","script","link","meta","div");
    foreach($linkable as $k => $v){
        for($i=0;$i<3;$i++){
            $str = preg_replace("~(".$v.")>[\s\n\r ]+<(/?".$v.")~","$1><$2",$str);
        }
    }
    $str = trim($str);
}
function project_file_get_contents($file){
    //$file = str_replace($_ENV['HTTP_MODE'].'://'.$_SERVER["SERVER_NAME"],dirname(__FILE__)."/..",$file);
    $file = str_replace("//", "/",$_SERVER['DOCUMENT_ROOT'].    
                str_replace("//", "/",
                    str_replace($_ENV['HTTP_MODE'].'://'.$_SERVER["SERVER_NAME"]
                    ,""
                    ,$file)
                )
            );
    return file_get_contents($file);
}

project_deny_referer_list();
project_referer();


/* 
 * @access      -
 * @param       -
 * @author      C2
 * Purpose      time
 * @roadmap     2020/12 : born
 */
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/* 
 * @access      -
 * @param       content & logger file
 * @author      C2
 * Purpose      debug
 * @roadmap     2020/12 : born
 */
function vardump($message="",$log=""){
    if ($message<>"") {
        // Default
            if (!$log) { 
                if ( isset($_ENV['LOG_ERROR']) && ($_ENV['LOG_ERROR']==TRUE) ) {
                    $log = $_ENV['LOG_ERROR'];
                }
                else { $log = '/var/log/php-errors.log'; }
            }
        // Echo DUMP
            error_log(strftime('%Y-%m-%d %I:%M %P', strtotime('now')));
            error_log("********* data *********\n", 3, $log);
            ob_start();                    // start buffer capture
            var_dump( $message );           // dump the values
            $contents = ob_get_contents(); // put the buffer into a variable
            ob_end_clean();                // end capture
            error_log( $contents, 3, $log); 
            error_log("********* /data *********\n", 3, $log);
    }
}

/* 
 * @param       various mail config
 * @author      C2
 * Purpose      verify the data format we want to store
 * @roadmap     2020/12 : born
 */
function debugMailer ( $arguments = array('') ) {
    if ( isset($_ENV['MAIL_DEBUG']) && ($_ENV['MAIL_DEBUG']==TRUE) ) {
        // Just in case
            if (!isset($arguments['message'])||($arguments['message'])=="") {
                $arguments['message'] = 
                 "*** \ ***"
                ."function : ".(explode('?', explode('/', trim($_SERVER['REQUEST_URI'],".php"))[1]))[0]
                ."<BR>"
                ."script : ".$_SERVER['PHP_SELF']
                ."arguments : ".$_SERVER['PHP_SELF']
                ."<BR>"
                ."time : ".strftime('%Y-%m-%d %I:%M %P', strtotime('now'))
                ."<BR>"
                ."*** / ***";
                foreach ($_SERVER as $arg) {
                    $arguments['message'] .= $arg.' :'.$_SERVER[$arg]."<BR>";
                }
                $arguments['message'] .= "*** \ ***";
            }
            if (!isset($arguments['subject'])||($arguments['subject'])=="") {
                $arguments['subject'] = "Civicpower Issue";
            }
        // Create email
            $mail               =   new PHPMailer;
            // C2 2018/11
                $mail->CharSet = 'UTF-8';
                $mail->ContentType = 'text/html';
            $mail->IsSMTP();
            // Debug level (2 full info)
                $mail->SMTPDebug    =   0;
            // SMTP auth
                $mail->SMTPAuth     =   true;
                $mail->SMTPSecure   =   'tls';
                $mail->Host         =   'mail.gandi.net';
                $mail->Port         =   587;
                $mail->Username     =   ( ( isset($_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG'])&&($_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG']<>"") ) ? $_ENV['MAIL_FROM_SMTPACCOUNT_DEBUG'] : "");
                $mail->Password     =   ( ( isset($_ENV['MAIL_TO_DEBUG_PASSWORD'])&&($_ENV['MAIL_TO_DEBUG_PASSWORD']<>"") ) ? $_ENV['MAIL_TO_DEBUG_PASSWORD'] : "");
            // From
                $mail->setFrom( ( ( isset($_ENV['MAIL_FROM_DEBUG'])&&($_ENV['MAIL_FROM_DEBUG']<>"") ) ? $_ENV['MAIL_FROM_DEBUG'] : "") );
            // To
                $mail->addAddress( ( ( isset($_ENV['MAIL_TO_DEBUG'])&&($_ENV['MAIL_TO_DEBUG']<>"") ) ? $_ENV['MAIL_TO_DEBUG'] : "") );
            // Subject
                $mail->Subject      =   utf8_decode($arguments['subject']);
            // Body
                $mail->AltBody      =   strip_tags( str_replace(array('<br>','<br/>','<br />'), "\r\n", $arguments['message'] ) );
            // Message
                $mail->MsgHTML($arguments['message']);
        // Do we send?
            $result = $mail->send();
            if ($mail->ErrorInfo) { return FALSE; }
            else { return TRUE; }                   
    }
}
?>
