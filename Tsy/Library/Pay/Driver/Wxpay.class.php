<?php
/**
 * Created by PhpStorm.
 * User: 鄢鹏权
 * Date: 2016/11/21
 * Time: 15:56
 */
namespace Tsy\Library\Pay\Driver;
use Tsy\Library\Pay\PayIFace;

class Wxpay implements PayIFace
{
    const WXPAY_QR='QRCode';
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
        require_once $this->WX_SDK_DIR."WxPayDataBase.class.php";
        require_once $this->WX_SDK_DIR."WxPayNotify.class.php";
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
    function notify(){}
    /**
     * 支付
     * @param $OrderID
     * @param $Name
     * @param $Money
     * @param string $Memo
     */
    function pay($Type,$OrderID,$Name,$Money,$Memo=''){
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
     * @return mixed
     */
    function payQRCode($OrderID,$Name,$Money,$Memo=''){
        require_once $this->WX_SDK_DIR."WxPayMicroPay.class.php";
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($Name);
        $input->SetAttach($Name);
        $input->SetOut_trade_no(uniqid($OrderID.'_'));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($Name);
        $input->SetNotify_url("http://www.houqin.swust.edu.cn/wechat/index.php?s=Printer/Order/WxpayNotify&XDEBUG_SESSION_START=12520");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        return $url2;
    }
}