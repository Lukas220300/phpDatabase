<?php

namespace SCHOENBECK\Database;

use function PHPSTORM_META\type;

/**
 * Class QueryBuilder
 */
class QueryBuilder
{

    /**
     * @param $tableName
     * @param $uid
     * @return string
     */
    public static function deleteRowFromTable($tableName, $uid)
    {
        return QueryBuilder::deleteFromTable($tableName, "uid=" . $uid);
    }

    /**
     * @param $tableName
     * @param $where
     */
    public static function deleteFromTable($tableName, $where)
    {
        return "DELETE FROM " . $tableName . " WHERE " . $where . ";";
    }

    /**
     * @param $tableName
     * @param array $values
     * @param $whereCondition
     * @return string
     */
    public static function updateRowsInFiled($tableName, array $values, $whereCondition)
    {
        $query = "UPDATE " . $tableName . " SET ";
        $query .= QueryBuilder::getSetsForUpdateRows($values);
        if ($whereCondition !== '/') {
            $query .= " WHERE " . $whereCondition;
        }
        return $query;
    }

    /**
     * @param $values
     * @return string
     */
    private static function getSetsForUpdateRows($values)
    {
        $result = "";
        foreach (array_keys($values) as $columnName) {
            if ($result === "") {
                $result .= QueryBuilder::getSetValueForOneColumn($columnName, $values[$columnName]);
            } else {
                $result .= ", " . QueryBuilder::getSetValueForOneColumn($columnName, $values[$columnName]);
            }
        }
        return $result;
    }

    /**
     * @param $columnName
     * @param $value
     * @return string
     */
    private static function getSetValueForOneColumn($columnName, $value)
    {
        return $columnName . "=" . self::addQuotationMarks($value);
    }

    /**
     * @return string
     */
    public static function showTables()
    {
        return "SHOW TABLES;";
    }

    /**
     * @param $tableName
     * @param $columns
     * @param $values
     */
    public static function insertIntoTable($tableName, array $columns = ['empty' => true], array $values)
    {
        $query = "INSERT INTO";
        $query .= " " . $tableName;
        if (!isset($columns['empty'])) {
            $query .= " " . self::getConcatColumnsForInsert($columns);
        }
        $query .= " VALUES";
        $query .= " " . self::getConcatValuesForInsert($values);
        $query .= ";";
        return $query;
    }

    /**
     * @param $values
     * @return string
     */
    private static function getConcatValuesForInsert($values)
    {
        $result = "( null";
        foreach ($values as $value) {
            $result .= ", " . QueryBuilder::addQuotationMarksIfNeeded($value);
        }
        return $result . " )";
    }

    private static function addQuotationMarksIfNeeded($value)
    {
        $type = gettype($value);
        if('string' === $type) {
            return QueryBuilder::addQuotationMarks($value);
        }
        return $value;
    }

    /**
     * @param $columns
     * @return string
     */
    private static function getConcatColumnsForInsert($columns)
    {
        $result = "( uid";
        foreach ($columns as $column) {
            $result .= ", " . $column;
        }
        return $result . " )";
    }

    /**
     * @param $tableName
     * @param array $selectedColumns
     * @param string $where
     * @param string $orderBy
     * @param bool $distict
     * @return string
     */
    public static function selectFromTable($tableName, array $selectedColumns = ['0' => '*'], $where = '', $orderBy = '', $distict = false)
    {
        $query = "SELECT";
        if ($distict) {
            $query .= " DISTINCT";
        }
        $query .= QueryBuilder::getColumnsForSelect($selectedColumns);
        $query .= " FROM";
        $query .= " " . $tableName;
        if ($where !== '') {
            $query .= " WHERE " . $where;
        }
        if ($orderBy !== '') {
            $query .= " ORDER BY " . $orderBy;
        }
        $query .= ";";
        return $query;
    }

    /**
     * @param $tableName
     * @param array $selectedColumns
     * @param $tableTwoName
     * @param $columnNameTableOne
     * @param $columnNameTableTwo
     * @param string $where
     * @param string $orderBy
     * @return string
     */
    public static function innerJoin($tableName, array $selectedColumns = ['0' => '*'], $tableTwoName, $columnNameTableOne, $columnNameTableTwo, $where = '', $orderBy = '')
    {
        $query = "SELECT";
        $query .= QueryBuilder::getColumnsForSelect($selectedColumns);
        $query .= " FROM";
        $query .= " " . $tableName;
        $query .= " INNER JOIN ";
        $query .= $tableTwoName . " ON ";
        $query .= $tableName . "." . $columnNameTableOne . " = " . $tableTwoName . "." . $columnNameTableTwo;
        if ($where !== '') {
            $query .= " WHERE " . $where;
        }
        if ($orderBy !== '') {
            $query .= " ORDER BY " . $orderBy;
        }
        $query .= ";";
        return $query;
    }

    /**
     * @param array $selectedColumns
     * @return string
     */
    private static function getColumnsForSelect(array $selectedColumns)
    {
        $columns = "";
        $first = 1;
        foreach ($selectedColumns as $column) {
            if ($first === 1) {
                $columns .= " " . $column;
            } else {
                $columns .= ", " . $column;
            }
        }
        return $columns;
    }

    /**
     * @param $tableName
     * @param $newColumnConfiguration
     * @return string
     */
    public static function alterColumnFromTable($tableName, $columnName, $newColumnConfiguration)
    {
        $query = 'ALTER TABLE ' . $tableName . ' MODIFY ' . QueryBuilder::createColumnLine($columnName, $newColumnConfiguration) . ';';
        return $query;
    }

    /**
     * @param $tableName
     * @param $fieldName
     * @return string
     */
    public static function dropColumnFromTable($tableName, $fieldName)
    {
        $query = 'Alter Table ' . $tableName . ' DROP COLUMN ' . $fieldName . ';';
        return $query;
    }

    /**
     * @param $tableName
     * @return string
     */
    public static function getFieldsOfTable($tableName)
    {
        return 'select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME=\'' . $tableName . '\';';
    }

    /**
     * @param $tableName
     * @return string
     */
    public static function getTableStructure($tableName)
    {
        return 'select * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME=\'' . $tableName . '\';';
    }

    /**
     *  [
     *      'name' => 'lalal',
     *      'type' => 'varchar(100)',
     *      'unsigned' => false,
     *      'notnull' => true,
     *      'default' => false,
     *      'd-value' => '0'
     *  ]
     *
     * @param $tableName
     * @param $column
     * @return string
     */
    public static function addColumnToTable($tableName, $columnName, $columnConfig)
    {
        $query = 'Alter Table ' . $tableName . ' ADD ' . QueryBuilder::createColumnLine($columnName, $columnConfig) . ';';
        return $query;
    }

    /**
     * @param $tableNameOld
     * @param $tableNameNew
     * @return string
     */
    public static function renameTable($tableNameOld, $tableNameNew)
    {
        return 'RENAME TABLE `' . $tableNameOld . '` TO `' . $tableNameNew . '`;';
    }

    /**
     * @param $tableName
     * @return string
     */
    public static function removeTable($tableName)
    {
        return 'DROP TABLE `' . $tableName . '`';
    }

    /**
     * Default added is uid
     *
     *
     * @param string $tableName
     * @param array $columns
     * @return string
     */
    public static function createTableNotExist($tableName = '', array $columns = [])
    {
        $query = 'CREATE TABLE `' . $tableName . '` (';
        $query .= QueryBuilder::getDefaultTableColumn();
        if (count($columns) != 0) {
            foreach ($columns as $columnName => $columnConfig) {
                $query .= ", ";
                $query .= QueryBuilder::createColumnLine($columnName, $columnConfig);
            }
        }
        $query .= ' );';
        return $query;
    }

    /**
     * @return string
     */
    private static function createColumnLine($columnName, $columnConfig)
    {
        $query = "";
        $query .= '`' . $columnName . '` ' . $columnConfig['type'];

        if(isset($columnConfig['unsigned'])) {
            if(1 === $columnConfig['unsigned']) {
                $query .= ' unsigned';
            }
        }
        if(isset($columnConfig['notNull'])) {
            if(1 === $columnConfig['notNull']) {
                $query .= ' NOT NULL';
            }
        }
        if(isset($columnConfig['autoIncrement'])) {
            if(1 === $columnConfig['autoIncrement']) {
                $query .= ' AUTO_INCREMENT';
            }
        }
        if(isset($columnConfig['default'])) {
            if('boolean' === $columnConfig['type']) {
                $columnConfig['default'] = QueryBuilder::convertToBooleanString($columnConfig['default']);
            }
            $query .= ' DEFAULT ' . $columnConfig['default'];
        }
        return $query;
    }

    /**
     * @param $value
     */
    private static function convertToBooleanString($value) {
        if($value) {
            return 'true';
        } else {
            return 'false';
        }
    }

    /**
     * Get the default Table fields for every Table
     * @return string
     */
    private static function getDefaultTableColumn()
    {
        return QueryBuilder::getDefaultUID() . QueryBuilder::getDefaultPrimaryKey();
    }

    /**
     * Get the default Key for Table
     * @return string
     */
    private static function getDefaultPrimaryKey()
    {
        return 'PRIMARY KEY (`uid`)';
    }

    /**
     * Get Default UID Table Field
     * @return string
     */
    private static function getDefaultUID()
    {
        return '`uid` int(10) unsigned NOT NULL AUTO_INCREMENT, ';
    }

    /**
     * @param $fieldName
     * @param $operator
     * @param $value
     * @param bool $valueIsString
     * @return string
     */
    public static function getWhereCommandForSingleCondition($fieldName, $operator, $value, $valueIsString = false)
    {
        $whereCondition = "";
        $whereCondition .= $fieldName . " " . $operator . " ";
        if ($valueIsString) {
            $whereCondition . "'" . $value . "'";
        } else {
            $whereCondition . $value;
        }
        return $whereCondition;
    }

    /**
     * @param $value
     * @return string
     */
    public static function addQuotationMarks($value)
    {
        return "'" . $value . "'";
    }

}
