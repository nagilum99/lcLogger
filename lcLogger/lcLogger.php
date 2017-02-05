<?php

include_once 'lcLoggerInterface.php';

/**
 * Class lcLogger
 */
class lcLogger implements lcLoggerInterface{

    /**
     * array with array of log-entries
     * [
     *      'Date',
     *      'Ip',
     *      'Level',
     *      'Source', // <- file:function:line
     *      'Method',
     *      'Uri',
     *      'Message'
     * ]
     * @var array
     */
    private $aLogInfo = [
    ];

    /**
     * @var lcLogger|null|mixed
     */
    private static $oInstance = null;

    /**
     * @var array
     */
    private static $aLogLevels = [
        "critical"  => 4,
        "warn"      => 3,
        "info"      => 2,
        "debug"     => 1
        ];

    /**
     * @var string
     */
    private $sCurrentLogLevel = "warn";

    /**
     * name of the class that should store the data
     * @var string
     */
    private $sDataStore = "file";

    /**
     * @var \lcLoggerStoreInterface
     */
    private $oDataStore = null;

    /**
     * Options for $oDataStore->commit
     * @var array
     */
    private $aDataStoreOptions = [];

    /**
     * @function returns an instance of lcLogger
     * @return lcLogger|null
     */
    public static function _getInstance(){

        if(self::$oInstance == null){
            self::$oInstance = new self();
            register_shutdown_function(__CLASS__."::Commit");
        }

        return self::$oInstance;
    }

    /**
     * lcLogger constructor. does nothing
     */
    function __construct(){}

    /**
     * @param string $sLoggerType
     * @param array $aOptions
     */
    public static function Init($sLoggerType = "file", $aOptions = []){
        $oInstance = self::_getInstance(); // set shutdown function

        $sPath = realpath(dirname(__FILE__))."/";
        // try to include file if class does not exist
        $sClass = "lcLogger".ucfirst(strtolower($sLoggerType));
        if(!class_exists($sClass)) {
            if (file_exists($sPath.$sClass . ".php")) {
                include_once $sPath.$sClass . ".php";
            }
        }
        // check if class exists
        if(!class_exists($sClass)) {
            trigger_error("storing class: ".$sClass." does not exist",E_USER_ERROR);
        }

        // check if the given class implements the interface
        $aInterfaces = class_implements($sClass);

        if(!isset($aInterfaces["lcLoggerStoreInterface"])){
            trigger_error("storing class: ".$sClass." does not implement lcLoggerStoreInterface",E_USER_ERROR);
        }

        $oInstance->sDataStore = $sClass;
        $oInstance->aDataStoreOptions = $aOptions;
    }

    /**
     * @function sets the log-level
     * @param $sLevel
     * @return bool
     */
    public static function SetLogLevel($sLevel){
        $sLevel = strtolower($sLevel);

        if(!isset(self::$aLogLevels[$sLevel])){
            self::pushLogEntry("warn","invalid loglevel",$sLevel);


            self::_getInstance()->sCurrentLogLevel = "warn";

            return false;
        }

        self::_getInstance()->sCurrentLogLevel = $sLevel;

        return true;
   }

    /**
     * @return string
     */
   public static function GetLogLevel(){
       return self::_getInstance()->sCurrentLogLevel;
   }

    /**
     * @return array
     */
   public static function getLogInfo(){
       return self::_getInstance()->aLogInfo;
   }

    /**
     * @function creates a string like file:function:line from source
     * @return string
     */
    private static function _getSource(){
        // last 3 frames
        $aBackTrace = debug_backtrace(0,3);
        $aCallingFrame = $aBackTrace[2];
        // using third frame for the function because it would show the log-functions
        $sCallingFunction = isset($aBackTrace[3]["function"]) ? $aBackTrace[3]["function"]:"";

        return $aCallingFrame["file"].":".$sCallingFunction.":".$aCallingFrame["line"];
    }

    /**
     * @function add a log-message to the array
     * @param $sLevel
     * @param $sMessage
     * @param null $mMixed
     */
    private static function pushLogEntry($sLevel,$sMessage,$mMixed = null){
        $sLevel = strtolower($sLevel);
        // don't log if level is too low
        if(self::$aLogLevels[$sLevel] < self::$aLogLevels[self::_getInstance()->sCurrentLogLevel])
            return;

        if($mMixed!=null)
            $sMessage = $sMessage." - ".var_export($mMixed,true);

        // create timestamp with milliseconds
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

        self::_getInstance()->aLogInfo[] = [
            'Date'      => $d->format("Y-m-d H:i:s.u"),
            'Ip'        => $_SERVER['REMOTE_ADDR'],
            'Level'     => $sLevel,
            'Source'    => self::_getSource(),
            'Method'    => $_SERVER['REQUEST_METHOD'],
            'Uri'       => $_SERVER['REQUEST_URI'],
            'Message'   => $sMessage
        ];
    }

    /**
     * @function add a message with log-level Debug
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Debug($sMessage, $mMixed = null){
        self::pushLogEntry("Debug",$sMessage,$mMixed);
    }

    /**
     * @function add a message with log-level Info
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Info($sMessage, $mMixed = null){
        self::pushLogEntry("Info",$sMessage,$mMixed);
    }

    /**
     * @function add a message with log-level Warn
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Warn($sMessage, $mMixed = null){
        self::pushLogEntry("Warn",$sMessage,$mMixed);
    }

    /**
     * @function add a message with log-level Critical
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Critical($sMessage, $mMixed = null){
        self::pushLogEntry("Critical",$sMessage,$mMixed);
    }

    /**
     * saves the data through the given storingclass
     */
    public static function commit(){
        $oInstance = self::_getInstance();

        // don't create any instance of the storingclass, there is nothing to log
        if(count($oInstance->aLogInfo) == 0)
            return;

        $oInstance->oDataStore = new $oInstance->sDataStore($oInstance->aDataStoreOptions);
        $oInstance->oDataStore->saveData($oInstance->aLogInfo);
    }

}