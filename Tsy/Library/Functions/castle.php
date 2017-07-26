<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2017/7/25
 * Time: 21:54
 */
function generate_htmljs($HTMLDir,$OutputJS){
    $HTMLMap=[];
    $Path = $HTMLDir;
    if(substr($Path,-1)!='/')$Path.='/';
    each_dir($Path,null,function ($path)use(&$HTMLMap,$Path){
        if(substr($path,-5)=='.html'){
            $Content = str_replace([
                '  ',"\r\n",
            ],' ',file_get_contents($path));
            $path = str_replace($Path,'',$path);
            $pathInfo = pathinfo($path);
            $Dir =$Obj=$Type= '';
            if($pathInfo['dirname']){
                $Dir=$pathInfo['dirname'];
            }
            list($Obj,$Type)=explode('_',$pathInfo['filename']);
            $HTMLMap[$Dir][$Obj][$Type]=$Content;
        }
    });
    $JSONStr = json_encode($HTMLMap,JSON_UNESCAPED_UNICODE);
    file_put_contents($OutputJS,"Config.HTMLMap = ".$JSONStr.";");
}
function generate_php_controller(){}