<?php

namespace SCHOENBECK\Database;

use mysqli;
use mysqli_result;
use SCHOENBECK\Database\Exception\DatabaseConnectionFailException;
use SCHOENBECK\Database\Exception\DatabaseDriverNotFoundException;

/**
 * Class DatabaseConnection
 *
 * Needs following configuration in $GLOBALS - variable:
 * $GLOBALS['GLOBAL_CONFIG']['DB']
 *                                  ['host']
 *                                  ['user']
 *                                  ['password']
 *                                  ['port']
 *                                  ['database']
 *                                  ['driver']
 */
class DatabaseConnection
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $driver;

    /**
     * DatabaseConnection constructor.
     */
    public function __construct()
    {
        $this->parseSettings();
    }

    /**
     * Parse the DB-Setting from the Config to single variable
     */
    private function parseSettings()
    {
        $dbConfig = $GLOBALS['GLOBAL_CONFIG']['DB'];

        $this->host = $dbConfig['host'];
        $this->user = $dbConfig['user'];
        $this->password = $dbConfig['password'];
        $this->port = $dbConfig['port'];
        $this->database = $dbConfig['database'];
        $this->driver = $dbConfig['driver'];

    }

    /**
     * @return string
     */
    public function __toString()
    {
        $infos = "";
        $infos .= "-------DatabaseConnection::class::toString-------\n";
        $infos .= "Database: " . $this->getDatabase() . "\n";
        $infos .= "Host: " . $this->getHost() . "\n";
        $infos .= "Port: " . $this->getPort() . "\n";
        $infos .= "Driver: " . $this->getDriver() . "\n";
        $infos .= "User: " . $this->getUser() . "\n";
        $infos .= "Password: ************" . "\n";
        $infos .= "-------------------------------------------------\n";
        return $infos;
    }

    /**
     * Check if the database server is reachable and if a connection could establish with configuration
     * @return bool
     * @throws DatabaseConnectionFailException
     * @throws DatabaseDriverNotFoundException
     */
    public function connectionIsAvailable()
    {
        $connection = $this->createDatabaseConnection();
        return $this->checkGivenConnection($connection);
    }

    /**
     * @return false|mysqli|null
     * @throws DatabaseDriverNotFoundException
     */
    public function createDatabaseConnection()
    {
        $connection = null;
        switch ($this->driver) {
            case 'mysql':
                $connection = $this->connectToMySqlDatabase();
                break;
            default:
                throw new DatabaseDriverNotFoundException("Database Driver was not found. Please check your configuration", 1000);
        }
        return $connection;
    }

    /**
     * @param $connection
     * @return bool
     * @throws DatabaseConnectionFailException
     */
    public function checkGivenConnection($connection)
    {
        if (!$connection) {
            $errorMsg = "Database connection could not established!" . PHP_EOL;
            $errorMsg .= "Error-Number: " . mysqli_connect_errno() . PHP_EOL;
            $errorMsg .= "Error-Message: " . mysqli_connect_error() . PHP_EOL;
            throw new DatabaseConnectionFailException($errorMsg, 1001);
        }
        return true;
    }

    /**
     * @param string $statement
     * @return array|bool|mysqli_result
     * @throws DatabaseConnectionFailException
     * @throws DatabaseDriverNotFoundException
     */
    public function execSQLStatement($statement = '')
    {
        $result = [];
        $connection = $this->createDatabaseConnection();
        if ($this->checkGivenConnection($connection)) {
            $result = mysqli_query($connection, $statement);
        }
        return $result;
    }

    /**
     * @return false|mysqli
     */
    private function connectToMySqlDatabase()
    {
        return mysqli_connect($this->getHost() . ":" . $this->getPort(), $this->getUser(), $this->getPassword(), $this->getDatabase());
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    protected function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    protected function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

}
