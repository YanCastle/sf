<?php

require_once '../libs/BaiduPCS.class.php';
//请根据实际情况更新$access_token与$appName参数
$access_token = '3.839af46f54db6ed60797847d2febbca0.2592000.1359262544.754976761-248414';

//离线下载任务Id
$taskId = 2462;

$pcs = new BaiduPCS($access_token);
$result = $pcs->cancelOfflineDownloadTask($taskId);
echo $result;
?>