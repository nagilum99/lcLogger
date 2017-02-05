<?php

/**
 * Class lcLoggerMysql
 */
class lcLoggerMysql implements \lcLoggerStoreInterface {
    /**
     * mysqli
     * @var mysqli
     */
    private $oMysql = null;
    /**
     * access-date for mysql
     * @var array
     */
    private $aMySqlData = [
        "Host"      => "localhost",
        "Database"  => "Log",
        "User"      => "",
        "Password"  => "",
        "Table"     => "Log"
    ];

    private static $sSqlTable = "
CREATE  TABLE __TABLE__(
  `date`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `ip`      VARCHAR (20)    NOT NULL DEFAULT \"\",
  `level`   VARCHAR (10)    NOT NULL DEFAULT \"INFO\",
  `source`  VARCHAR (255)   NOT NULL DEFAULT \"\",
  `method`  VARCHAR (10)    NOT NULL DEFAULT \"\",
  `uri`     VARCHAR (255)   NOT NULL DEFAULT \"\",
  `message` TEXT            NOT NULL DEFAULT \"\"
)ENGINE = InnoDB ;";

    /**
     * lcDatabaseLogger constructor.
     * @param array $aOptions
     */
    public function __construct($aOptions = ["Database"=>"Log","User"=>"","Password"=>"","Table"=>"Log"]){
        foreach ($this->aMySqlData as $sKey=>$sVal){
            if(isset($aOptions[$sKey]))
                $this->aMySqlData[$sKey] = $aOptions[$sKey];
        }

    }

    /**
     * @param array $aData
     */
    public function saveData($aData = []){

        if(!$this->_checkSql()){
            return;
        }
        $sQuery = "INSERT INTO ".$this->aMySqlData["Table"]." (date,ip,level,source,method,uri,message) VALUES 
                                                             (   ?, ?,    ?,     ?,     ?,  ?,      ?)";
        $sDate = "";
        $sIp = "";
        $sLevel = "";
        $sSource = "";
        $sMethod = "";
        $sUri = "";
        $sMessage= "";

        $stmt = $this->oMysql->prepare($sQuery);
        $stmt->bind_param("sssssss",$sDate,$sIp,$sLevel,$sSource,$sMethod,$sUri,$sMessage);

        foreach($aData as $aLogEntry){
            $sDate = $aLogEntry["Date"];
            $sIp = $aLogEntry["Ip"];
            $sLevel = $aLogEntry["Level"];
            $sSource = $aLogEntry["Source"];
            $sMethod = $aLogEntry["Method"];
            $sUri = $aLogEntry["Uri"];
            $sMessage= $aLogEntry["Message"];
            $stmt->execute();
        }
    }

    /**
     * @function connects to MySql and checks for the given table
     * @return bool
     */
    private function _checkSql(){
        $bError = $this->_checkSqlConnection();
        if(!$bError)
            return false;
        $bError = $this->_checkSqlTable();

        return $bError;
    }

    /**
     * @function tries to connect to database
     * @return bool
     */
    private function _checkSqlConnection(){
        $this->oMysql = new mysqli($this->aMySqlData["Host"],
                                    $this->aMySqlData["User"],
                                    $this->aMySqlData["Password"],
                                    $this->aMySqlData["Database"]);
        if($this->oMysql->connect_errno){
            trigger_error("lcDatabaseLogger Failed to connect to database: ".$this->oMysql->connect_error,E_USER_WARNING);
            return false;
        }

        return true;
    }

    /**
     * @function checks if the given table exists and tries to create it if not
     * @return bool
     */
    private function _checkSqlTable(){
        if ($this->oMysql->connect_errno)
            return false;

        $sQuery = "SELECT 1 FROM " . $this->aMySqlData["Table"] . " LIMIT 1";

        $bError = $this->oMysql->query($sQuery);

        // 1146 = Table does not exist
        if (!$bError && $this->oMysql->errno != 1146) {
            trigger_error("lcDatabaseLogger database-error: " . $this->oMysql->connect_error, E_USER_WARNING);
            return false;
        } elseif ($bError)
            return true;

        // !$bError && connect_error == 1146
        // try to create table now

        $sQuery = str_replace("__TABLE__", $this->aMySqlData["Table"], self::$sSqlTable);

        $bError = $this->oMysql->query($sQuery);

        if (!$bError) {
            trigger_error("lcDatabaseLogger database-error: " . $this->oMysql->connect_error, E_USER_WARNING);

            return false;
        }

        return true;
    }
}