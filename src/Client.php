<?php
namespace yzyblog\coffice_client_php;

Class Client
{
    private static $appId;

    private static $appKey;

    private static $appMasterKey;

    private static $sUrl;

    private static $defaultInit;

    public static function initialize( $sUrl, $appId = '', $appKey = '', $appMasterKey = '' )
    {
        self::$sUrl         = $sUrl;
        self::$appId        = $appId;
        self::$appKey       = $appKey;
        self::$appMasterKey = $appMasterKey;

        self::$defaultInit = array(
            'app_id'    => $appId,
            'app_sign'  => self::verifySign()
        );
    }


    public static function initMaster()
    {
        self::$defaultInit = array(
            'app_id'    => self::$appId,
            'app_sign'  => self::verifySign( true )
        );
    }


    /**
     * app_sign 加密校验
     * @param $timestamp
     * @return string
     */
    private static function verifySign( $bFlag = false )
    {
        $timestamp = time();
        $key       = self::$appKey;
        if( $bFlag )
        {
            $key       = self::$appMasterKey ?: self::$appKey;
        }
        $sign      = md5( $timestamp . $key );
        $sign     .= "," . $timestamp;

        if ( $bFlag )
        {
            $sign .= ",master";
        }

        return $sign;
    }

    /**
     * @return mixed
     */
    public static function getSUrl()
    {
        return self::$sUrl;
    }

    /**
     * @return mixed
     */
    public static function getDefaultInit()
    {
        return self::$defaultInit;
    }



}