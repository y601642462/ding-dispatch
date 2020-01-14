<?php

require_once '../vendor/autoload.php';

$config = [
    'customKey' => 'XXXX',
    'customSecret' => 'XXXX',
    'auth_corpid' => 'XXXX',
    'sso_secret' => 'XX',
    'agent_id' => 'XX'
];
$app = new \Wandell\Dispatch\Dispatch($config);
//获取用户基本信息
$res = $app->getUser("code");
print_r($res);
exit;