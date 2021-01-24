<?php
function traduction($mot,$langue_source,$langue_dest){
	$file=file_get_contents('http://translate.google.com/translate_t?hl=fr&text='.$mot.'&tl='.$langue_dest.'&sl='.$langue_source.'&ie=UTF-8&oe=UTF-8');
	preg_match('~<div id=result_box dir="ltr">(.*?)</div>~i',$file,$res);
	return $res[1];
}
function multilingue_field($is_do_action,$str_field,$value,$sql_field){
	$res = array();
	if($is_do_action){
		global $fw;
		$tab = tab_langues();
		$idg = 'idg_' . rand(1000,9999) . '_' . md5($str_field);
		$res[] = '<div class="groupement_champ_multi">';
		$value = explode_lang($value);
		//printr($value);
		$deja=false;
		foreach($tab as $v){
			$code = $v['lan_code'];
			$cur_field = $str_field;
			$cur_field = str_replace('<!--value-->',for_input(htmlspecialchars($value[$code])),$cur_field);
			$cur_field = str_replace('<!--name-->','['.for_input($code).']',$cur_field);
			$cur_field = str_replace('<!--uniquename-->','['.for_input($code).']',$cur_field);
			$cur_field = str_replace('<!--lang-->',for_input($code),$cur_field);
			$cur_field = str_replace('<!--id-->','_'.for_input($code),$cur_field);
			$cur_field = str_replace('<!--title-->',' ('.for_input($v['lan_lib']).')',$cur_field);
			if(eregi('<!--calendar-->',$cur_field)){
				$rand = rand(10,99) . '_' . time();
				$str_calendar = icone(
					'calendrier',
					'style="cursor:pointer" align="absbottom" id="btn_cal_'.$rand.'_'.for_input($sql_field).'"'
				);
				init_calendar($sql_field . '_'.for_input($code),'btn_cal_'.$rand.'_'.for_input($sql_field));
				$cur_field = str_replace('<!--calendar-->',$str_calendar,$cur_field);
			}
			$res[] = '<div '.($deja?'style="display:none"':'').' class="un_champ_multi_'.$sql_field.'" id="un_champ_multi_'.$idg.'_'.$code.'">';
			$res[] = $cur_field;
			$res[] = '</div>';
			$deja = true;
		}
		$res[] = '</div>';
		$res[] = '<div class="groupement_drapeaux">';
		$deja=false;
		foreach($tab as $v){
			$code = $v['lan_code'];
			if($deja){
				$fw->add_onload('
					$("#un_champ_multi_'.$idg.'_'.$code.'").hide("fast");
					$("#flag_'.$idg.'_'.$code.'").animate({"opacity": "0.3"}, "fast");
				');
			}
			$fw->add_onload('
				$("#flag_'.$idg.'_'.$code.'").click(function(){
					$(".flag_'.$idg.'").animate({"opacity": "0.3"}, "fast");
					$("#flag_'.$idg.'_'.$code.'").animate({"opacity": "1"}, "fast");
					$(".un_champ_multi_'.$sql_field.':visible").slideUp("fast");
					$("#un_champ_multi_'.$idg.'_'.$code.'").slideDown("fast");
				});
			');
			$res[] = '<img alt="'.$code.'" title="'.for_input($v['lan_lib']).'" id="flag_'.$idg.'_'.$code.'" class="flag_'.$idg.'" src="'.webdir('/../fw/module/flags/' . strtolower($code) . '.gif').'" />';
			$deja = true;
		}
		$res[] = '</div>';
	}else{
		$cur_field = $str_field;
		$cur_field = str_replace('<!--value-->',for_input(htmlspecialchars($value)),$cur_field);
		$cur_field = str_replace('<!--name-->','',$cur_field);
		$cur_field = str_replace('<!--uniquename-->','[UNIQUE]',$cur_field);
		$cur_field = str_replace('<!--lang-->','',$cur_field);
		$cur_field = str_replace('<!--id-->','',$cur_field);
		$cur_field = str_replace('<!--title-->','',$cur_field);
		if(eregi('<!--calendar-->',$cur_field)){
			$rand = rand(10,99) . '_' . time();
			$str_calendar = icone(
				'calendrier',
				'style="cursor:pointer" align="absbottom" id="btn_cal_'.$rand.'_'.for_input($sql_field).'"'
			);
			init_calendar($sql_field ,'btn_cal_'.$rand.'_'.for_input($sql_field));
			$cur_field = str_replace('<!--calendar-->',$str_calendar,$cur_field);
		}
		$res[]=$cur_field;
	}
	$res = implode(BN,$res);
	return $res;
}
function explode_lang($str){
	$str = explode('^~',$str);
	$cur=array();
	foreach($str as $k => $v){
		$v = explode('~^',$v);
		if(count($v)>=2){
			$lib = $v[0];
			$code = $v[1];
			$cur[$code] = $lib;
		}
	}

	$tab = tab_langues();
	$res = array();
	foreach($tab as $v){
		$code = $v['lan_code'];
		$res[$code] = $cur[$code];
	}
	return $res;
}
function cur_lang(){
	static $res = null;
	if(is_null($res)){
		$res = '';
		$setcookie = false;
		$langs = tab_langues();
		if(get_exists('lang') && isset($langs[gget('lang')])){
			$setcookie = true;
			$res = gget('lang');
		}else if(post_exists('lang') && isset($langs[gpost('lang')])){
			$setcookie = true;
			$res = gpost('lang');
		}else{
			$url = $_SERVER['REQUEST_URI'];
			$url = preg_replace("~^/~","",$url);
			$url = preg_replace("~/$~","",$url);
			$url = explode('/',$url);
			$fini = false;
			if(count($url) > 0){
				$one = array_shift($url);
				if(isset($langs[$one])){
					$res = $one;
					$setcookie = true;
					$fini = true;
				}
			}
			if(! $fini){
				if(cookie_exists('lang') && isset($langs[gcookie('lang')])){
					$res = gcookie('lang');
				}else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $t = get_browser_lang($_SERVER['HTTP_ACCEPT_LANGUAGE'],$langs)){
					$setcookie = true;
					$res = $t;
				}else if(isset($_SERVER['GEOIP_COUNTRY_CODE']) && isset($langs[strtolower($_SERVER['GEOIP_COUNTRY_CODE'])])){
					$setcookie = true;
					$res = strtolower($_SERVER['GEOIP_COUNTRY_CODE']);
				}
			}
		}
		if($setcookie){
			setcookie('lang',$res,time()+(60*60*24*365*5));
		}
	}
	return $res;
}
function get_browser_lang($blang,$langs){
	$blang = preg_replace("~[0-9]~","",$blang);
	$blang = simplize($blang);
	$blang = explode("-",$blang);
	foreach($blang as $k => $v){
		if(isset($langs[$v])){
			return $v;
		}
	}
	return false;
}
function trad($mot,$lang=null){
    return $mot;
	$tab = explode_lang($mot);
	if(is_null($lang)){
		$lang = cur_lang();
	}
	return $tab[$lang];
}
function implode_lang($val){
	$res=array();
	$tab=tab_langues();
	foreach($tab as $v){
		$code = $v['lan_code'];
		if(isset($val[$code]) && strlen($val[$code])>0){
			$res[] = $val[$code];
		}
		$res[] = '~^' . $code . '^~';
	}
	$res = implode('',$res);
	//printr($res);exit();
	return $res;
}
function tab_langues(){
	static $res = null;
	if(is_null($res)){
			$res = arranger_tableau_par(sql("
			SELECT * FROM boa_langue
			ORDER BY lan_default='1' DESC
		"),'lan_code');
	}
	return $res;
}
function chglang($new){
	$url = $_SERVER['REQUEST_URI'];
	$url = explode("/",$url);
	array_shift($url);
	array_shift($url);
	array_unshift($url, $new);
	$url = '/' . implode('/',$url);
	return $url;
}
function go_lang(){
	$url = $_SERVER['REQUEST_URI'];
	$url = preg_replace("~^/~","",$url);
	$url = preg_replace("~/$~","",$url);
	$url = explode('/',$url);
	$ln = cur_lang();
	if(count($url) > 0){
		$one = array_shift($url);
		$langues = tab_langues();
		if(! isset($langues[$one])){
			header("LOCATION:/".$ln . $_SERVER['REQUEST_URI']);
		}else{
			if($ln != $one){
				header("LOCATION:/".$ln . preg_replace("~^/".$one."/~",'/'.$ln.'/',$_SERVER['REQUEST_URI']));
			}
		}
	}
}
?>