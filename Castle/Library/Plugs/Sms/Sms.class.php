<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/11/25
 * Time: 12:25
 */
namespace Plugs\Sms;
use Core\Controller;
use Plugs\Arrays\Arrays;
use Plugs\Db\Db;
use Plugs\Model;
use Plugs\Sms\Model\SmsTemplateModel;

class Sms extends Controller{
    public $error=[];
    /**
     * @param array $to
     * @param $content
     * @param $data
     * @return bool
     */
    function send(array $to,$Driver=false,$content=false,$TemplateID=false){
//        $to = [
//            '133333333333','1561651651561','16516161'
//        ];
//        $to = [
//            '13333123132'=>[
//                'Name'=>'haha',
//                'Phone'=>'ff',
//            ]
//        ];
        if(!$content&&!$TemplateID){return false;}
        if(!$Driver){$Driver=C('DEFAULT_SMS_DRIVER');}
        if(!$Driver){return false;}
        $DriverTemplate=false;
        if($TemplateID&&!$content){
            $TemplateModel =new SmsTemplateModel();
            $Template = $TemplateModel->where(['TID'=>$TemplateID])->find();
            if(isset($Template['DriverTemplateID'])&&strlen($Template['DriverTemplateID'])){
                //驱动模板
                $DriverTemplate=$Template['DriverTemplateID'];
            }else{
                //本地模板
                $content = $Template['Content'];
            }
        }

        if(count($to)&&$content){
//            判断是否需要进行数据渲染
            switch(Arrays::getArrayLevel($to)){
                case 1:
                    //一维数据，只渲染一次,直接发送该内容，不做渲染
                    break;
                case 2:
                    //二位数组，需要渲染，渲染内容为值
                    foreach($to as $number=>$data){
                        if(!is_array($data)&&count($data)<1){$this->error[]=[$number=>$data];continue;}
                        $this->assign($data);
                        $sendContent = $this->fetch('',$content);
                        if($sendContent){
                            //TODO 发送
                            if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'Drivers'.DIRECTORY_SEPARATOR.$Driver.'.class.php')){

                            }
                        }else{
                            $this->error[]=[$number=>$data];continue;
                        }
                    }
                    break;
                default:
                    return false;
                    break;
            }
        }else{
            return false;
        }
        return true;
    }
    function build(){
        $Model = new Model();
        Db::build($Model,__DIR__.'/build.sql','',C('DB_PREFIX'));
    }
}