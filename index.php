<?php
/**
 * Created by PhpStorm.
 * User: liangyaohe
 * Date: 2017/7/12
 * Time: 上午12:51
 */

require('pds.php');

if(empty($_GET['url'])){
    echo "Request: index.php?url=http://example.com <br>";
    echo "请求链接: index.php?url=http://example.com <br>";
    exit();
}
$url = $_GET['url'];

list($header, $contents, $status) = pds::request($url);
//var_dump($status);exit();
//echo $header;exit();

if(strpos($header, 'HTTP/1.1 302') !== false || strpos($header, 'HTTP/1.1 301') !== false){
    header("location:index.php?url=" . $status['url']);
    exit();
}

if($status['http_code'] == 200){
    $header_arr = isset($header) ? preg_split( '/[\r\n]+/', $header ) : array();
    // Propagate headers to response.
    foreach ( $header_arr as $header_str ) {
        header($header_str);
    }

    echo $contents;

}else{
    echo "<h1>ERROR</h1>";
    var_dump($status);
}