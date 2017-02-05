<?php

/**
 * Class lcLoggerMongodb
 */
class lcLoggerMongodb implements lcLoggerStoreInterface{

    /**
     * Connection data / Database / Collection
     * @var array
     */
    private $aMongoData = [
        "ConnectionString" => "mongodb://localhost:27017",
        "Database"      => "Log",
        "Collection"    => "Log"
    ];

    /**
     * "document" for monogdb
     * @var array
     */
    private $aInsertData = [
            "IP"        => "",
            "Method"    => "",
            "Uri"       => "",
            "Date"      => "",
            "Messages"  => [
            ]
        ];

    /**
     * lcLoggerMongodb constructor.
     * @param $aOptions array
     */
    public function __construct($aOptions = ["ConnectionString" => "mongodb://localhost:27017",
                                             "Database" => "Log",
                                             "Collection" => "Log"]){

        foreach ($this->aMongoData as $sKey=>$sVal){
            if(isset($aOptions[$sKey]))
                $this->aMongoData[$sKey] = $aOptions[$sKey];
        }
    }

    /**
     * @param array $aData
     */
    public function saveData($aData = []){

        // prepare the data
        $this->aInsertData["IP"]        = $aData[0]["Ip"];
        $this->aInsertData["Method"]    = $aData[0]["Method"];
        $this->aInsertData["Uri"]       = $aData[0]["Uri"];
        $this->aInsertData["Date"]      = date("Y-m-d H:i:s");

        foreach ($aData as $aLogEntry){
            $this->aInsertData["Messages"][] = [
                "Date"      => $aLogEntry["Date"],
                "Source"    => $aLogEntry["Source"],
                "Level"     => $aLogEntry["Level"],
                "Message"   => $aLogEntry["Message"]
            ];
        }

        // check which version of the mongodb class is installed
        if(class_exists("MongoDB\\Driver\\Manager")){
            $this->_mongoDbInsertPhp7();
        }elseif (class_exists("MongoClient")){
            $this->_mongoDbInsertPhp5();
        }else{
            trigger_error("Failed to find MongoDB modul.");
        }
    }

    /**
     * uses the old MongoClient classes to insert logdata into database
     */
    private function _mongoDbInsertPhp5(){
        /**
         * Todo: Need to test this
         * only able to use the new classes at the moment
         */
        try{
            $oClient = new MongoClient($this->aMongoData["ConnectionString"]);

            $oDB = $oClient->selectDB($this->aMongoData["Database"]);

            $oCollection = $oDB->selectCollection($this->aMongoData["Collection"]);

            $oCollection->insert($this->aInsertData);

        }catch (Exception $exception){
            trigger_error($exception->getMessage());
        }
    }

    /**
     * uses the new PHP7 classes to insert logdata into database
     * MongoDB\Driver\
     */
    private function _mongoDbInsertPhp7(){
        /**
         * @var $oManager MongoDB\Driver\Manager
         */
        $oManager = null;
        try{
            $oManager = new MongoDB\Driver\Manager($this->aMongoData["ConnectionString"]);
        }catch (Exception $exception){
            trigger_error($exception->getMessage());
            return;
        }

        $oBulkWrite = new MongoDB\Driver\BulkWrite();
        $oBulkWrite->insert($this->aInsertData);

        $sDbString = $this->aMongoData["Database"].".".$this->aMongoData["Collection"];

        try{
            $oManager->executeBulkWrite($sDbString,$oBulkWrite);
        }catch (Exception $exception){
            trigger_error($exception->getMessage());
            return;
        }
    }
}