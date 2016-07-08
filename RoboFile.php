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
      ->run();
  }
  
  public function testWatch() 
  {
    $this
      ->taskWatch()
      ->monitor('index.php', $this->test)
      ->monitor('composer.json', $this->test)
      ->monitor('./src', $this->test)
      ->run();
  }
  
  public function syntaxcheck($loglevel='error') 
  {
    $this
      ->taskExec('./vendor/bin/php7cc')
      ->arg('./src')
      ->arg('index.php')
      ->arg('RoboFile.php')
      ->option('level', $loglevel)
      ->run();
  }
  
  public function syntaxcheckWatch() 
  {
    $this
      ->taskWatch()
      ->monitor('index.php', $this->syntaxCheck)
      ->monitor('./src', $this->syntaxCheck)
      ->run();
  }
  
}