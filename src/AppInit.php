<?php
namespace yzyblog\coffice_client_php;

Class AppInit
{
    public static function appInit(  & $arrRtn, & $sDesc, $dbs = '')
    {
        if( '' != $dbs )
        {
            $dbs = '/'.$dbs;
        }
        $arrRequest = [
            'sMethod'   =>  'get',
            'sUrl'      =>  Client::getSUrl().'/coffice/init'.$dbs,
            'arrData'   =>  Client::getDefaultInit(),
        ];

        return Query::request( $arrRequest, $arrRtn, $sDesc );
    }
}