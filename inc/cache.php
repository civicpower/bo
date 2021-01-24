<?php
$do_caching = true;
$no_cache = array(
	'/tist',
	'/blog',
);
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = preg_replace("~\?.*?$~","",$request_uri);
$request_uri = preg_replace("~#.*?$~","",$request_uri);
if(in_array($request_uri,$no_cache)){
	$do_caching = false;
}
if(isset($no_caching) && $no_caching){
	$do_caching = false;
}
if(isset($_GET['spintax']) && $_GET['spintax']=='ok'){
	$do_caching = false;
}
if($do_caching){
	if(!isset($fw_cache_dir) || strlen($fw_cache_dir)==0){
		$fw_cache_dir = 'data';
	}
	if(!isset($cache_uri)){
		$cache_uri = $request_uri;
	}
	$md5 = md5($cache_uri);
	$cache_filename = dirname(__FILE__) . '/../'.$fw_cache_dir.'/' . $md5 . '.txt';
	if(!isset($no_get_caching) || !$no_get_caching){

		if(file_exists($cache_filename)){
            if(false){
                $time = microtime();
                $time = explode(' ',$time);
                $time = $time[1]+$time[0];
                $start = $time;
            }
            $str = file_get_contents($cache_filename);
            $str = gzuncompress($str);
            echo $str;
            if(false){
                $time = microtime();
                $time = explode(' ',$time);
                $time = $time[1]+$time[0];
                $finish = $time;
                $diff = $finish-$start;
                $diff *= 10000000;
                $total_time = round(($diff),4);
                echo '<!--Page generated in '.$total_time.' seconds.-->';
            }
			exit();
		}
	}
	$cached_url_file = dirname(__FILE__) . '/cached-url.php';
	if(file_exists($cached_url_file)){
        $cached_url = file($cached_url_file);
	}else{
		$cached_url = array();
	}
	$cached_url[] = $request_uri;
	$cached_url = array_unique($cached_url);
	foreach($cached_url as $kk => $vv){
	    $vv = trim($vv);
	    if(preg_match("~utm_source~i",$vv)){
		    unset($cached_url[$kk]);
	    }else if(strlen($vv)==0){
	        unset($cached_url[$kk]);
        }else{
	        $cached_url[$kk]=$vv;
        }
	}
	$cached_url = array_unique($cached_url);
	sort($cached_url);
	$fp2 = fopen($cached_url_file,'w');
	fwrite($fp2,implode("\n",$cached_url) );
	fclose($fp2);
	function mise_en_cache(){
		global $cache_filename, $fw;
		force_mise_en_cache($cache_filename);
	}
}else{
	function mise_en_cache(){
		global $fw;
		$fw->go();
	}
}

function force_mise_en_cache($cache_filename){
	global $fw;
	$cache = $fw->fetch();
	$fp = fopen($cache_filename,'w');
	project_clean_cachefile($cache);
	//$gzcache = $cache;
	$gzcache = gzcompress($cache);
	fwrite($fp,$gzcache );
	fclose($fp);
	echo $cache;
	exit();
}
?>