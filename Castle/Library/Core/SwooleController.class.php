<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 1/14/16
 * Time: 4:34 PM
 */

namespace Core;


class SwooleController
{
    function Task(\swoole_server $server, int $task_id, int $from_id, string $data){}
    function Start(\swoole_server $server){}
    function Shutdown(\swoole_server $server){}
    function WorkerStart(\swoole_server $server,$worker_id){}
    function WorkerStop(\swoole_server $server,$worker_id){}
    function Timer(\swoole_server $server,$interval){}
    function Finish(\swoole_server $server, $task_id, $data){}
    function PipeMessage(\swoole_server $server, int $from_worker_id, string $message){}
    function ManagerStart(\swoole_server $server, int $worker_id, int $worker_pid, int $exit_code){}
    function ManagerStop(\swoole_server $server){}
    function WorkerError(\swoole_server $server){}
    function Connect(\swoole_server $server,int $fd,int $from_id){}
    function Close(\swoole_server $server,int $fd,int $from_id){}
    function Packet(\swoole_server $server,$data,$client_info){}

    /**
     * 收到的回调，存在则调，不存在则直接按照协议解析到Controller中，
     * @param \swoole_server $server
     * @param int $fd
     * @param int $form_id
     * @param string $data
     */
    function Receive(\swoole_server $server,int $fd,int $form_id,string $data){}
}