<?php

function smarty_block_seo_cache($params, $content, &$smarty, &$open){
    $doit = true;
    if(isset($GLOBALS['disable_seo_cache']) && $GLOBALS['disable_seo_cache']==true){
        $doit = false;
    }
    if($doit){
        $name = $params['name'];
        $cachepath = dirname(__FILE__)."/../../tmp/seo_cache_".$name.".txt";
        if($open){
            $prc = $params['prc'];
            //echo $filepath;exit();
            $cache = true;
            if(!file_exists($cachepath)){
                $cache = false;
            }else{
                $rand = rand(0,100);
                if($rand<$prc){
                    $cache = false;
                }
            }
            if($cache){
                $content = file_get_contents($cachepath);
                $content = gzuncompress($content);
                $open = false;
                return $content;
            }
            //return $content;
            //echo $content;
        }else{
            $fp = fopen($cachepath,"w");
            project_clean_cachefile($content);
            $gzcache = gzcompress($content,9);
            fwrite($fp,$gzcache);
            fclose($fp);
            return $content;
        }
    }else{
        if(!$open){
            return $content;
        }
    }
}

