<?php
/**
 * Created by PhpStorm.
 * User: xingwei
 * Date: 2018/4/20
 * Time: 上午9:14
 */
$APPID = 'wx0b7c40ddf3a129fe';
$APPSECRET = 'a3de93cb427b0b7bdd616ea8e07a4e23';
$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$APPID.'&secret='.$APPSECRET;
$json = file_get_contents($url);
$json = json_decode($json);
$access_token = $json->{'access_token'};
echo $access_token

?>