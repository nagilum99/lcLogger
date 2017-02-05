<?php

/**
 * Class lcLoggerFile
 */
class lcLoggerFile implements lcLoggerStoreInterface{

    /**
     * path because fopen can't use relative paths
     * @var string
     */
    private $sPath = "";

    /**
     * filename
     * @var string
     */
    private $sFile = "log.log";

    /**
     * format for each log-entry
     * @var string
     */
    private $sFormat = "%date%\t%level%\t%ip%:%method%\t%uri%:%source%\t%message%\r\n";

    /**
     * lcLoggerFile constructor.
     * @param $aOptions|array
     */
    public function __construct($aOptions){
        if(isset($aOptions["Format"]) ){
            $this->sFormat["Format"] = $aOptions["Format"];
        }

        if(isset($aOptions["File"]))
            $this->sFile = $aOptions["File"];
        else
            $this->sFile = date("my").".log";

        $this->sPath = dirname(__FILE__)."/";
    }

    /**
     * @function string replace with associative arrays
     * @param array $replace
     * @param $subject
     * @return string
     */
    private static function str_replace_assoc(array $replace, $subject) {
        return str_replace(array_keys($replace), array_values($replace), $subject);
    }

    /**
     * @param array $aData
     */
    public function saveData($aData = []){

        $fFile = fopen($this->sPath.$this->sFile,'a');

        if($fFile == false){
            trigger_error("can not open logfile: ".$this->sFile,E_USER_ERROR);
        }

        foreach ($aData as $aLogEntry) {
            $aReplace = [
                "%date%" => $aLogEntry["Date"],
                "%level%" => $aLogEntry["Level"],
                "%ip%" => $aLogEntry["Ip"],
                "%method%" => $aLogEntry["Method"],
                "%uri%" => $aLogEntry["Uri"],
                "%source%" => $aLogEntry["Source"],
                "%message%" => $aLogEntry["Message"],
            ];

            $sLine = self::str_replace_assoc($aReplace, $this->sFormat);
            fwrite($fFile,$sLine);
        }

       fclose($fFile);
    }
}