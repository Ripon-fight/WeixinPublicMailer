<?php       
   define("TOKEN", "xingwei");
   require_once "Log.php";
   class WeiXinConfirm{ 
       private function checkSignature()
       {
             //1.接收微信发过来的get请求过来的4个参数 
             $signature = $_GET["signature"];
             $timestamp = $_GET["timestamp"];
             $nonce = $_GET["nonce"]; //随机数
             
             //2.加密
             //1.将token,timestamp,once 三个参数进行字典序排序
             $tmpArr = array(TOKEN,$timestamp,$nonce);
             sort($tmpArr,SORT_STRING);
             
             //2.将三个参数字符串拼接成一个字符串进行sha1加密
             $tmpStr =  implode($tmpArr);
             $tmpStr =  sha1($tmpStr);

             //3.将 加密后的字符串与$signature对比
             if( $tmpStr == $signature ){
                 return true;
             }else{
                 return false;
             }
         }
         
         public function valid()
         {
              $log = new Log();
             if ($this->checkSignature()){
                 echo $_GET["echostr"];
                 return true;
             }else{
                 echo "Token验证失败！"."\n<br>";
                 $log->mylog("Token验证失败！");
                 return false;
             }
         }
     
 
   }

?>
