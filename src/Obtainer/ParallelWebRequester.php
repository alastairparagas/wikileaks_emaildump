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
  private $multi_curl_handle;
  private $curl_references;
  
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
    $this->requestFired = false;
    
    $this->multi_curl_handle = null;
    $this->curl_references = null;
  }
  
  /**
  * Constructs ParallelWebRequester from a generator
  * @param Generator $generator
  */
  public static function fromGenerator(Generator $generator) 
  { 
    return new self(iterator_to_array($generator));
  }
  
  /**
  * Issues out parallelized requests
  * @returns void
  */
  public function request(): void
  {
    $this->requestFired = true;
    
    $this->multi_curl_handle = curl_multi_init();
    $this->curl_refs = array();
    
    // Instantiate cURL requests
    foreach ($this->urlList as $index => $url) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_multi_add_handle($multi_curl_handle, $curl);
      $curl_refs[$index] = $curl;
    }
  }
  
  /**
  * Threads out the requests and fires the requests 
  *   to the multiple urls in a threaded-fashion
  * @returns ArrayAccess
  */
  public function getResponses(): Generator 
  {
    if (!$this->requestFired) {
      throw new LogicException("request() method must be called " . 
                               "first before obtaining responses");
    }
    
    return function () {
      // Thread out cURL execution
      $is_running = 0;
      do {
        curl_multi_exec($this->multi_curl_handle, $is_running);
      } 
      while ($is_running);
      
      // Obtain response for each cURL instance
      foreach ($this->curl_refs as $index => $curl) {
        yield curl_multi_getcontent($curl);
        curl_multi_remove_handle($this->multi_curl_handle, $curl);
      }
      curl_multi_close($multi_curl_handle);
    };
  }
    
}