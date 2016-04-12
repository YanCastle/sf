<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 15:40
 */
session_id('sds');
session_start();
$_SESSION['s']=1;
session_write_close();
session_id('sfwaw');
session_start();
$_SESSION['s']=2;
session_write_close();
session_id('sds');
session_start();
echo $_SESSION['s'];
session_write_close();
session_id('sfwaw');
session_start();
echo $_SESSION['s'];
session_write_close();