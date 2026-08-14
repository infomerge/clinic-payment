<?php

//define( 'SMARTY_DIR', '/usr/local/apache2/htdocs/tmdb/libs/' );
#define( 'SMARTY_DIR', '/var/www/html/saloncms/libs/' );
define( 'SMARTY_DIR', dirname(dirname(__FILE__)).'/libs/' );

require_once( SMARTY_DIR .'Smarty.class.php' );
$smarty = new Smarty();

$smarty->caching = false;

/*
$smarty->template_dir = '/usr/local/apache2/htdocs/tmdb/libs/templates/';
$smarty->compile_dir  = '/usr/local/apache2/htdocs/tmdb/libs/templates_c/';
$smarty->config_dir   = '/usr/local/apache2/htdocs/tmdb/libs/configs/';
$smarty->cache_dir    = '/usr/local/apache2/htdocs/tmdb/libs/cache/';
*/
/**/
$smarty->template_dir = SMARTY_DIR.'templates/';
$smarty->compile_dir  = SMARTY_DIR.'templates_c/';
$smarty->config_dir   = SMARTY_DIR.'configs/';
$smarty->cache_dir    = SMARTY_DIR.'cache/';
/**/
?>