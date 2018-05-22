<?php

namespace app\index\controller;


//use mikkle\tp_wechat\Wechat;
use think\Controller;
use app\index\model\Index as indexModel;

class Index extends Controller
{
    static $Text_template = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";

    public function index()
    {

        //获得参数 signature nonce token timestamp echostr

        $token = 'shuoshuo';
        if (isset($_GET['nonce'])) {
            $nonce = $_GET['nonce'];
        }
        if (isset($_GET['timestamp'])) {
            $timestamp = $_GET['timestamp'];
        }
        if (isset($_GET['echostr'])) {
            $echostr = $_GET['echostr'];
        }
        if (isset($_GET['signature'])) {
            $signature = $_GET['signature'];
        }


        //形成数组，然后按字典序排序
        $array = array();
        $array = array($nonce, $timestamp, $token);


        sort($array, SORT_STRING);

        //拼接成字符串,sha1加密 ，然后与signature进行校验
        $str = sha1(implode($array));

        try {

            if ($str == $signature && isset($echostr)) {
                //第一次接入weixin api接口的时候
                echo $echostr;
                exit;
            } else {
                $this->reponseMsg();
            }
        } catch (\think\Exception $ex) {
            echo $ex->getMessage();
        }


    }

    // 接收事件推送并回复
    public function reponseMsg()
    {
//        //1.获取到微信推送过来post数据（xml格式）
        //$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postArr = file_get_contents('php://input');
//        //2.处理消息类型，并设置回复类型和内容<xml>
        $postObj = simplexml_load_string($postArr);
//        //判断数据包中是否有订阅的实践推送
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();

        if ($postObj->MsgType == 'event') {
            $msgType = 'text';
//            //关注subscribe事件
            if ($postObj->Event == 'subscribe') {
                //回复用户消息(纯文本格式)
                $content = '欢迎关注，发送公司关键字即可查询';
                $info = sprintf(self::$Text_template, $toUser, $fromUser, $time, $msgType, $content);
                echo $info;
                exit;
            }
        } elseif ($postObj->MsgType == 'text') {
            $msgType = 'text';
            $str = $postObj->Content;
            $strs = "%";
            for($j = 0;$j<strlen($str);$j++){
                $strs  .= mb_substr($str,$j,1,"utf-8");
                $strs  .= "%";
            }
            $company = new indexModel();
            $con = $company->where("name","like","$strs")->limit(0,45)->select();


            $cons ="";
            if(!empty($con)){
                $num = 1;
                for($i = 0;$i<count($con);$i++){

                    $cons .= $num."、".$con[$i]->name."\n";
                    $num++;
                }
            }else{
                $cons = "暂无数据";
            }
            echo sprintf(self::$Text_template, $toUser, $fromUser, $time, $msgType, $cons);
        }
    }
}

