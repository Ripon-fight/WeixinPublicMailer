<?php  
   require("weixin.php");
   require_once "msg.php";
   require_once "Log.php";
   require(__DIR__."/vendor/phpmailer/phpmailer/src/PHPMailer.php");
   require(__DIR__."/vendor/phpmailer/phpmailer/src/SMTP.php");


   $message = new WeixinMessage();
   $log = new Log();
   $wx = new WeiXinConfirm();  

//   if($_GET['signature']!=''){
//      $wx->valid();
//   }
//   else {
{
//       $text= "开始接收Message...";
//       $file = fopen("run_result.html", "aw") or die("Unable to open file!");
//       fwrite($file, $text."\n");
//       fclose($file);

//    $log->mylog("开始接收Message...");
    $text = $message->receiveTextMsg();
//    $log->mylog($text);
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP(); // enable SMTP
//将收到的文本内容转发到邮箱
    $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for QQmail
    $mail->Host = "smtp.qq.com";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username = "408721706@qq.com";
    $mail->Password = "zdxwqbrunmwhbjbe";
    $mail->SetFrom("408721706@qq.com");
    $mail->Subject = "New message from WeixinPublic";
    $mail->Body = $text;
    $mail->AddAddress("408721706@qq.com");
    $message->Content = "您好！已收到您的提问！我们将转发至管理者邮箱！";

    if(!$mail->Send()) {

//        $log->mylog("Mailer Error: " . $mail->ErrorInfo);
    } else {
       echo "<xml>
 <ToUserName><![CDATA[".$message->FromUserName."]]></ToUserName>
 <FromUserName><![CDATA[".$message->ToUserName."]]></FromUserName>
 <CreateTime>".$message->CreateTime."</CreateTime>
 <MsgType><![CDATA[".$message->MsgType."]]></MsgType>
 <Content><![CDATA[".$message->Content."]]></Content>
 </xml>";
        $log->mylog( "Message has been sent");
    }

   }

?> 
