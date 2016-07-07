<?php

namespace WikiLeaksEmailDump\Obtainer;

use \InvalidArgumentException; 
use \LogicException;
use \ArrayAccess;
use \Generator;

/**
* Fires Parallel Web Requests
* @package WikiLeaksEmailDump
*/
class ParallelWebRequester implements WebRequesterContract 
{
  
  private $urlList;
  private $responses;
  private $requestFired;
  
  /**
  * Constructs ParallelWebRequester
  * @param array $urlList String[] of urls to request from
  */
  public function __construct(array $urlList) 
  { 
    foreach($urlList as $url) {
      if (!is_string($url)) {
        throw new InvalidArgumentException("urlList must be a " . 
                                           "string[]");
      }
    }
    
    $this->urlList = $urlList;
    $this->responses = [];
    $this->requestFired = false;
  }
  
  /**
  * Constructs ParallelWebRequester from a generator
  * @param Generator $generator
  */
  public static function fromGenerator(Generator $generator) 
  {
    $urlList = [];
    
    foreach ($generator() as $url) {
      $urlList[] = $url;
    }
    
    return new self($urlList);
  }
  
  /**
  * Issues out parallelized requests
  * @returns void
  */
  public function request(): Generator
  {
    $this->requestFired = true;
    
    $multi_curl_handle = curl_multi_init();
    $curl_refs = array();
    
    // Instantiate cURL instances
    foreach ($this->urlList as $index => $url) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HEADER, 0);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_multi_add_handle($multi_curl_handle, $curl);
      $curl_refs[$index] = $curl;
    }
    
    // Thread out cURL execution
    $is_running = 0;
    do {
      curl_multi_exec($multi_curl_handle, $is_running);
    } 
    while ($is_running > 0);
    
    // Obtain response for each cURL instance
    foreach ($curl_refs as $index => $curl) {
      $this->responses[$index] = curl_multi_getcontent($curl);
      curl_multi_remove_handle($multi_curl_handle, $curl);
    }
    curl_multi_close($multi_curl_handle);
  }
  
  /**
  * Threads out the requests and fires the requests 
  *   to the multiple urls in a threaded-fashion
  * @returns ArrayAccess
  */
  public function getResponses(): ArrayAccess
  {
    if (!$this->requestFired) {
      throw new LogicException("request() method must be called " . 
                               "first before obtaining responses");
    }
    
    return new class($this->responses) extends ArrayAccess {
      private $responses;
      public function __construct($urlList) {
        $this->responses = $responses;
      }
      public function offsetExists($offset) {
        return array_key_exists($offset, $this->responses);
      }
      public function offsetGet($offset) {
        return $this->responses[$offset];
      }
      public function offsetSet($offset, $newVal) {
        $this->responses[$offset] = $newVal;
      }
      public function offsetUnset($offset) {
        unset($this->responses[$offset]);
      }
    };
  }
    
}