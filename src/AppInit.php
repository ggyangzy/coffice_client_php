<?php

namespace App\Http\Coffice_sdk_php;

use dekuan\vdata\CVData;

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

        $nRtn = Query::request( $arrRequest, $arrData, $sDesc );

        if( 0 == $nRtn )
        {
            $arrRtn = json_decode( $arrData, true );

            if( CVData::GetInstance()->IsValidVData( $arrRtn ) )
            {
                $arrRtn = $arrRtn['vdata'];
            }

        }

        return $nRtn;
    }
}