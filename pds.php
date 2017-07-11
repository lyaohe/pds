<?php

/**
 * Created by PhpStorm.
 * User: liangyaohe
 * Date: 2017/7/12
 * Time: 上午12:11
 */
class pds
{
    static public function request($url, $user_agent=''){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ) {
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST );
        }

        $cookie = array();
        foreach ( $_COOKIE as $key => $value ) {
            $cookie[] = $key . '=' . $value;
        }
        $cookie = implode( '; ', $cookie );

        curl_setopt( $ch, CURLOPT_COOKIE, $cookie );

        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_HEADER, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent != '' ? $user_agent : $_SERVER['HTTP_USER_AGENT'] );

        list( $header, $contents ) = preg_split( '/([\r\n][\r\n])\\1/', curl_exec( $ch ), 2 );
        //$contents = str_replace(array("\n", "\r"), '', $contents);
        $status = curl_getinfo( $ch );

        curl_close( $ch );

        return array($header, $contents, $status);
    }

}