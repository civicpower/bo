<?php

function smarty_block_spin($params, $content, &$smarty, $open){
	if(!$open){
		if($_GET['spintax']=='ok'){
			return '{ldelim}' .trim($content). '{rdelim}';
		}else{
			$content = trim($content);
			$content = explode("|",$content);
			if(count($content)>1){
				do{
					shuffle($content);
					array_shift($content);
				}while(count($content)>1);
			}
			$content = array_shift($content);
			return trim($content);
		}
	}
}
