<?php

class RoboFile extends \Robo\Tasks
{
  
  public function bootstrapProd() 
  { 
    $collection = $this->collection();
    
    $this
      ->taskComposerInstall()
      ->noDev()
      ->optimizeAutoloader()
      ->addToCollection($collection);
    $this
      ->taskComposerDumpAutoload()
      ->optimize()
      ->noDev()
      ->addToCollection($collection);
    
    $collection->run();
  }
  
  public function bootstrapDev() 
  {
    $collection = $this->collection();
    
    $this
      ->taskComposerInstall()
      ->addToCollection($collection);
    $this
      ->taskComposerDumpAutoload()
      ->addToCollection($collection);
    
    $collection->run();
  }
  
  public function test() 
  {
    $this
      ->taskPHPUnit()
      ->bootstrap('tests/bootstrap.php')
      ->files('tests')
      ->run();
  }
  
  public function testWatch() 
  {
    $runTest = function () {
      $this->test();
    };
    
    $this
      ->taskWatch()
      ->monitor('index.php', $runTest)
      ->monitor('composer.json', $runTest)
      ->monitor('./src', $runTest)
      ->monitor('./tests', $runTest)
      ->run();
  }
  
  public function syntaxcheck() 
  {
    $this
      ->taskExec('./vendor/bin/parallel-lint')
      ->arg('./src')
      ->arg('index.php')
      ->arg('RoboFile.php')
      ->arg('./tests')
      ->run();
  }
  
  public function syntaxcheckWatch() 
  {
    $runSyntaxCheck = function () {
      $this->syntaxCheck();
    };
    
    $this
      ->taskWatch()
      ->monitor('index.php', $runSyntaxCheck)
      ->monitor('./src', $runSyntaxCheck)
      ->monitor('RoboFile.php', $runSyntaxCheck)
      ->monitor('./tests', $runSyntaxCheck)
      ->run();
  }
  
}