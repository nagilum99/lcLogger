# lcLogger for PHP
lcLogger is a simple php class to log messages to a file or database (MySql or Mongodb)
### Requierements
* at least PHP5.4
* Mysqli if you want to use MySql
* MongoClient (PHP5) or MongoDBManager(PHP7) if you use MongoDB 

### Useage
just include the file and init the logger
```PHP
<?php
include_once "lcLogger/lcLogger.php";

lcLogger::Init("File",["File"=>"Log.log"]);

// set a loglevel
lcLogger::SetLogLevel("Debug");

// log a message
lcLogger::Warn("Warn message",[1337=>"test"]);
````

### Loglevels 
Possible loglevels are:
* critical
* warn
* info
* debug

Set the loglevel with `lcLogger::SetLogLevel($sLevel);` if you set an invalid level, the function will return false and the level will be set to warn.
If you don't set any loglevel, the defaul will be warn.

To log a message to a level use these functions
```PHP
lcLogger::Critical("Critical message");
lcLogger::Warn("Warn message");
lcLogger::Info("Info message");
lcLogger::Debug("Debug message");
```
all these functions are taking a second parameter that could be anything. It will be converted to a String and is added to the logmessage:
```PHP
lcLogger::Critical("Critical message",["User"=>"123","Host"=>"321"]);
lcLogger::Debug("Debug message",$oObjectToDebug);
```

### Logging to file
Use `lcLogger::Init("File",$aOption);` to init the logger for file-logging.
When using files for logging make sure that nobody can access the files from outside (deny *.log via htaccess eg.)

Options (both are optional)  are:
* File -> Default is `date("my")" ".log" extension is added within the function
* Format -> Default is `"%date%\t%level%\t%ip%:%method%\t%uri%:%source%\t%message%\r\n"`

```PHP
<?php
include_once "lcLogger/lcLogger.php";

$aOptions = [
    "File"      => "mylog-".date("my"),
    // use spaces instead of tabs
    "Format"    => "%date% %level% %ip%:%method% %uri%:%source% %message%\r\n"
];
lcLogger::Init("File",$aOptions);

lcLogger::SetLogLevel("warn");

lcLogger::Critical("Critical message");
```

### Logging to MySql
Use `lcLogger::Init("MySql",$aOption);` to init the logger to save the log in a MySql database.
Options are:
(Requiered)
* User => Username for MySql
* Password => Password for MySql

(Optional)
* Host => MySql Host, default is "localhost"
* Database => Databasename in MySql, default is "Log"
* Table => Table in the database, default is "Log"

```PHP
<?php
include_once "lcLogger/lcLogger.php";

$aOptions = [
    "User"      => "USERNAME",
    "Password"  => "PASSWORD",
    "Database"  => "Log2017",
    "Table"     => date("my")
];
lcLogger::Init("MySql",$aOptions);

lcLogger::SetLogLevel("warn");

lcLogger::Critical("Critical message");
```

### Logging to MonogDB
Use `lcLogger::Init("MongoDB",$aOption);` to init the logger to save the log in a MongoDB database.

Options are:
* ConnectionString => string to connect to MongoDB, Default is "mongodb://localhost:27017"
    see https://secure.php.net/manual/de/mongodb-driver-manager.construct.php for more information
* Database => Database to log into, default is "Log"
* Collection => Collection in database, default is "Log"
 
```PHP
<?php
include_once "lcLogger/lcLogger.php";

$aOptions = [
    "Database"  => "Log",
    "Collection"=> date("m-y")
];
lcLogger::Init("Mongodb",$aOptions);

lcLogger::SetLogLevel("warn");

lcLogger::Critical("Critical message");
```