<?php
$returnConfig = array();

$returnConfig['URL_ROUTE_RULES'] = array(
    '/^news\/id_([a-z0-9]+)$/' => 'News/view?id=:1',
    '/^coin\/code_([a-z0-9]+)$/' => 'Coin/view?code=:1',
);


$returnConfig['URL_MAP_RULES'] = array(
    'index'  =>'Index/index',
    'quotations'  =>'Index/quotations',
    'deep'  =>'Index/deep',
    'login'  =>'Index/login',
    'reg_protocol'  =>'Index/reg_protocol',
    'about'  =>'Index/about',
    'search'  =>'Search/index',
);

return $returnConfig;