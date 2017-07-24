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
        "Transfer-Encoding: chunked\r\n",
    ];

    static public function request($url, $debug = false, $host = ''){
        self::$debug = $debug;
        $httpHeaderArr = [];

        if(!empty($host)){
            $httpHeaderArr[] = 'Host: ' . $host;
        }

        $ch = curl_init($url);

        // Authorization
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "{$_SERVER['PHP_AUTH_USER']}:{$_SERVER['PHP_AUTH_PW']}");
        }

        // set cookie
        if(isset($_SERVER['HTTP_SET_COOKIE'])){
            curl_setopt ($ch, CURLOPT_COOKIE , $_SERVER['HTTP_SET_COOKIE']);
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' ) {
            curl_setopt( $ch, CURLOPT_POST, true );


            if(isset($_SERVER['CONTENT_TYPE'])) {
                $httpHeaderArr[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
            }
            if(count($_POST) > 0){
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  $_POST);
            }else{
                $input = file_get_contents('php://input');
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  $input);
                $httpHeaderArr[] = 'Content-Length: ' . strlen($input);
            }
        }
        if(count($httpHeaderArr) > 0){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaderArr);
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
        //$status = curl_getinfo($ch);
        //var_dump($status);

        curl_close($ch);
    }

    private static function pdsHeader($headerStr){
        if(in_array($headerStr, self::$filterHeader)){
            return;
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

// 获取URL参数，支持两种方式传参
$url = '';
if(isset($_GET['url'])){
    $url = $_GET['url'];
}else{
    //var_dump($_SERVER);
    $request_url = $_SERVER['REQUEST_URI'];
    if(strpos($request_url, '/u/') !== false){
        list( $u, $url ) = preg_split( '/u\//', $request_url, 2 );
    }else{
        echo "Request: pds.php?url=http://example.com <br>";
        echo "请求链接: pds.php?url=http://example.com <br>";
        exit();
    }
}
// 提取Host参数，不需要可以去掉
$host = '';
if(strpos($url, '://') !== false){
    preg_match("/:\/\/(.*?)\//", $url, $arr);
    if(!empty($arr[1])){
        $host = $arr[1];
    }
}

// 调试参数，仅支持第一种传参方式
$debug = empty($_GET['debug']) ? false : true;
//$debug = true;

Pds::request($url, $debug, $host);