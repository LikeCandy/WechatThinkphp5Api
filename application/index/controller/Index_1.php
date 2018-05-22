<?php

namespace app\index\controller;
use app\index\model\Index as indexModel;

//use mikkle\tp_wechat\Wechat;
use think\Controller;

class Index_1 extends Controller
{
    function index(){
        $str = "团";
        $strs = "%";
        for($j = 0;$j<strlen($str);$j++){
            $strs  .= mb_substr($str,$j,1,"utf-8");
            $strs  .= "%";
        }
        $company = new indexModel();

        $con = $company->where("name","like","$strs")->limit(0,30)->select();

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
        dump($cons);

    }
}

