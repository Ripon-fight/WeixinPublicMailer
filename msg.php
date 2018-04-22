<?php
/**
 * Created by PhpStorm.
 * User: xingwei
 * Date: 2018/4/20
 * Time: 上午8:42
 */
//接收微信服务器发过来的参数
//参数	        |   描述
//--------------------------------------
//ToUserName	|   开发者微信号
//FromUserName	|   发送方帐号（一个OpenID）
//CreateTime	|   消息创建时间 （整型）
//MsgType	    |   text
//Content	    |   文本消息内容
//MsgId	        |   消息id，64位整型
class WeixinMessage
{
    public $ToUserName;
    public $FromUserName;
    public $CreateTime;
    public $MsgType;
    public $Content;
    public $MsgId;

    function receiveTextMsg()
    {
        require_once "Log.php";
        $log = new Log();
        $input = file_get_contents("php://input");
        if (!empty($input)) {
            $xml = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
//      $content = "你发送的文本内容为：".$xml->Content."\n<br>";
            $this->Content = $xml->Content;
            $this->ToUserName = $xml->ToUsername;
            $this->FromUserName = $xml->FromUserName;
            $this->CreateTime = $xml->CreateTime;
            $this->MsgId = $xml->MsgId;
            $this->MsgType = $xml->MsgType;
//      $log->mylog("收到文本消息：".$xml->Content."\n");
            return $this->Content;
        } else {
//        echo "No POST"."\n<br>";
            $log->mylog("No POST");
            exit;
        }
    }
}


?>
