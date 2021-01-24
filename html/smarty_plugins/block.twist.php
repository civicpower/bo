<?php

function smarty_block_twist($params, $content, &$smarty, $open){
	if(!$open){
		$content = trim($content);
		$sep = "\n";
		if(isset($params['sep'])){
			$sep = $params['sep'];
		}
		$sep2 = "\n";
		if(isset($params['sep2'])){
			$sep2 = $params['sep2'];
		}
		$content = explode($sep,$content);
		shuffle($content);
		if(isset($params['max']) && is_numeric($params['max']) && $params['max']>=0){
			while(count($content)>$params['max']){
				array_shift($content);
				shuffle($content);
			}
		}
		$content = implode($sep2,$content);
		$content = trim($content);
		return $content;
	}
}
