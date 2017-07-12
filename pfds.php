<?php
/**
 * Created by PhpStorm.
 * User: liangyaohe
 * Date: 2017/7/13
 * Time: 上午12:39
 */

/**
 * pfd - PHP File Download Server PHP文件下载服务
 * @param $url
 */
function pfds($url){
    $ch = curl_init($url);

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
    //$str 是取一行数据
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch ,$str) use (&$flag){
        $len = strlen($str);

        if($len == 0){ //header与body之间有两个回车换行，所以有一行是空字符串
            $flag = 1;
        }

        if($flag == 0){
            header($str);
        }else{
            echo $str;
        }
        return $len;
    });

    curl_exec($ch);
    curl_close($ch);
}

if(empty($_GET['url'])){
    echo "Request: pfds.php?url=http://example.com <br>";
    echo "请求链接: pdfs.php?url=http://example.com <br>";
    exit();
}
$url = $_GET['url'];

pfds($url);