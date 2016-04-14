<?php
//CLI 模式
//要基于外部文件缓存对链接编号进行分组
/**
 * CLI模式下的链接标识符分组
 * @param bool $GroupName
 * @param bool $fd
 * @param bool $del
 * @return array|bool|string
 */
function cli_fd_group($GroupName=false,$fd=false,$del=false){
//    Group => fd
    $Groups = cache(C('CLI_FD_GROUP'),false,false,[]);
    if(null===$GroupName&&null===$fd){
//        使用两个null来初始化
        cache(C('CLI_FD_GROUP'),[]);
    }
    if(false===$GroupName){
//        返回全部已定义的组
        return $Groups;
    }
    if($GroupName&&$fd&&$del){
//        删除这个分组的这个东西
        if(isset($Groups[$GroupName])&&is_array($Groups[$GroupName])){
            if($key = array_search($fd,$Groups[$GroupName])){
                unset($Groups[$GroupName][$key]);
            }
        }else{
            $Groups[$GroupName]=[];
        }
    }
    if(false===$fd&&!$del){
        $return=[];
        if(is_array($GroupName)){
            foreach ($GroupName as $GN){
                $return[$GN]=isset($Groups[$GN])?$Groups[$GN]:[];
            }
        }else{
            $return = isset($Groups[$GroupName])?$Groups[$GroupName]:[];
        }
        return $return;
    }
    if(null===$fd&&!$del){
        //删除这个分组
        if(isset($Groups[$GroupName])){
            unset($Groups[$GroupName]);
        }
    }
    cache(C('CLI_FD_GROUP'),$Groups);
    return true;
}