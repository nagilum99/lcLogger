<?php

interface lcLoggerInterface{

    /**
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Critical($sMessage,$mMixed=null);

    /**
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Warn($sMessage,$mMixed=null);

    /**
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Info($sMessage,$mMixed=null);

    /**
     * @param $sMessage
     * @param null $mMixed
     */
    public static function Debug($sMessage,$mMixed=null);

    /**
     * @param string $sLoggerType
     * @param array $aOptions
     */
    public static function Init($sLoggerType="mySql",$aOptions=[]);

    /**
     */
    public static function Commit();

    /**
     * @param $sLogLevel
     * @return bool
     */
    public static function SetLogLevel($sLogLevel);

    /**
     * @return string
     */
    public static function GetLogLevel();
}

interface lcLoggerStoreInterface{
    /**
     * lcLoggerStoreInterface constructor.
     * @param $aOptions
     */
    public function __construct($aOptions);

    /**
     * @function saves the data to whatever you want
     *   Array looks like this
     *    [
     *      [
     *      'Date',
     *      'Ip',
     *      'Level',
     *      'Source', // <- file:function:line
     *      'Method',
     *      'Uri',
     *      'Message'
     *      ],
     *       ...
     *    ]
     * @param array $aData
     */
    public function saveData($aData=[]);
}