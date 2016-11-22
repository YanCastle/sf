<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/11/21
 * Time: 15:56
 */
namespace Tsy\Library\Pay\Driver;
use Tsy\Library\Pay\PayIFace;
use Tsy\Tsy;
require_once __DIR__.'/Wxpay/WxPayDataBase.class.php';
class Wxpay extends \WxPayNotifyReply implements PayIFace
{
    const WXPAY_QR='QRCode';

    const TRADE_TYPE_JSAPI='JSAPI';
    const TRADE_TYPE_NATIVE='NATIVE';
    const TRADE_TYPE_APP='APP';
    public $error='';
    static public $NOTIFY_URL='';
    static public $APPID='';
    static public $MCHID='';
    static public $KEY='';
    static public $APPSECRET='';
    static public $SSLCERT_PATH='';
    static public $SSLKEY_PATH='';
    static public $CURL_PROXY_HOST='';
    static public $CURL_PROXY_PORT='';
    static public $REPORT_LEVENL='';
    protected $WX_SDK_DIR="";
    function __construct($Type,$Config=[])
    {
        $this->WX_SDK_DIR=__DIR__.'/Wxpay/';
        require_once $this->WX_SDK_DIR."WxPayException.class.php";
//        require_once $this->WX_SDK_DIR."WxPayDataBase.class.php";
//        require_once $this->WX_SDK_DIR."WxPayNotify.class.php";
        require_once $this->WX_SDK_DIR."WxPayApi.class.php";
//        require_once $this->WX_SDK_DIR."WxPayJsApiPay.class.php";
//        require_once $this->WX_SDK_DIR."WxPayNativePay.class.php";
//        require_once $this->WX_SDK_DIR."WxPayMicroPay.class.php";
        $this->config($Config);
    }
    function config($Config=[]){
        foreach ([
            'NOTIFY_URL',
            'APPID',
            'MCHID',
            'KEY',
            'APPSECRET',
            'SSLCERT_PATH',
            'SSLKEY_PATH',
            'CURL_PROXY_HOST',
            'CURL_PROXY_PORT',
            'REPORT_LEVENL',
                 ] as $key){
            self::$$key=isset($Config[$key])?$Config[$key]:C('WXPAY.'.$key);
        }
    }
    /**
     * 异步回调通知
     * @param callable $success
     * @param callable $finish
     * @param callable|null $fail
     * @return mixed
     */
    function notify($success,$finish,$fail){
        Tsy::$Out=false;
        $msg = 'OK';
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA'])&&$GLOBALS['HTTP_RAW_POST_DATA']?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents('php://input');
        try {
            $result = \WxPayResults::Init($xml);
        } catch (\WxPayException $e){
            $this->error = $e->errorMessage();
            return false;
        }
        if($result){
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
            file_put_contents('data',json_encode($result,JSON_UNESCAPED_UNICODE));
            $this->ReplyNotify(true);
        }else{
            $this->error=$msg;
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false);
            return false;
        }
    }
    function notifyCallback($data){
        return $data;
    }
    /**
     * 支付
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     */
    function pay($Type,$OrderID,$Name,$Money,$Memo='',$Config=[]){
        if(method_exists($this,'pay'.$Type)){
            return $this->payQRCode($OrderID,$Name,$Money,$Memo);
        }
    }

    /**
     * 需要将返回内容生成成二维码供微信扫码支付u
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     * @return mixed 返回用于生成二维码的相关数据
     */
    function payQRCode($OrderID,$Name,$Money,$Memo='',$Config=[]){
        require_once $this->WX_SDK_DIR."WxPayNativePay.class.php";
        $input = new \WxPayUnifiedOrder();
        foreach ([
            'Body',//商品或支付单的简要描述
            'Attach',//附加数据，再查询Api和支付通知中原样返回
            'Out_trade_no'=>'OutTradeNo',//设置商户系统内部的订单号，32个字符内、可包含字母, 其他说明见商户订单号
            'Total_fee'=>'TotalFee',//设置订单总金额，只能为整数，详见支付金额,单位为分
            'Time_start'=>'TimeStart',//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
            'Time_expire'=>'TimeExpire',//设置订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。其他详见时间规则
            'Goods_tag'=>'GoodsTag',//设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
             'Notify_url'=>'NotifyUrl',//设置接收微信支付异步通知回调地址
             'Trade_type'=>'TradeType',//设置取值如下：JSAPI，NATIVE，APP，详细说明见参数规定
             'Product_id'=>'ProductID',//设置trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
                 ] as $k=>$item){
            $Value=isset($Config[$item])?$Config[$item]:'';
            if(is_numeric($k))$k=$item;
            switch ($item){
                case 'Body':
                    $Value=$Name;
                    break;
                case 'OutTradeNo':
                    $Value = $OrderID.'_';
                    if(strlen($Value)<10){
                        $Value.=uniqid();
                    }
                    break;
                case 'TotalFee':
                    $Value=$Money;
                    if(!is_numeric($Value)||!is_float($Value)){
                        $this->error='错误的金额';
                        return false;
                    }
                    $Value *= 100;
//                    if(intval($Value)!=$Value){
//                        $this->error='错误的金额';
//                        return '错误的金额';
//                    }
                    break;
                case 'Attach':
                    if(!is_string($Value)){
                        $Value = serialize($Value);
                    }
                    break;
                case 'TimeStart':
                case 'TimeExpire':
                    if($Value==''){
                        if($item=='TimeStart'){
                            $Value = date("YmdHis");
                        }else{
                            $Value = date("YmdHis",time()+3200);
                        }
                    }
                    if(!is_numeric($Value)&&strlen($Value)!=10){
                        $this->error='错误的时间设定';
                        return false;
                    }
                    break;
                case 'TradeType':
                    if(!$Value)$Value=self::TRADE_TYPE_NATIVE;
                    if(!in_array($Value,[self::TRADE_TYPE_APP,self::TRADE_TYPE_JSAPI,self::TRADE_TYPE_NATIVE])){
                        $this->error='错误的支付方式';
                        return false;
                    }
                    break;
                case 'NotifyUrl':
                    $Value = $Value?$Value:self::$NOTIFY_URL;
                    if(!$Value){
                        $this->error='错误的回调地址';
                        return false;
                    }
                    break;
            }
            call_user_func([$input,"Set{$k}"],$Value);
        }
        $notify = new \NativePay();
        $result = $notify->GetPayUrl($input);
        return $result["code_url"];
    }
    /**
     *
     * 回复通知
     * @param bool $needSign 是否需要签名输出
     */
    final private function ReplyNotify($needSign = true)
    {
        //如果需要签名
        if($needSign == true &&
            $this->GetReturn_code() == "SUCCESS")
        {
            $this->SetSign();
        }
        \WxPayApi::replyNotify($this->ToXml());
    }
}
