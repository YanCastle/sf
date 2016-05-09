<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/12
 * Time: 11:14
 */

namespace Tsy\Library\Swoole;


use Tsy\Library\Swoole;

class Websocket extends Swoole
{
    protected static $opcodes = array(
        'continuation' => 0,
        'text'         => 1,
        'binary'       => 2,
        'close'        => 8,
        'ping'         => 9,
        'pong'         => 10,
    );
    /**
     * WebSocket握手
     * @param $k
     * @param $buffer
     * @return bool
     */
    function handshake($buffer){
        if($pos = strpos($buffer,'Sec-WebSocket-Key:')){
            $buf  = substr($buffer,$pos+18);
            $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
            $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
            $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
            $new_message .= "Upgrade: websocket\r\n";
            $new_message .= "Sec-WebSocket-Version: 13\r\n";
            $new_message .= "Connection: Upgrade\r\n";
            $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
            return $new_message;
        }

    }

    function uncode($str){
        $mask = array();
        $data = '';
        $msg = unpack('H*',$str);
        $head = substr($msg[1],0,2);
        if (hexdec($head{1}) === 8) {
            $data = false;
        }else if (hexdec($head{1}) === 1){
            $mask[] = hexdec(substr($msg[1],4,2));
            $mask[] = hexdec(substr($msg[1],6,2));
            $mask[] = hexdec(substr($msg[1],8,2));
            $mask[] = hexdec(substr($msg[1],10,2));

            $s = 12;
            $e = strlen($msg[1])-2;
            $n = 0;
            for ($i=$s; $i<= $e; $i+= 2) {
                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));
                $n++;
            }
        }
        return $data;
    }

    function code($message) {
        $messageType='text';
        switch ($messageType) {
            case 'continuous':
                $b1 = 0;
                break;
            case 'text':
                $b1 = 1;
                break;
            case 'binary':
                $b1 = 2;
                break;
            case 'close':
                $b1 = 8;
                break;
            case 'ping':
                $b1 = 9;
                break;
            case 'pong':
                $b1 = 10;
                break;
        }
        $b1+=128;
        $length = strlen($message);
        $lengthField = "";
        if ($length < 126) {
            $b2 = $length;
        }
        elseif ($length < 65536) {
            $b2 = 126;
            $hexLength = dechex($length);
            //$this->stdout("Hex Length: $hexLength");
            if (strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for ($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 2) {
                $lengthField = chr(0) . $lengthField;
            }
        }
        else {
            $b2 = 127;
            $hexLength = dechex($length);
            if (strlen($hexLength)%2 == 1) {
                $hexLength = '0' . $hexLength;
            }
            $n = strlen($hexLength) - 2;
            for ($i = $n; $i >= 0; $i=$i-2) {
                $lengthField = chr(hexdec(substr($hexLength, $i, 2))) . $lengthField;
            }
            while (strlen($lengthField) < 8) {
                $lengthField = chr(0) . $lengthField;
            }
        }
        return chr($b1) . chr($b2) . $lengthField . $message;
    }
}