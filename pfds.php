<?php
/**
 * Created by PhpStorm.
 * User: liangyaohe
 * Date: 2017/7/13
 * Time: 上午12:39
 */

/**
 * pfds - PHP File Download Server PHP文件下载服务
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
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch ,$str) use (&$flag){
        //$str 可以简单理解成每次回调，取一行数据
        $len = strlen($str);

        switch ($flag)
        {
            case 0:
                if(strpos($str, 'HTTP/1.1 200') !== false || strpos($str, 'HTTP/1.1 404') !== false || strpos($str, 'HTTP/1.1 500') !== false){
                    //当标志位flag == 0, 且找到 (HTTP/1.1 200 || HTTP/1.1 404 || HTTP/1.1 500)，标志位flag改为1
                    $flag = 1;
                }
                break;
            case 1:
                if($len > 2){  //当标志位flag == 1, 且 len > 2,是 header 字符串
                    header($str);
                }else{
                    // header与body之间有两个回车换行，所以有一行是"\r\n"，2个字符
                    // 表示 “\r\n” 必须要用双引号
                    if($str === "\r\n"){
                        $flag = 2; //标志位flag改为2，后面是正文内容
                    }
                }
                break;
            case 2:
                echo $str; // 输出内容 body
                break;
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