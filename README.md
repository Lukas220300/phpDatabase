# PHP Database Adaptor

[![Latest Stable Version](https://poser.pugx.org/schoenbeck/phpdatabase/v/stable)](https://packagist.org/packages/schoenbeck/phprouter)
[![License](https://poser.pugx.org/schoenbeck/phpdatabase/license)](//packagist.org/packages/schoenbeck/phprouter)

<!--# Authors

- [Lukas220300](https://github.com/Lukas220300) -->

# Easy to install with **composer**

```sh
$ composer require schoenbeck/phpdatabase
```

## Functions

- Create a connection with a database
- Send requests to a database
- Check and configure the database and tables

## Usage

### Create connection

First you have to load the connection configuration for the database. You need to declare the following parts:
- host
- port
- driver
- user
- password
- database

You can choose between two ways to load them:
1. Save configuration in ```$GLOBALS``` variable
2. Create `DatabaseConfig` Object and pass it to the `DatabaseConnection` Object.

#### 1. Save configuration in `$GLOBALS` variable

Structure of `$GLOBALS` variable:

```php
$GLOBALS['GLOBAL_CONFIG']['DB']['host'] = "host";
$GLOBALS['GLOBAL_CONFIG']['DB']['port'] = "port";
$GLOBALS['GLOBAL_CONFIG']['DB']['driver'] = "driver";
$GLOBALS['GLOBAL_CONFIG']['DB']['user'] = "user";
$GLOBALS['GLOBAL_CONFIG']['DB']['password'] = "password";
$GLOBALS['GLOBAL_CONFIG']['DB']['database'] = "database";
```
Create a new `DatabaseConnection` Object and the configuration will be loaded.
```php
$databaseConnection = new DatabaseConnection($databaseConfiguration);
```

#### 2. Create `DatabaseConfig` Object

```php
$databaseConfiguration = new DatabaseConfig('host', 'user', 'password', 'port', 'database', 'driver');
$databaseConnection = new DatabaseConnection($databaseConfiguration);
```

### Send requests

You can send requests directly with the `DatabaseConnection` Object.

```php
$query = 'SELECT * FROM User;';
$databaseConnection->execSQLStatement($query);
```
**Another** way to send request to the database is to use the `DatabaseAdapter` class, which build the query statements for you.
```php
$databaseAdaptor = new DatabaseAdaptor($databaseConnection);
$result = $databaseAdaptor->selectFromTable('User', ['id', 'firstName', 'lastName']);
```
The result will automatic formatted as an php array.

### Use the `DatabaseConfigurator` to create, alter or drop tables and columns

Before you can configure the database you have to create a config file. This file holds the configuration of the tables.
> Note that the `DatabaseConfigurator` **always** add an index to every table. Do not declare one in the configuration file.

In the current version of this package [![Latest Stable Version](https://poser.pugx.org/schoenbeck/phpdatabase/v/stable)](https://packagist.org/packages/schoenbeck/phprouter) the following config file types are supported:
- Yaml

```Yaml
tables:
    User:
        firstName:
            type: varchar(50)
            default:
        lastName:
            type: int(10)
            default: -1
            notNull: true
```

After creating the config file you can now load them. For that you create a `DatabaseConfigurator` Object and pass it a `DatabaseAdaptor` Object.

```php
$databaseConfigurator = new DatabaseConfigurator($databaseAdaptor);
```

For each supported config file type exist an own method to load and configure the database.

```php
$databaseConfigurator->checkDatabaseConfigYamlFile('database.yml')
```

### DryRun

The `DatabaseConfigurator` support a 'dryRun' mode. It return an array which contains the queries the configurator would send.

```php
$databaseConfigurator->checkDatabaseConfigYamlFile('database.yml', $dryRun = true)
```

## License

MIT Licensed, http://www.opensource.org/licenses/MIT