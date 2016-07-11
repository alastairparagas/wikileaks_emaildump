<?php

namespace WikiLeaksEmailDump\DataStore;

use \Closure;
use \Generator;

interface DbStoreContract
{
  
  /**
  * Executes a database operation
  * @param string $statement - Database query statement
  * @param array $data - Database query statement parameters
  */
  public function execute(string $statement, array $data): void;
  
  /**
  * Allows you to chain database operations and only 
  *   actually execute them by running the returning 
  *   closure.
  * @param string $statement - Database query statement
  * @param array $data - Database query statement parameters
  * @returns Closure - If executed, runs the statements
  */
  public function bulkExecute(string $statement, array $data): Closure;
  
  /**
  * Obtains the result of the operation
  * @returns Generator
  */
  public function obtainResult(): Generator;
  
}