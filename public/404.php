<?php
header("HTTP/1.0 404 Not Found");
$user = must_be_connected();
local_action($user);
project_css_js($fw);
$fw->include_css('404');
$fw->include_js('404');
$fw->set_canonical('/404');
$fw->smarty->display('404.tpl');
$fw->go();
function local_action($user){
}
?>