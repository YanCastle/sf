微信支付配置：
```php
'WXPAY'=>[
         'NOTIFY_URL'=>'',//回调地址
         'APPID'=>'',//微信APPID
         'MCHID'=>'',//微信商户编号
         'KEY'=>'',//微信密钥
         'APPSECRET'=>'',//应用密钥
         'SSLCERT_PATH'=>CONF_PATH.'/application_cert.pem',//证书文件
         'SSLKEY_PATH'=>CONF_PATH.'/application_key.pem',//证书文件
         'CURL_PROXY_HOST'=>'0.0.0.0',//CURL代理
         'CURL_PROXY_PORT'=>'0',//
         'REPORT_LEVENL'=>APP_DEBUG?2:0,
         'NOTIFY_CALLBACK'=>''//回掉函数
     ],
```

回调函数参数：
```php
/**
     * 支付回调
     * @param int $OrderID 订单编号
     * @param double $Money 订单金额
     * @param string $Type 支付类型
     * @param int $PayID 支付系统的支付订单编号
     * @param array $Data 支付系统返回的所有数据，请做差异性处理
     */
    public static function wxpay($OrderID,$Money,$Type,$PayID,$Data){

    }
```
