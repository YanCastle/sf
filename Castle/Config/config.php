<?php
/**
 * Created by PhpStorm.
 * User: castle
 * Date: 2015/12/17
 * Time: 12:28
 */
return [
    'LIB_PATH'=>C('CASTLE_PATH').'/Library',//ϵͳĿ¼
    'EXT'=>'.class.php',
    'IS_CLI'=>PHP_SAPI=='cli'? 1   :   0,
    'IS_WIN'=>strstr(PHP_OS, 'WIN') ? 1 : 0,
    'IS_CGI'=>(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0,
    'MEMORY_LIMIT_ON'=>function_exists('memory_get_usage'),
];