<?php

namespace SCHOENBECK\Database;

use Exception;
use mysqli_result;
use SCHOENBECK\Database\Exception\FieldNotFoundException;
use SCHOENBECK\Database\Exception\RecordNotFoundException;
use SCHOENBECK\Database\Exception\SiteNotFoundException;
use SCHOENBECK\Database\Exception\TableAlreadyExistException;
use SCHOENBECK\Database\Exception\TableCanNotCreateException;
use SCHOENBECK\Database\Exception\TableNotExistException;

/**
 * Class DatabaseAdapter
 */
class DatabaseAdapter
{

    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * DatabaseAdapter constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->databaseConnection = new DatabaseConnection();
    }

    /**
     * @param $tableName
     * @param $uid
     * @return array|bool|mysqli_result|null
     * @throws RecordNotFoundException
     * @throws TableNotExistException
     */
    public function deleteRowFromTable($tableName, $uid)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        if (!$this->checkIfRecordWithIdExits($tableName, $uid)) {
            throw new RecordNotFoundException('Record with the UID ' . $uid . ' in Table ' . $tableName . ' could NOT found.', 1007);
        }

        $query = QueryBuilder::deleteRowFromTable($tableName, $uid);
        $this->logger->debug('Execute SQL Query for Method: deleteRowFromTable ##: ' . $query);
        return $this->execQuery($query);
    }

    public function deleteFromTable($tableName, $where)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        $query = QueryBuilder::deleteFromTable($tableName, $where);
        $this->logger->debug('Execute SQL Query for Method: deleteRowFromTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param $tableName
     * @param $recordID
     * @return bool
     * @throws TableNotExistException
     */
    public function checkIfRecordWithIdExits($tableName, $recordID)
    {
        $result = $this->selectFromTable($tableName, ['0' => '*'], 'uid=' . $recordID);
        $size = count($result);
        if ($size !== 1) {
            if ($size < 1) {
                return false;
            } else {
                throw new Exception('There are two records with same uid. Should not be Impossible!');
            }
        }
        return true;
    }

    /**
     * @param $tableName
     * @param array $values
     * @param string $where
     * @return array|bool|mysqli_result|null
     * @throws FieldNotFoundException
     * @throws TableNotExistException
     */
    public function updateRowInTable($tableName, array $values, $where = '/')
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }

        foreach (array_keys($values) as $columnName) {
            if (!$this->checkIfFiledExitsInTable($tableName, $columnName)) {
                throw new FieldNotFoundException("Filed " . $columnName . " NOT exist in table " . $tableName, 1005);
            }
        }
        $query = QueryBuilder::updateRowsInFiled($tableName, $values, $where);
        $this->logger->debug('Execute SQL Query for Method: updateRowInTable ##: ' . $query);
        $result = $this->execQuery($query);
        return $result;
    }

    /**
     * @param $tableName
     * @param array $values
     * @param array $columns
     * @return array|bool|mysqli_result|null
     * @throws TableNotExistException
     */
    public function insertIntoTable($tableName, array $values, array $columns = ['empty' => true])
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }

        $query = QueryBuilder::insertIntoTable($tableName, $columns, $values);
        $this->logger->debug('Execute SQL Query for Method: insertIntoTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param $tableName
     * @param array $selectedColumns
     * @param string $where
     * @param string $orderBy
     * @param bool $distict
     * @return array
     * @throws TableNotExistException
     */
    public function selectFromTable($tableName, array $selectedColumns = ['0' => '*'], $where = '', $orderBy = '', $distict = false)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }

        $query = QueryBuilder::selectFromTable($tableName, $selectedColumns, $where, $orderBy, $distict);
        $this->logger->debug('Execute SQL Query for Method: selectFromTable ##: ' . $query);
        $result = $this->execQuery($query);
        return $this->queryResultToArray($result);
    }

    /**
     * @param $tableName
     * @param array $selectedColumns
     * @param $tableTwoName
     * @param $columnNameTableOne
     * @param $columnNameTableTwo
     * @param string $where
     * @param string $orderBy
     * @return array
     * @throws TableNotExistException
     */
    public function innerJoin($tableName, array $selectedColumns = ['0' => '*'], $tableTwoName, $columnNameTableOne, $columnNameTableTwo, $where = '', $orderBy = '')
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        if (!$this->checkIfTableExist($tableTwoName)) {
            throw new TableNotExistException("Table " . $tableTwoName . " NOT exist.", 1004);
        }

        $query = QueryBuilder::innerJoin($tableName, $selectedColumns, $tableTwoName, $columnNameTableOne, $columnNameTableTwo, $where, $orderBy);
        $this->logger->debug('Execute SQL Query for Method: selectFromTable ##: ' . $query);
        $result = $this->execQuery($query);
        return $this->queryResultToArray($result);
    }

    /**
     * @param $tableName
     * @param $newColumnConfiguration
     * @return array|bool|mysqli_result|null
     * @throws FieldNotFoundException
     * @throws TableNotExistException
     */
    public function modifyColumnFromTable($tableName, $newColumnConfiguration)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        if (!$this->checkIfFiledExitsInTable($tableName, $newColumnConfiguration['name'])) {
            throw new FieldNotFoundException("Filed " . $newColumnConfiguration['name'] . " NOT exist in table " . $tableName, 1005);
        }

        $query = QueryBuilder::modifyColumnFromTable($tableName, $newColumnConfiguration);
        $this->logger->debug('Execute SQL Query for Method: modifyColumnFromTable ##: ' . $query);
        $result = $this->execQuery($query);
        return $this->queryResultToArray($result);
    }

    /**
     * @param $tableName
     * @param $columnName
     * @return array|bool|mysqli_result|null
     * @throws FieldNotFoundException
     * @throws TableNotExistException
     */
    public function dropColumnFromTable($tableName, $columnName)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        if (!$this->checkIfFiledExitsInTable($tableName, $columnName)) {
            throw new FieldNotFoundException("Filed " . $columnName . " NOT exist in table " . $tableName, 1005);
        }

        $query = QueryBuilder::dropColumnFromTable($tableName, $columnName);
        $this->logger->debug('Execute SQL Query for Method: dropColumnFromTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param $tableName
     * @param $fieldName
     * @return bool
     * @throws TableNotExistException
     */
    public function checkIfFiledExitsInTable($tableName, $fieldName)
    {
        $fields = $this->getFieldsOfTable($tableName);
        foreach ($fields as $field) {
            if ($field[0] == $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $tableName
     * @return array
     * @throws TableNotExistException
     */
    public function getFieldsOfTable($tableName)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        $query = QueryBuilder::getFieldsOfTable($tableName);
        $this->logger->debug('Execute SQL Query for Method: getFieldsOfTable ##: ' . $query);
        $result = $this->execQuery($query);
        return $this->queryResultToArray($result);
    }

    /**
     * @param $tableName
     * @return array|bool|mysqli_result|null
     * @throws TableNotExistException
     */
    public function getTableStructure($tableName)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        $query = QueryBuilder::getTableStructure($tableName);
        $this->logger->debug('Execute SQL Query for Method: getTableStructure ##: ' . $query);
        return $this->queryResultToArray($this->execQuery($query));
    }

    /**
     * @param $tableName
     * @param $column
     * @return array|bool|mysqli_result|null
     * @throws TableNotExistException
     */
    public function addColumnToTable($tableName, $column)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }

        $query = QueryBuilder::addColumnToTable($tableName, $column);
        $this->logger->debug('Execute SQL Query for Method: addColumnToTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param $tableNameOld
     * @param $tableNameNew
     * @return array|bool|mysqli_result|null
     * @throws TableNotExistException
     */
    public function renameTable($tableNameOld, $tableNameNew)
    {
        if (!$this->checkIfTableExist($tableNameOld)) {
            throw new TableNotExistException("Table " . $tableNameOld . " NOT exist.", 1004);
        }
        $query = QueryBuilder::renameTable($tableNameOld, $tableNameNew);
        $this->logger->debug('Execute SQL Query for Method: renameTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param $tableName
     * @return array|bool|mysqli_result|null
     * @throws TableNotExistException
     */
    public function dropTable($tableName)
    {
        if (!$this->checkIfTableExist($tableName)) {
            throw new TableNotExistException("Table " . $tableName . " NOT exist.", 1004);
        }
        $query = QueryBuilder::dropTable($tableName);
        $this->logger->debug('Execute SQL Query for Method: dropTable ##: ' . $query);
        return $this->execQuery($query);
    }

    /**
     * @param string $tableName
     * @param array $fileds
     * @throws TableAlreadyExistException
     * @throws TableCanNotCreateException
     */
    public function createTable($tableName = '', array $fileds = [])
    {
        if ($this->checkIfTableExist($tableName)) {
            throw new TableAlreadyExistException("Table " . $tableName . " already exist.", 1002);
        }
        $query = QueryBuilder::creatTableNotExist($tableName, $fileds);
        $this->logger->debug('Execute SQL Query for Method: creatTable ##: ' . $query);
        $result = $this->execQuery($query);
        if (!$result) {
            throw new TableCanNotCreateException("Tabel can not create. Pleas check your configuration or the connection to Database. ", 1003);
        }
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function checkIfTableExist($tableName = '')
    {
        if ($tableName === '') {
            return false;
        }

        $tables = $this->showTables();
        foreach ($tables as $table) {
            $name = $table[0];
            if ($name == $tableName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array|null
     */
    public function showTables()
    {
        $query = QueryBuilder::showTables();
        $this->logger->debug('Execute SQL Query for Method: showTables ##: ' . $query);
        $result = $this->execQuery($query);
        return $this->queryResultToArray($result);
    }

    /**
     * @param $result
     * @return array
     */
    private function queryResultToArray($result)
    {
        if (gettype($result) !== "boolean") {
            $rows = [];
            while ($row = mysqli_fetch_array($result)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return $result;
    }

    /**
     * @param $query
     * @return array|bool|mysqli_result|null
     */
    private function execQuery($query)
    {
        try {
            return $this->databaseConnection->execSQLStatement($query);
        } catch (Exception $exception) {
            //TODO: Exception handling
            echo "####### ERROR ######";
            echo $exception->getTraceAsString();
        }
        return null;
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * @param DatabaseConnection $databaseConnection
     */
    public function setDatabaseConnection($databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    private static function addQuotationMarks($value)
    {
        return "'" . $value . "'";
    }

    public static function splitParameterToColumnAndValue($parameter)
    {
        $columns = [];
        $values = [];
        foreach (array_keys($parameter) as $key) {
            array_push($columns, $key);
            if (gettype($parameter[$key]) === 'integer') {
                array_push($values, $parameter[$key]);
            } else {
                array_push($values, self::addQuotationMarks($parameter[$key]));
            }
        }

        return ['columns' => $columns, 'values' => $values];
    }

}
