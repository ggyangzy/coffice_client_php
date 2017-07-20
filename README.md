# coffice_client_php
install

    composer require yzyblog/coffice_client_php
update file :routes/web.php

add content:

    <?php
    
    // 跨域
    header( 'Access-Control-Allow-Methods: GET,PUT,POST,OPTIONS,DELETE' );
    header( 'Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type, Accept, content-type' );
    header( 'Access-Control-Allow-Origin: *' );
    
    
    $app->get('init',       'InitController\InitController@init');
    $app->get('init/{dbs}', 'InitController\InitController@init');
    
    
    $app->group( [ 'prefix' => 'user' ], function () use ($app) {
    
        // 注册
        $app -> post   ( '/',           'UserController\UserController@user'     );
    
        // 登陆
        $app -> get    ( '/',           'UserController\UserController@login'     );
    
        // 用户信息
        $app -> get    ( 'info',        'UserController\UserController@info'    );
    
        // 修改密码
        $app -> put    ( 'reset',       'UserController\UserController@reset'    );
    
    });
    
    
    $app->group( [ 'prefix' => '{class}' ], function () use ($app) {
    
        // get all
        $app -> get    ( '/',           'IndexController\IndexController@find'     );
    
        // get once
        $app -> get    ( '{objectId}',  'IndexController\IndexController@show'      );
    
        // post
        $app -> post   ( '/',           'IndexController\IndexController@store'     );
    
        // update
        $app -> put    ( '{objectId}',  'IndexController\IndexController@reset'    );
    
        // del
        $app -> delete ( '{objectId}',  'IndexController\IndexController@remove'   );
    
    });

create file: app/Http/Libs/Helper.php

add content:

    <?php
    namespace App\Http\Libs;
    
    use dekuan\vdata\CResponse;
    use dekuan\vdata\CRequest;
    use dekuan\vdata\CVData;
    use dekuan\delib\CLib;
    
    class Helper
    {
        /**
         * 返回vdata
         * @param $nErrorId
         * @param string $sErrorDesc
         * @param $arrVData
         * @param string $sVersion
         * @param null $bCached
         * @param array $arrExtra
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public static function getVDataResponse( $nErrorId,
                                                 $sErrorDesc	= '',
                                                 $arrVData,
                                                 $sVersion	= '1.0',
                                                 $bCached	= null,
                                                 $arrExtra	= [] )
        {
    
                return CResponse::GetInstance()->GetVDataResponse( $nErrorId,
                    $sErrorDesc,
                    $arrVData,
                    $sVersion,
                    $bCached,
                    $arrExtra );
    
        }
    
    
    
        /**
         * CURL请求
         * @param $sMethod
         * @param $sUrl
         * @param $arrData
         * @param string $sVersion
         * @param int $nTimeout
         * @param array $arrCookie
         * @param array $arrHeaders
         * @param $sResponse
         * @return mixed
         */
        public static function httpRequest( $arrParam, & $sResponse )
        {
    
                $arrResult = '';
    
                $nErr = CRequest::GetInstance()->HttpRaw( [
    
                        'method'	=> @$arrParam['sMethod'],
                        'url'		=> @$arrParam['sUrl'],
                        'data'		=> @$arrParam['arrData'],
                        'version'	=> @$arrParam['sVersion'],
                        'timeout'	=> @$arrParam['nTimeout'],
                        'cookie'	=> @$arrParam['arrCookie'],
                        'headers'	=> @$arrParam['arrHeaders']
    
                ], $arrResult );
    
                $sResponse = self::getVal( $arrResult, 'data', false, null );
    
                return $nErr;
    
        }
    
    
        /**
         * @param $arr
         * @param $sName
         * @param bool $bIsNumeric
         * @param null $default
         * @return null
         */
        public static function getVal( $arr, $sName, $bIsNumeric = false, $default = null )
        {
    
                return CLib::GetVal( $arr, $sName, $bIsNumeric, $default );
    
        }
    
    
        /**
         * curl get
         * @param $arrParam
         * @param $arrResponse
         * @return int
         */
        public static function httpGet( $arrParam, & $arrResponse )
        {
    
            return CRequest::GetInstance()->Get( $arrParam, $arrResponse );
    
        }
    
    
        /**
         * curl post
         * @param $arrParam
         * @param $arrResponse
         * @return int
         */
        public static function httpPost( $arrParam, & $arrResponse )
        {
    
            return CRequest::GetInstance()->Post( $arrParam, $arrResponse );
    
        }
    
    
        /**
         * 验证返回格式vdata
         * @param $arrJson
         * @return bool
         */
        public static function isValidVData( $arrJson )
        {
    
            return CVData::GetInstance()->IsValidVData( $arrJson );
    
        }
    }

update file: app/Http/Controllers/Controller.php

add content:

    <?php
    namespace App\Http\Controllers;
    
    use yzyblog\coffice_client_php\Client;
    use Laravel\Lumen\Routing\Controller as BaseController;
    use dekuan\vdata\CRemote;
    use dekuan\delib\CLib;
    
    class Controller extends BaseController
    {
        //
        protected $m_sAcceptedVersion;
    
    
        function __construct()
        {
    
            $this->m_sAcceptedVersion = CRemote::GetAcceptedVersionEx();
    
            Client::initialize(
                env('ServiceDomain', ''),
                env('APPID', ''),
                env('APPKey', ''),
                env('MasterKey', '')
            );
    
        }
    
        protected function _GetInstanceByVersion( $arrList )
        {
            $oRet = null;
    
            if ( CLib::IsExistingString( $this->m_sAcceptedVersion ) &&
                CLib::isArrayWithKeys( $arrList, $this->m_sAcceptedVersion ) )
            {
    
                $oRet = $arrList[ $this->m_sAcceptedVersion ]::GetInstance();
    
            }
            else if ( CLib::IsArrayWithKeys( $arrList ) )
            {
    
                $oDefaultCls	= reset( $arrList );
    
                if ( $oDefaultCls )
                {
    
                    $oRet = $oDefaultCls::GetInstance();
    
                }
    
            }
    
            return $oRet;
        }
    }
    

create file: app/Http/Controllers/IndexController/IndexController.php
 
add content:

    <?php
    namespace App\Http\Controllers\IndexController;
    
    use App\Http\Controllers\Controller;
    use App\Http\Libs\Helper;
    use dekuan\vdata\CConst;
    use yzyblog\coffice_client_php\Client;
    
    class IndexController extends Controller
    {
    
        public function __construct()
        {
            parent::__construct();
            $arrInputData = app('request')->input();
            
            if( CLib::IsArrayWithKeys( $arrInputData, ['MasteToken'] )
                && CLib::IsExistingString( $arrInputData['MasteToken'] )
                && env('MasteToken') == $arrInputData['MasteToken'] )
            {
                Client::initMaster();
            }
        }
    
        /**
         * 查询所有对象 find
         * @param string $dbs
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function find( $class )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->find( $class, $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 查询单个对象 show
         * @param $class
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function show( $class, $objectId )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->show( $class, $objectId, $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 创建对象 post
         * @param $class
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function store( $class )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->store( $class, $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 修改对象 put
         * @param $class
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function reset( $class, $objectId )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->reset( $class, $objectId, $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 删除对象 delete
         * @param $class
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function remove( $class, $objectId )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->remove( $class, $objectId, $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
    
        /**
         * 版本设置
         * @return null
         */
        private function _getInstance()
        {
    
            return $this->_GetInstanceByVersion
            ([
                '1.0' => Models\Index\Index_V1_0::class,
            ]);
    
        }
    
    }
    
create file: app/Http/Controllers/InitController/InitController.php

add content:

    <?php
    namespace App\Http\Controllers\InitController;
    
    use App\Http\Controllers\Controller;
    use App\Http\Libs\Helper;
    use yzyblog\coffice_client_php\AppInit;
    use yzyblog\coffice_client_php\Client;
    
    class InitController extends Controller
    {
    
        public function __construct()
        {
            parent::__construct();

            if( env('MasteToken') == app('request')->input('masterToken') )
            {
                Client::initMaster();
            }
        }
    
        public function init( $dbs = '' )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $nErrCode = AppInit::appInit( $arrRtn, $sDesc, $dbs );
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn );
        }
    
    
    }
    
create file: app/Http/Controllers/UserController/UserController.php

add content:

    <?php
    namespace App\Http\Controllers\UserController;
    
    use App\Http\Controllers\Controller;
    use App\Http\Libs\Helper;
    use dekuan\vdata\CConst;
    
    class UserController extends Controller
    {
    
        /**
         * 用户注册
         * @param string $dbs
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function user()
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->users( $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 用户登陆
         * @param string $dbs
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function login( $dbs = '' )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->login( $arrRtn, $sDesc, $dbs );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 用户登陆
         * @param string $dbs
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function info( $dbs = '' )
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->info( $arrRtn, $sDesc, $dbs );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 修改密码
         * @param string $dbs
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function reset()
        {
            $arrRtn 		= [];
    
            $sDesc			= '';
    
            $oInstance 		= $this->_getInstance();
    
            if ( $oInstance )
            {
    
                $nErrCode = $oInstance->reset( $arrRtn, $sDesc );
    
            }
            else
            {
    
                $nErrCode = CConst::ERROR_CREATE_INSTANCE;
    
            }
    
            return Helper::getVDataResponse( $nErrCode, $sDesc, $arrRtn, $oInstance->getModVersion() );
        }
    
    
        /**
         * 版本设置
         * @return null
         */
        private function _getInstance()
        {
    
            return $this->_GetInstanceByVersion
            ([
                '1.0' => Models\User\User_V1_0::class,
            ]);
    
        }
    
    }

create file: app/Http/Models/BaseModel.php

add content: 

    <?php
    
    namespace App\Http\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class BaseModel extends Model
    {
        protected static $g_arrInstances	= [];
    
        protected $m_sModVersion;
    
        public $Logger;
    
    
        public function __construct( array $arrAttributes = [] )
        {
    
            parent::__construct( $arrAttributes );
    
            $this->m_sModVersion = '1.0';
    
        }
    
    
        final public static function GetInstance()
        {
    
            $oRet		= null;
    
            $sClassName	= get_called_class();
    
            if ( false !== $sClassName )
            {
    
                if ( ! isset( self::$g_arrInstances[ $sClassName ] ) )
                {
    
                    $oRet = self::$g_arrInstances[ $sClassName ] = new $sClassName();
    
                }
                else
                {
    
                    $oRet = self::$g_arrInstances[ $sClassName ];
    
                }
    
            }
    
            return $oRet;
    
        }
    
    
    
        final private function __clone()
        {
    
            // ...
    
        }
    
        final public function GetModVersion()
        {
    
            return $this->m_sModVersion;
    
        }
    
        final public function SetModVersion( $sVersion )
        {
    
            $this->m_sModVersion = $sVersion;
    
        }
    }
    
create file: /Usersapp/Http/Models/Index/Index_V1_0.php

add content:

    <?php
    namespace App\Http\Models\Index;
    
    use yzyblog\coffice_client_php\Query;
    use App\Http\Models\BaseModel;
    
    class Index_V1_0 extends BaseModel
    {
        private $m_sModelVersion = '1.0';
    
        protected static $g_cStaticInstance;
    
    
        public function __construct()
        {
    
            parent::__construct();
    
            $this->SetModVersion( $this->m_sModelVersion );
    
        }
    
    
        static function mGetInstance()
        {
    
            if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
            {
    
                self::$g_cStaticInstance = new self();
    
            }
    
            return self::$g_cStaticInstance;
    
        }
    
    
    
        public function find( $class, & $arrData, & $sDesc )
        {
            // ...
    
            return Query::mGetInstance()->find( $class, app('request')->input(), $arrData, $sDesc );
    
        }
    
    
    
        public function show( $class, $objectId, & $arrData, & $sDesc )
        {
            // ...
    
            return Query::mGetInstance()->show( $class, $objectId, app('request')->input(), $arrData, $sDesc );
    
        }
    
    
    
        public function store( $class, & $arrData, & $sDesc )
        {
            // ...
    
            return Query::mGetInstance()->store( $class, app('request')->input(), $arrData, $sDesc );
    
        }
    
    
    
        public function reset( $class, $objectId, & $arrData, & $sDesc )
        {
            // ...
    
            return Query::mGetInstance()->reset( $class, $objectId, app('request')->input(), $arrData, $sDesc );
    
        }
    
    
    
        public function remove( $class, $objectId, & $arrData, & $sDesc )
        {
            // ...
    
            return Query::mGetInstance()->remove( $class, $objectId, app('request')->input(), $arrData, $sDesc );
    
        }
    
    }
    
create file: app/Http/Models/User/User_V1_0.php

add content:

    <?php
    namespace App\Http\Models\User;
    
    use yzyblog\coffice_client_php\User;
    use App\Http\Models\BaseModel;
    
    class User_V1_0 extends BaseModel
    {
        private $m_sModelVersion = '1.0';
    
        protected static $g_cStaticInstance;
    
    
        public function __construct()
        {
    
            parent::__construct();
    
            $this->SetModVersion( $this->m_sModelVersion );
    
        }
    
    
        static function mGetInstance()
        {
    
            if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
            {
    
                self::$g_cStaticInstance = new self();
    
            }
    
            return self::$g_cStaticInstance;
    
        }
    
    
        public function users( & $arrData, & $sDesc )
        {
            // ...
    
            return User::mGetInstance()->users( app('request')->input(), $arrData, $sDesc );
    
        }
    
    
        public function login( & $arrData, & $sDesc )
        {
            // ...
    
            return User::mGetInstance()->login( app('request')->input(), $arrData, $sDesc );
    
        }
    
    
        public function info( & $arrData, & $sDesc )
        {
            // ...
    
            return User::mGetInstance()->info( app('request')->input(), $arrData, $sDesc );
    
        }
    
    
        public function reset( & $arrData, & $sDesc )
        {
            // ...
    
            return User::mGetInstance()->rePassword( app('request')->input(), $arrData, $sDesc );
    
        }
    
    
    }
    
update file: .env

add content:

    ServiceDomain=XXX
    APPID=XXX
    APPKey=XXX
    MasterKey=XXX
    MasteToken=XXX