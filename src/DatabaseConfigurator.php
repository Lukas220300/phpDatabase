<?php

namespace SCHOENBECK\Database;

/*
TODO: refactor dryRun
*/
class DatabaseConfigurator
{
    /** @var DatabaseAdapter $databaseAdaptor */
    protected $databaseAdaptor;

    protected $dryRun;

    protected $dryRunResult;

    public function __construct($databaseAdaptor)
    {
        $this->databaseAdaptor = $databaseAdaptor;
        $this->dryRun = false;
        $this->dryRunResult = [];
    }

    public function checkDatabaseConfigYamlFile(string $yamlConfigFileName, bool $dryRun = false) 
    {
        $this->dryRun = $dryRun;
        $tableConfig = TableConfigLoaderYaml::loadConfigFromFile($yamlConfigFileName);
        $this->checkDatabaseConfig($tableConfig);
        if($this->dryRun) {
            return $this->dryRunResult;
        }
    }

    private function checkDatabaseConfig($tableConfig) 
    {
        $tablesConfig = $tableConfig['tables'];
        $tables = [];
        foreach($tablesConfig as $tableName => $config) {
            $tables[] = $tableName;
            $this->checkTable($tableName, $config);
        }
        $this->compareDatabaseConfigWithConfigFileForTables($tables);
    }

    private function checkTable($tableName, $tableConfig)
    {
        if($this->databaseAdaptor->checkIfTableExist($tableName)) {
            $configDB = $this->databaseAdaptor->getTableStructure($tableName);
            $columns = $this->compareConfigFromFileWithDatabase($tableName, $tableConfig, $configDB);
            $this->compareDatabaseConfigWithConfigFile($tableName, $configDB, $columns);
        } else {
            $this->createTable($tableName, $tableConfig);
        }
    }
    private function createTable($tableName, $config)
    {
        $this->dryRunResult[] = $this->databaseAdaptor->createTable($tableName,$config, $this->dryRun);
    }

    private function compareConfigFromFileWithDatabase($tableName, $tableConfig, $configDB)
    {
        $columns = [];
        foreach($tableConfig as $columnName => $columnConfig) {
            $columns[] = $columnName;
            $columnDbConfig = null;
            foreach($configDB as $column) {
                if($column['COLUMN_NAME'] === $columnName) {
                    $columnDbConfig = $column;
                    break;
                }
            }
            if(null === $columnDbConfig) {
                $this->dryRunResult[] = $this->databaseAdaptor->addColumnToTable($tableName, $columnName, $columnConfig, $this->dryRun);
            } else {
                $this->checkColumnConfiguration($tableName, $columnName, $columnConfig, $columnDbConfig);
            }
        }
        return $columns;
    }

    private function checkColumnConfiguration($tableName, $columnName, $columnConfig, $columnDbConfig)
    {
        $changeColumnConfiguration = false;
        if(isset($columnConfig['type'])) {
            $configValue = $columnConfig['type'];
            $databaseValue = $this->buildTypeString($columnDbConfig['DATA_TYPE'], $columnDbConfig['CHARACTER_MAXIMUM_LENGTH']);
            if($configValue !== $databaseValue) {
                $changeColumnConfiguration = true;
            }
        }
        if(isset($columnConfig['default'])) {
            $configValue = $columnConfig['default'];
            $databaseValue = $columnDbConfig['COLUMN_DEFAULT'];
            if($configValue != $databaseValue && $configValue != ('"' . $databaseValue . '"')) {
                $changeColumnConfiguration = true;
            }
        }
        if(isset($columnConfig['notNull'])) {
            $configValue = $columnConfig['notNull'];
            $databaseValue = $this->convertDatabaseStringToBoolean($columnDbConfig['IS_NULLABLE']);
            if($configValue != $databaseValue) {
                $changeColumnConfiguration = true;
            }
        }
        if(isset($columnConfig['autoIncrement'])) {
            $configValue = $columnConfig['autoIncrement'];
            $databaseValue = $this->convertDatabaseExtra('autoIncrement', $columnDbConfig['EXTRA']);
            if($configValue != $databaseValue) {
                $changeColumnConfiguration = true;
            }
        }

        if($changeColumnConfiguration) {
            $this->dryRunResult[] = $this->databaseAdaptor->alterColumnFromTable($tableName, $columnName, $columnConfig, $this->dryRun);
        }

    }

    private function convertDatabaseExtra($mode, $value)
    {
        if('autoIncrement' === $mode) {
            if('auto_increment' === $value){
                return true;
            } else {
                return false;
            }
        }
    }

    private function convertDatabaseStringToBoolean($value)
    {
        if('YES' === $value) {
            return true;
        } else if('NO' === $value) {
            return false;
        }
    }

    private function buildTypeString($type, $maxCharLength) {
        if('tinyint' === $type) {
            return 'boolean';
        }
        return $type . "(" . $maxCharLength . ")";
    }

    private function compareDatabaseConfigWithConfigFile($tableName, $configDB, $columns) 
    {
        foreach($configDB as $column) {
            $columnName = $column['COLUMN_NAME'];
            if('uid' !== $columnName) {
                if(!in_array($columnName, $columns)) {
                    $this->dryRunResult[] = $this->databaseAdaptor->removeColumnFromTable($tableName, $columnName, $this->dryRun);
                }
            }
        }
    }

    private function compareDatabaseConfigWithConfigFileForTables($tables)
    {
        $databaseTables = $this->databaseAdaptor->showTables();
        foreach($databaseTables as $table) {
            if(!in_array($table['Tables_in_lamp'], $tables)) {
                $this->dryRunResult[] = $this->databaseAdaptor->removeTable($table['Tables_in_lamp'], $this->dryRun);
            }
        }
    }

}
