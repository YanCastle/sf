<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/13
 * Time: 21:36
 */
function load_module_config($module){
    //清空配置缓存
    C(false,false);
    //加载公共配置
    C($GLOBALS['Config']);
    $ModuleConfigPath = APP_PATH.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.'Config/';
//        加载项目配置文件,http模式则加载http.php,swoole模式则加载swoole.php
    C(load_config($ModuleConfigPath.'config.php'));
    !APP_DEBUG or C(load_config($ModuleConfigPath.'debug.php'));
    C(load_config($ModuleConfigPath.strtolower(APP_MODE).'.php'));
    !APP_DEBUG or C(load_config($ModuleConfigPath.strtolower(APP_MODE).'_debug.php'));
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name=null, $value=null,$default=null) {
    static $_config=[];
    // 无参数时获取所有
//    if(!isset($_config)){$_config=[];}
    if (empty($name)) {
        return $_config;
    }
    if(false===$name&&$value===false){
        $_config=[];
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : $default;
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        $_config[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}

/**
 * 加载配置文件 支持格式转换 仅支持一级配置
 * @param string $file 配置文件名
 * @param string $parse 配置解析方法 有些格式需要用户自己解析
 * @return array
 */
function load_config($file,$parse='php'){
    $ext  = pathinfo($file,PATHINFO_EXTENSION);
    switch($ext){
        case 'php':
            return include $file;
        case 'ini':
            return parse_ini_file($file);
        case 'yaml':
            return yaml_parse_file($file);
        case 'xml':
            return (array)simplexml_load_file($file);
        case 'json':
            return json_decode(file_get_contents($file), true);
        default:
            if(function_exists($parse)){
                return $parse($file);
            }else{
                E(L('_NOT_SUPPORT_').':'.$ext);
            }
    }
}

function controller($i,$data,$mid){
    $ModuleClassAction=explode('/',$i);
    $MCACount = count($ModuleClassAction);
    if($MCACount==2){
        list($C,$A)=$ModuleClassAction;
        $M=DEFAULT_MODULE;
    }elseif($MCACount==3){
        list($M,$C,$A)=$ModuleClassAction;
    }else{
        log($i.'错误',LOG_ERR);
        return null;
    }
//    判断配置文件是否是当前模块配置文件，如果不是则加载当前模块配置文件
    if(isset($GLOBALS['CurrentModule'])&&$GLOBALS['CurrentModule']==$M){

    }else{
        $GLOBALS['CurrentModule']=$M;
        load_module_config($M);
    }
//    如果要切换配置需要先还原Common配置再加载需要加载的模块配置文件
    $ClassName = implode('\\',[$M,'Controller',$C.'Controller']);
    if(!class_exists($ClassName)){
        $ClassName=str_replace($C,'Empty',$ClassName);
        if(!class_exists($ClassName)){
            log($C.'类不存在',LOG_ERR);
            return null;
        }
    }
    $result = '';//返回结果
    $Class = new $ClassName();
    if(!method_exists($Class,$A)){
        //方法不存在
        if(method_exists($Class,'_empty')){
            $result = call_user_func_array([$Class,'_empty'],[$i,$data]);
        }elseif(method_exists($Class,'__call')){
            $result = call_user_func_array([$Class,$A],$data);
        }else{
            L($A.'方法不存在',LOG_ERR);
            return null;
        }
        return $result;
    }
    //方法存在时
    $ReflectMethod = new ReflectionMethod($Class,$A);
    //获取方法参数
    if($ReflectMethod->isPublic()){
//        是否需要参数绑定
        if($ReflectMethod->getNumberOfParameters()>0){
            $args = [];
            foreach ($ReflectMethod->getParameters() as $Param){
                $ParamName=$Param->getName();
                if(isset($data[$ParamName])){
                    $args[]=$data[$ParamName];
                }elseif($Param->isDefaultValueAvailable()){
                    $args[]=$Param->getDefaultValue();
                }else{
                    //必填参数未传入完整
                    L($ParamName.':必填参数未传入完整',LOG_ERR);
                    return null;
                }
            }
            $result = $ReflectMethod->invokeArgs($Class,$args);
        }else{
            $result = $ReflectMethod->invoke($Class);
        }
//        TODO 判断result内容
    }else{
        L($i.'方法不是公共方法',LOG_ERR);
    }
    return $result;
}

function E($msg){
    L($msg);
}

function L($msg,$Type=0){
    //TODO 完善log函数
    echo $msg,"\r\n";
}