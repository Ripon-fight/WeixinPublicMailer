# WeixinPublicMailer
## 微信公众平台开发记录
### 开发前的准备
1. 准备一个公众号，如果没有到[微信公众平台](https://mp.weixin.qq.com/)去注册

2. 在[微信开放平台](https://open.weixin.qq.com)注册开发者账号,并和绑定微信公众号的管理员账号

3. 到微信开放平台上进行token认证，不认证我们的服务器调用接口就没法用，具体的直接看微信开放平台的开发者文档，这里就不搬运了。

P.S. 如果只是为了测试实验用可以不用费力气注册一个公众号，可以到开发者工具——公众测试平台账号去测试服务器接口
### 项目：收到用户特定消息发送邮件到指定邮箱
**分为模块来实现。**

1. msg.php  
	实现`WeixinMessage`类和`ReceiveTextMsg()`方法,用接收POST+XML包请求的方式获取消息文本，如果消息匹配某个特定的值比如`StarStudio`就执行接下来的邮件模块。
	
```php
<?php
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
//出于方便没有封装属性，实际上这是不安全的写法
    public $ToUserName;
    public $FromUserName;
    public $CreateTime;
    public $MsgType;
    public $Content;
    public $MsgId;

    function receiveTextMsg()
    {
        $log = new Log();
        $input = file_get_contents("php://input");
        if (!empty($input)) {
        //解析xml包
            $xml = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
//      $content = "你发送的文本内容为：".$xml->Content."\n<br>";
//将解析出来的key-value存储到对象
            $this->Content = $xml->Content;
            $this->ToUserName = $xml->ToUsername;
            $this->FromUserName = $xml->FromUserName;
            $this->CreateTime = $xml->CreateTime;
            $this->MsgId = $xml->MsgId;
            $this->MsgType = $xml->MsgType;
            return $this->Content;
            //直接返回收到的文本消息，这里偷懒了。
        } else {
//        echo "No POST"."\n<br>";
            exit;
        }
    }
}
?>
```
2. index.php  
邮件部分使用比较常用的[PHPmailer](https://github.com/PHPMailer/PHPMailer),如果没有安装[composer](https://getcomposer.org/)的话需要安装一下，
composer是一个PHP包管理器，类似pip，它依托于`Packagist`平台集成了大量的轮子供PHP开发者使用。
CentOS命令如下：

```bash
wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet
```
将`composer.pchar`下载到网站的root目录，运行以下命令来获取PHPMailer：

```bash
php composer.pchar require PHPMailer/PHPMailer
```
等待一段时间，terminal最后两行显示如下信息就安装成功了：）

```bash
Writing lock file
Generating autoload file
```
接下来我们可以愉快地使用PHPMailer来发送邮件了.
发送邮件我选择使用最简单，同时支持HTML邮件的smtp服务，首先要从邮箱服务器获取smtp授权：
![](http://xingwei.me/wp-content/uploads/2018/04/qqsmtp.png)

点击生成授权码，按照要求发送短信，这个授权码千万不能给别人看到

OK，有了授权码以后，我们可以写代码了。

```php
#index.php
<?php  
//   require("weixin.php");
   require_once "msg.php";
   require(__DIR__."/vendor/phpmailer/phpmailer/src/PHPMailer.php");
   require(__DIR__."/vendor/phpmailer/phpmailer/src/SMTP.php");


   $message = new WeixinMessage();
   $log = new Log();
   $wx = new WeiXinConfirm();  
//	服务器Token认证
//   if($_GET['signature']!=''){
//      $wx->valid();
//   }
//   else
{
    $text = $message->receiveTextMsg();//接收用户发送过来的消息
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    //下面的配置其实应该写在ini文件里比较好
    $mail->IsSMTP(); // enable SMTP
//将收到的文本内容转发到邮箱
    $mail->SMTPDebug = 0; //这里调试的时候写1，调通记得改成0，不然PHPMailer会echodebug 数据
// debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for QQmail
    $mail->Host = "smtp.qq.com";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username = "xxxx@qq.com";//邮件服务器的账号
    $mail->Password = "xxxxxx";//不是邮箱密码是授权码
    $mail->SetFrom("xxxx@qq.com");
    $mail->Subject = "New message from WeixinPublic";
    $mail->Body = $text;
    $mail->AddAddress("xxxx@qq.com");
    //如果想发到多个邮箱就重复调用AddAddress()方法
    $message->Content = "您好！已收到您的提问！我们将转发至管理者邮箱！";

    if(!$mail->Send()) {

//        echo "Mailer Error: " . $mail->ErrorInfo);
    } else {
    // 这里直接返回xml包给微信，实现被动回复用户消息
       echo "<xml>
 <ToUserName><![CDATA[".$message->FromUserName."]]></ToUserName>
 <FromUserName><![CDATA[".$message->ToUserName."]]></FromUserName>
 <CreateTime>".$message->CreateTime."</CreateTime>
 <MsgType><![CDATA[".$message->MsgType."]]></MsgType>
 <Content><![CDATA[".$message->Content."]]></Content>
 </xml>";

    }

   }

?> 

```
这里有一个大坑，原先要调用PHPmailer类只要声明PHPMailer命名空间然后`require(__DIR__.'/vendor/autoload.php');`就可以了，在2018/02之后好像不适用了，上Stackoverflow查到只能如上述代码使用绝对路径：

```php
require(__DIR__."/vendor/phpmailer/phpmailer/src/PHPMailer.php");
require(__DIR__."/vendor/phpmailer/phpmailer/src/SMTP.php");
```
至此整个项目就写完了，到微信公众号上发一条消息就可以自动转发到指定邮箱。

