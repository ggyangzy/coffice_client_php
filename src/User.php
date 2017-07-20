<?php
namespace yzyblog\coffice_client_php;

Class User
{

    protected static $g_cStaticInstance;

    static function mGetInstance()
    {

        if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
        {

            self::$g_cStaticInstance = new self();

        }

        return self::$g_cStaticInstance;

    }


    /**
     * 注册
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function users( $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'post',
            'sUrl'      =>  Client::getSUrl().'/class/user',
            'arrData'   =>  array_merge( $arrInputData, Client::getDefaultInit() ),
        ];

        return Query::request( $arrRequest, $arrRtn, $sDesc );
    }


    /**
     * 登陆
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function login( $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'get',
            'sUrl'      =>  Client::getSUrl().'/class/user',
            'arrData'   =>  array_merge( $arrInputData, Client::getDefaultInit() ),
        ];

        return Query::request( $arrRequest, $arrRtn, $sDesc );
    }


    /**
     * 用户信息
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function info( $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'get',
            'sUrl'      =>  Client::getSUrl().'/class/user/'.@$_COOKIE['user_id'],
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit() ),
        ];

        return Query::request( $arrRequest, $arrRtn, $sDesc );
    }


    /**
     * 修改密码
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function rePassword( $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'post',
            'sUrl'      =>  Client::getSUrl().'/class/user/'.@$_COOKIE['user_id'],
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit(), ['_method'=>'put'] ),
        ];

        return Query::request( $arrRequest, $arrRtn, $sDesc );
    }

}