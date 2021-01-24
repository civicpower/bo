<?php
must_be_disconnected();
$local_login_errors = local_login_errors($fw->smarty);
project_css_js($fw);
$fw->set_header_file('blank.tpl');
$fw->set_footer_file('blank.tpl');
$fw->set_body_class('login-page');
$fw->smarty->assign('login_errors',$local_login_errors);
$fw->include_css('login');
$fw->include_js('login');
$fw->set_canonical('/');
$fw->smarty->display('login.tpl');
$fw->go();

function local_login_errors(&$smarty){
	$res = array();
	if(gpost('form') == 'form'){
		global $fw;
		$login = '';
		if(!post_exists('login') || !is_string(gpost('login'))){
			$res[] = 'Veuillez renseigner votre adresse email ou votre numéro de mobile';
		}else{
			$login = substr(gpost('login'),0,255);
		}

		$password = '';
		if(!post_exists('password') || !is_string(gpost('password'))){
			$res[] = 'Veuillez renseigner votre mot de passe';
		}else{
			$password = substr(gpost('password'),0,255);
		}
		if(count($res) == 0){
			if(count($res) == 0){
				$user = sql_shift($sql = "
					SELECT *
					FROM usr_user
					WHERE (
					    user_email = '" . for_db($login) . "'
					    OR user_phone_international = '".for_db(civicpower_international_phone($login))."'
                    )
					AND user_password = '".for_db(sha1($_ENV['GLOBAL_SALT'].$password))."'
					AND user_active = '1' AND user_ban = '0'
				");
				if(isset($user) && is_array($user) && count($user)>0 && isset($user['user_id']) && is_numeric($user['user_id']) && $user['user_id']>0){
					$_SESSION['user'] = $user;
					$user_token = civicpower_hash_db(false,$user['user_salt'],$_ENV['SALT_USER']);
					setcookie($_ENV['LOGIN_COOKIE_NAME'],$user_token,time() + 365*24*60*60,'/','civicpower.io',true);
					rediriger_vers('/');
					exit();
				}else{
					$res[] = 'Informations de connexion incorrectes';
					$password = '';
				}
			}
		}
		$fw->smarty->assign('login',$login);
		$fw->smarty->assign('password',$password);
	}
	return $res;
}
?>