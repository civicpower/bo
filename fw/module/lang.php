<?php
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
function trad($mot,$lang=null){
    return $mot;
	$tab = explode_lang($mot);
	if(is_null($lang)){
		$lang = cur_lang();
	}
	return $tab[$lang];
}
?>