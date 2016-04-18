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
    $Groups = cache(C('tmp_CLI_FD_GROUP'),false,false,[]);
    if(null===$GroupName&&null===$fd){
//        使用两个null来初始化
        cache(C('tmp_CLI_FD_GROUP'),[]);
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
    cache(C('tmp_CLI_FD_GROUP'),$Groups);
    return true;
}

/**
 * 给链接赋值
 * @param bool $name
 * @return mixed
 */
function fd_name($name=false){
    $fdName = cache('tmp_fd_name');
    if(false===$name){
        return isset($fdName[$_GET['_fd']])?$fdName[$_GET['_fd']]:$_GET['_fd'];
    }
    if(null===$name){
        unset($fdName[$_GET['_fd']]);
    }else{
        $fdName[$_GET['_fd']]=$name;
    }
    cache('tmp_fd_name',$fdName);
}

/**
 * Socket推送，
 * @param string $name 推送的目标名称，需要用fd_name设定，如果未设定则是当前连接
 * @param string|array $value 推送内容，可以是数组也可以是字符串，会经过通道code输出
 * @param bool $online 是否必须要当前连接在线才推送，默认为是
 */
function push($name,$value,$online=true){
    $fdName = cache('tmp_fd_name');
    if(!is_array($fdName)){$fdName=[];}
    //获取所有映射关系
    if($fd = array_search($name,$fdName)){
        swoole_out_check($fd,$value);
    }else{
        if(!$online){
            //TODO 处理不在线的情况
        }
        return false;
    }
}

/**
 * 往某个端口上所有连接广播信息
 * @param $Port
 * @param $value
 */
function broadcast($Port,$value){
    $Group = port_group($Port);
    $Type = swoole_get_port_property($Port,'TYPE');
    $Class = swoole_get_mode_class($Type);
    foreach ($Group as $fd){
        swoole_send($fd,$Class->code($value));
    }
}

/**
 * 按照连接端口对连接进行分组
 * @param $port
 * @param bool $fd
 * @return array|bool
 */
function port_group($port,$fd=false){
    if(false===$fd){
        $g = cache('tmp_port_group'.$port);
        return is_array($g)?$g:[];
    }elseif(null===$fd){
        $g = cache('tmp_port_group'.$port);
        $g = is_array($g)?$g:[];
        if($k = array_search($_GET['_fd'],$g)){
            unset($g[$k]);
        }
        cache('tmp_port_group'.$port,$fd);
    }else{
        $g = cache('tmp_port_group'.$port);
        $g = is_array($g)?$g:[];
        $g[]=$fd;
        cache('tmp_port_group'.$port,$g);
    }
}

function swoole_in_check($fd,$data){
    $info = swoole_connect_info($fd);
    if(false===$info){return false;}
    $Port = $info['server_port'];
    $Type = swoole_get_port_property($Port,'TYPE');

    $Class = swoole_get_mode_class($Type);
    if(swoole_receive($_GET['_fd'])==1){
//        第一次，做通道协议检测
        if($HandData = $Class->handshake($data)){
            //响应握手协议
            L('握手响应:'.$HandData);
            swoole_send($fd,$HandData);
            return false;
        }
    }
    //            解码协议，
    $data = $Class->uncode($data);
    $_GET['_str']=$data;
    if(false===$data){return;}
    $Data=[
        'i'=>'Empty/_empty',
        'd'=>$data,
        't'=>''
    ];
//            实例化Controller
    $Dispatch = swoole_get_port_property($Port,'DISPATCH');
    if(is_callable($Dispatch)){
        $tmpData = call_user_func($Dispatch,$data);
        $Data = is_array($tmpData)?array_merge($Data,$tmpData):$Data;
    }
    return $Data;
}

/**
 * Swoole通信桥检测
 * @param $fd
 * @param $Data
 * @return bool
 */
function swoole_bridge_check($fd,$Data){
    //-----------------------------------------
    //开始进行t值检测，做桥链接处理
    //        生成mid
    $_POST['_mid']=uniqid();
    $Data['m']=$_POST['_mid'];
    //            响应检测
    $_POST['_i']=$Data['i'];
    if($Data['t']){
//            链接桥响应,此处要应用通道编码,通道编码之前要有协议编码
        $SendData = [
            't'=>$Data['t'],
            'm'=>$Data['m']
        ];
        $info = swoole_connect_info($fd);
        if(false===$info){return false;}
        $Port = $info['server_port'];
        $Type = swoole_get_port_property($Port,'TYPE');
        $Bridge = swoole_get_port_property($Port,'BRIDGE');
//            响应桥请求
        $BridgeData=is_callable($Bridge)?call_user_func($Bridge,$SendData):'';
        $Class = swoole_get_mode_class($Type);
        if(is_string($BridgeData)&&strlen($BridgeData)>0){
            swoole_send($fd,$Class->code($BridgeData));
        }
    }
}

/**
 * Swoole输出时的内容检测，
 * @param $fd
 * @param $data
 * @return bool
 */
function swoole_out_check($fd,$data){
    $info = swoole_connect_info($fd);
    if(false===$info){return false;}
    $Port = $info['server_port'];
    $Type = swoole_get_port_property($Port,'TYPE');
    $Out = swoole_get_port_property($Port,'OUT');
    //返回内容检测
    $Class = swoole_get_mode_class($Type);
    $OutData=is_callable($Out)?call_user_func($Out,$data):'';
    if(is_string($OutData)&&strlen($OutData)>0){
        swoole_send($fd,$Class->code($OutData));
    }
}

function swoole_connect_check(\swoole_server $server,$info,$fd){
    $Connect = swoole_get_port_property($info['server_port'],'CONNECT');
    if(is_callable($Connect)){
        call_user_func_array($Connect,[$server,$info,$fd]);
    }
}
function swoole_close_check(\swoole_server $server,$info,$fd){
    $Close = swoole_get_port_property($info['server_port'],'CLOSE');
    if(is_callable($Close)){
        call_user_func_array($Close,[$server,$info,$fd]);
    }
}

function swoole_get_port_property($Port,$Property){
    return isset($GLOBALS['_PortModeMap'][$Port])&&isset($GLOBALS['_PortModeMap'][$Port][$Property])?$GLOBALS['_PortModeMap'][$Port][$Property]:null;
}
/**
 * @param int $fd 链接标识符
 * @return array
 */
function swoole_connect_info($fd){
    return $GLOBALS['_SWOOLE']->connection_info($fd);
}
function swoole_send($fd,$str){
    $GLOBALS['_SWOOLE']->send($fd,$str);
    if(isset($_REQUEST['_close'])&&$_REQUEST['_close']===true){
        $GLOBALS['_SWOOLE']->close($fd);
        $_REQUEST['_close']=false;
    }
}
/**
 * @param bool $fd
 * @return int|string
 */
function swoole_receive($fd=false){
    if(null===$fd){
        //删除该链接的计数缓存
        cache('tmp_swoole_receive_count_'.$_GET['_fd'],null);
    }elseif($fd){
        //返回接受次数
        $count =  cache('tmp_swoole_receive_count_'.$_GET['_fd']);
        return is_numeric($count)?$count:0;
    }else{
//        计数+1
        $count =  cache('tmp_swoole_receive_count_'.$_GET['_fd']);
        cache('tmp_swoole_receive_count_'.$_GET['_fd'],is_numeric($count)?$count+1:1);
    }
}

function swoole_get_mode_class($mode){
    static $mode_class=[];
    if(!isset($mode_class[$mode])&&$mode){
        $class='Tsy\\Library\\Swoole\\'.$mode;
        $mode_class[$mode]=new $class();
    }
    return $mode_class[$mode];
}