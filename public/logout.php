<?php
must_be_connected();
unset($_COOKIE[$_ENV['LOGIN_COOKIE_NAME']]);
setcookie($_ENV['LOGIN_COOKIE_NAME'],'',time() + 365*24*60*60,'/',$_ENV['COOKIE_URL'],true,true);
unset($_SESSION);
session_destroy();
header('LOCATION:/login');
exit();
?>