<?php
$user = must_be_connected();
project_css_js($fw);
$fw->smarty->assign('menu_actif',"dashboard");
$fw->smarty->assign('user',$user);
$fw->include_css('index');
$fw->add_css('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
$fw->include_js('index');
$fw->set_canonical('/');
$fw->smarty->display('index.tpl');
$fw->go();
?>