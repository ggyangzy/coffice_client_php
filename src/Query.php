<?php
namespace yzyblog\coffice_client_php;

use dekuan\vdata\CRequest;
use dekuan\vdata\CVData;
use dekuan\delib\CLib;

Class Query
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
     * 查询所有记录
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function find( $class, $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'get',
            'sUrl'      =>  Client::getSUrl().'/class/'.$class,
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit() ),
        ];

        return self::request( $arrRequest, $arrRtn, $sDesc );
    }



    /**
     * 查询单条记录
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function show( $class, $objectId, $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'get',
            'sUrl'      =>  Client::getSUrl().'/class/'.$class.'/'.$objectId,
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit() ),
        ];

        return self::request( $arrRequest, $arrRtn, $sDesc );
    }



    /**
     * 添加记录
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function store( $class, $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'post',
            'sUrl'      =>  Client::getSUrl().'/class/'.$class,
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit() ),
        ];

        return self::request( $arrRequest, $arrRtn, $sDesc );
    }



    /**
     * 修改记录
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function reset( $class, $objectId, $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'post',
            'sUrl'      =>  Client::getSUrl().'/class/'.$class.'/'.$objectId,
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit(), ['_method'=>'put'] ),
        ];

        return self::request( $arrRequest, $arrRtn, $sDesc );
    }



    /**
     * 删除记录
     * @param $arrInputData
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public function remove( $class, $objectId, $arrInputData, & $arrRtn, & $sDesc )
    {
        $arrRequest = [
            'sMethod'   =>  'delete',
            'sUrl'      =>  Client::getSUrl().'/class/'.$class.'/'.$objectId,
            'arrData'   =>  array_merge( $arrInputData, $_COOKIE, Client::getDefaultInit() ),
        ];

        return self::request( $arrRequest, $arrRtn, $sDesc );
    }


    /**
     * @param $arrRequest
     * @param $arrRtn
     * @param $sDesc
     * @return mixed
     */
    public static function request( $arrParam, & $arrRtn, & $sDesc )
    {
        $arrResult = '';

        $nFlag = CRequest::GetInstance()->HttpRaw( [

            'method'	=> @$arrParam['sMethod'],
            'url'		=> @$arrParam['sUrl'],
            'data'		=> @$arrParam['arrData'],
            'version'	=> @$arrParam['sVersion'],
            'timeout'	=> @$arrParam['nTimeout'],
            'cookie'	=> @$arrParam['arrCookie'],
            'headers'	=> @$arrParam['arrHeaders']

        ], $arrResult );

        $arrData = CLib::GetVal( $arrResult, 'data', false, null );

        if( 0 == $nFlag )
        {
            $arrRtn = json_decode( $arrData, true );

            if( CVData::GetInstance()->IsValidVData( $arrRtn ) )
            {
                $nFlag  = $arrRtn['errorid'];
                $sDesc  = $arrRtn['errordesc'];
                $arrRtn = $arrRtn['vdata'];
            }
            else
            {
                if( CLib::IsExistingString( $arrRtn ) )
                {
                    $arrRtn = [ 'result' => $arrRtn ];
                }
            }
        }
        else
        {
            $sDesc = '请求失败';
        }

        return $nFlag;
    }
}