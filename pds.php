<?php

/**
 * Created by PhpStorm.
 * User: liangyaohe
 * Date: 2017/7/12
 * Time: 上午12:11
 */

/**
 * Pds(Proxy Download Server) 代理下载服务器
 */
class Pds
{
    static public $debug = false;

    static private $filterHeader = [
        'Transfer-Encoding',
    ];

    static public function request($url, $debug = false){
        self::$debug = $debug;

        $ch = curl_init($url);

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
            curl_setopt( $ch, CURLOPT_POST, true );

            if(count($_POST) > 0){
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  $_POST);
            }else{
                $input = file_get_contents('php://input');
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  $input);
                if(isset($_SERVER['CONTENT_TYPE'])) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length: ' . strlen($input))
                    );
                }
            }
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);//在启用CURLOPT_RETURNTRANSFER的时候，返回原生的（Raw）输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);//启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器
        curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // HTTP_USER_AGENT
        curl_setopt($ch, CURLOPT_HEADER, true); //输出header
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);//在发起连接前等待的时间，如果设置为0，则无限等待
        curl_setopt($ch, CURLOPT_TIMEOUT, 60*60*2);//设置cURL允许执行的最长秒数
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 20971520);//每次获取的数据中读入缓存的大小，但是不保证这个值每次都会被填满

        $flag=0;
        //回调函数名。该函数应接受两个参数。第一个是 cURL resource；第二个是要写入的数据字符串
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch ,$str) use (&$flag){
            //$str 可以简单理解成每次回调，取一行数据,但超过缓存区设置大小也会被截取
            $len = strlen($str);

            //echo "len:{$len};str:{$str}";

            switch ($flag)
            {
                case 0:

                    if(strpos($str, 'HTTP/1.1 200') !== false ||
                        strpos($str, 'HTTP/1.1 404') !== false || strpos($str, 'HTTP/1.1 500') !== false){
                        // todo 这个判断条件可以优化
                        //当标志位flag == 0, 且找到 (HTTP/1.1 200 || HTTP/1.1 404 || HTTP/1.1 500)，标志位flag改为1
                        self::pdsHeader($str);
                        $flag = 1;
                    }

                    break;
                case 1:
                    if($len > 2){  //当标志位flag == 1, 且 len > 2,是 header 字符串
                        self::pdsHeader($str);
                    }else{
                        // header与body之间有两个回车换行，所以有一行是"\r\n"，2个字符
                        // 表示 “\r\n” 必须要用双引号
                        if($str === "\r\n"){
                            $flag = 2; //标志位flag改为2，后面是正文内容
                        }
                    }
                    break;
                case 2:
                    self::pdsBody($str, $len);
                    break;
            }

            return $len;
        });

        curl_exec($ch);
        curl_close($ch);
    }

    private static function pdsHeader($headerStr){
        foreach(self::$filterHeader as $filterStr){
            if(strpos($headerStr, $filterStr) !== false){
                return;
            }
        }
        if (self::$debug) {
            echo "header: {$headerStr}<br>";
        } else {
            header($headerStr);
        }

    }

    private static function pdsBody($bodyStr, $len){
        if(self::$debug){
            echo "body: {$len}<br>";
        }else{
            echo $bodyStr; // 输出内容 body
        }
    }
}



if(empty($_GET['url'])){
    echo "Request: pds.php?url=http://example.com <br>";
    echo "请求链接: pds.php?url=http://example.com <br>";
    exit();
}
$debug = empty($_GET['debug']) ? false : true;
$url = $_GET['url'];

Pds::request($url, $debug);