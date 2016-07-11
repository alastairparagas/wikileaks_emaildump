<?php

namespace WikiLeaksEmailDump\DataStore;

use \PDO;
use \Generator;
use \Closure;
use \InvalidArgumentException;

/**
* Wraps over the database CRUD actions
* @package WikiLeaksEmailDump\DataStore
*/
class RelationalDbStore
{
  
  private $config;
  private $database;
  private $databaseQuery;
  private $executed;
  private $bulkExecMode;
  const REQUIRED_CONFIG_KEYS = array('db_name', 
                                     'db_host',
                                     'db_port',
                                     'db_username', 
                                     'db_userpass');
  
  /**
  * Constructs an instance of a Relational DB Store representation
  * @param array $config - DB Configuration information
  */
  public function __construct(array $config = array()) 
  {
    if (empty($config)) {
      $this->config['db_name'] = getenv('db_name');
      $this->config['db_host'] = getenv('db_host');
      $this->config['db_port'] = getenv('db_port');
      $this->config['db_username']= getenv('db_username');
      $this->config['db_userpass'] = getenv('db_userpass');
      
      $this->config = array_map(function ($envVariable) {
        if ($envVariable !== false) {
          return $envVariable;
        }
        return null;
      }, $this->config);
    }
    
    // Make sure we have keys for required config
    foreach (self::REQUIRED_CONFIG_KEYS as $requiredConfigKey) {
      if (!in_array($requiredConfigKey, $this->config) || 
          !$this->config[$requiredConfigKey] || 
          !is_string($this->config[$requiredConfigKey])) {
        throw new InvalidArgumentException(
          "Missing/Invalid required config key: {$requiredConfigKey}"
        );
      }
    }
    
    // Instantiate database driver
    $this->database = new PDO(
      "pgsql:dbname={$this->config['db_name']};" . 
      "host={$this->config['db_host']}",
      $this->config['db_username'], 
      $this->config['db_userpass']
    );
    $this->executed = false;
    $this->bulkExecMode = false;
    $this->databaseQuery = null;
  }
  
  /**
  * Executes a database operation
  * @param string $statement
  * @param array $data
  */
  public function execute(string $statement, array $data = array()): void
  {
    if ($this->bulkExecMode === true) {
      throw new RuntimeException(
        "Database connection currently in transaction mode. Use bulkExecute()"
      );
    }
    
    $this->databaseQuery = $this->database->prepare($statement);
    
    foreach ($data as $paramName => $paramValueTuple) {
      $paramValue = $paramValueTuple[0] ?? '';
      $paramType = $paramValueTuple[1] ?? PDO::PARAM_STR;
      
      $this->databaseQuery->bindValue(
        $paramName, $paramValue, $paramType
      );
    }
    
    if (!$this->databaseQuery->execute()) {
      throw new RuntimeException("Database execution unsuccesful");
    }
    $this->executed = true;
  }
  
  /**
  * Allows you to chain database operations and only 
  *   actually execute them by running the returning 
  *   closure.
  * @param string $statement
  * @param array $data
  * @returns Closure
  */
  public function bulkExecute(string $statement, 
                              array $data = array()): Closure
  {
    if ($this->bulkExecMode === false) {
      $this->database->beginTransaction();
      $this->bulkExecMode = true;
      $this->executed = false;
    }
    
    $this->execute($statement, $data);
    
    return function () {
      $this->database->commit();
      $this->bulkExecMode = false;
      $this->executed = true;
    };
  }
  
  /**
  * Obtains the result of the operation
  * @returns Generator Rows of the result set, with each row as an object
  */
  public function obtainResult(): Generator
  {
    if ($this->executed === false) {
      throw new RuntimeException("No queries executed");
    }
    
    return call_user_func(function () {
      yield $this->databaseQuery->fetch(PDO::FETCH_LAZY);
    });
  }
  
}