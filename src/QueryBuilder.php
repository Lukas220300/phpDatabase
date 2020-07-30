<?php

namespace SCHOENBECK\Database;

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
            $result .= ", " . $value;
        }
        return $result . " )";
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
    public static function modifyColumnFromTable($tableName, $newColumnConfiguration)
    {
        $query = 'ALTER TABLE ' . $tableName . ' MODIFY ' . QueryBuilder::createColumnLine($newColumnConfiguration) . ';';
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
    public static function addColumnToTable($tableName, $column)
    {
        $query = 'Alter Table ' . $tableName . ' ADD ' . QueryBuilder::createColumnLine($column) . ';';
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
    public static function dropTable($tableName)
    {
        return 'DROP TABLE `' . $tableName . '`';
    }

    /**
     * Default added is uid
     *
     * [
     *  '0' => [
     *      'name' => 'lalal',
     *      'type' => 'int(11)',
     *      'unsigned' => false,
     *      'notnull' => true,
     *      'default' => true,
     *      'd-value' => '0'
     *  ]
     * ]
     *
     * @param string $tableName
     * @param array $fileds
     * @return string
     */
    public static function creatTableNotExist($tableName = '', array $fileds = [])
    {

        $query = 'CREATE TABLE `' . $tableName . '` (';
        $query .= QueryBuilder::getDefaultTableFileds();

        if (count($fileds) != 0) {
            foreach ($fileds as $filed) {
                $query .= ", ";
                $query .= QueryBuilder::createColumnLine($filed);
            }
        }
        $query .= ' );';

        return $query;
    }

    /**
     * @param $filed
     * @return string
     */
    private static function createColumnLine($filed)
    {
        $query = "";
        $query .= '`' . $filed['name'] . '` ' . $filed['type'];
        if ($filed['unsigned']) {
            $query .= ' unsigned';
        }
        if ($filed['notnull']) {
            $query .= ' NOT NULL';
        }
        if ($filed['default']) {
            $query .= ' DEFAULT ' . $filed['d-value'];
        }
        return $query;
    }

    /**
     * Get the default Table fields for every Table
     * @return string
     */
    private static function getDefaultTableFileds()
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
