<?php

namespace tests\Obtainer;

use \PHPUnit\Framework\TestCase;
use \InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use \WikiLeaksEmailDump\Obtainer\ParallelWebRequester;
use \WikiLeaksEmailDump\Obtainer\WebRequesterContract;

class ParallelWebRequesterTest extends TestCase
{
  use HttpMockTrait;
  
  public static function setUpBeforeClass()
  {
    static::setUpHttpMockBeforeClass('8000', 'localhost');
  }
  
  public static function tearDownAfterClass()
  {
    static::tearDownHttpMockAfterClass();
  }
  
  public function setUp()
  {
    $this->setUpHttpMock();
  }
  
  public function tearDown()
  {
    $this->tearDownHttpMock();
  }
  
  public function testImplementsInterface()
  {
    $webRequester = new ParallelWebRequester([]);
    $this->assertTrue($webRequester instanceof WebRequesterContract);
    
    $generator = function () {
      yield "randomYield";
    };
    $webRequester = ParallelWebRequester::fromGenerator($generator());
    $this->assertTrue($webRequester instanceof WebRequesterContract);
  }
  
  public function testArrayConstructedWebRequester()
  {
    $this->http->mock
      ->when()
        ->methodIs("GET")
        ->pathIs("/url1")
        ->then()
          ->statusCode(200)
          ->body("url1Body")
      ->end();
    $this->http->mock
      ->when()
        ->methodIs("GET")
        ->pathIs("/url2")
        ->then()
          ->statusCode(400)
        ->end();
    $this->http->setUp();
    
    $webRequester = new ParallelWebRequester([
      'localhost:8000/url1',
      'localhost:8000/url2'
    ]);
    $webRequester->request();
    $responses = iterator_to_array($webRequester->getResponses());
    $this->assertEquals($responses, array("url1Body", ""));
  }
  
  public function testGeneratorConstructedWebRequester()
  {
    $this->http->mock
      ->when()
        ->methodIs("GET")
        ->pathIs("/url1")
        ->then()
          ->statusCode(200)
          ->body("url1Body")
        ->end();
    $this->http->mock
      ->when()
        ->methodIs("GET")
        ->pathIs("/url2")
        ->then()
          ->statusCode(400)
        ->end();
    $this->http->setUp();
    
    $generator = function () {
      yield "localhost:8000/url1";
      yield "localhost:8000/url2";
    };
    $webRequester = ParallelWebRequester::fromGenerator($generator());
    $webRequester->request();
    $responses = iterator_to_array($webRequester->getResponses());
    $this->assertEquals($responses, array("url1Body", ""));
  }
  
}
