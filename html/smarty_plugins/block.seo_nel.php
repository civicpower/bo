<?php
/**** seo_nel : NOT EMPTY LINE ***/
function smarty_block_seo_nel($params, $content, &$smarty, $open){
    if(!$open){
		$content = explode(BN,$content);
		foreach($content as $k => $v){
		    $v = trim($v);
		    if(strlen($v)<=0){
		        unset($content[$k]);
            }
        }
		$content = implode(BN,$content);
		return $content;
    }
}

