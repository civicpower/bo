<?php
$referer = $_SERVER['HTTP_REFERER'];
if(strlen($referer)>2){
	$base_referer = parse_url($referer);
	$base_referer = $base_referer['host'];
	$base_site = $_SERVER['SERVER_NAME'];
	if($base_site != $base_referer){
		setcookie('URL_REFERER',$referer,time() + 3600 * 24 * 30);
	}
}
function load_redirect(){
	if(session_exists('redirect')){
		$url = gsession('redirect');
		unset($_SESSION['redirect']);
		rediriger_vers($url);
		exit();
	}
}
function save_redirect(){
	$url =  $_SERVER['REQUEST_URI'];
	$_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
}
?>