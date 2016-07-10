<?php

namespace tests\Parser;

use \PHPUnit\Framework\TestCase;
use \WikiLeaksEmailDump\Parser\HtmlParser;
use \WikiLeaksEmailDump\Parser\ParserContract;

class HtmlParserTester extends TestCase
{
  
  /**
  * Tests that two constructors generate objects 
  *   that abide by the Parser interface.
  */
  public function testImplementsInterface()
  {
    $htmlParser = new HtmlParser(array(), "1");
    $this->assertTrue($htmlParser instanceof ParserContract);
    
    $generator = function () {
      yield "1";
    };
    $htmlParser = HtmlParser::fromGenerator($generator(), "1");
    $this->assertTrue($htmlParser instanceof ParserContract);
  }
  
  /**
  * Tests operation behavior on valid DOM string with 
  *   both constructors
  */
  public function testValidDom()
  {
    $sampleDomString = "
    <div>
      <div class='empire'>
        Some Value
      </div>
      <div class='empire'>
        <span>A span nested value</span>
      </div>
    </div>
    ";
      
    $htmlParser = new HtmlParser(
      array(
        "//div/div[@class='empire']",
        "//span",
        "//empire"
      ),
      $sampleDomString
    );
    $results = iterator_to_array($htmlParser->getValues());
    $this->assertEquals(
      $results,
      array(
        array(
          "<div class=\"empire\">Some Value</div>", 
          "<div class=\"empire\"><span>A span nested value</span></div>"
        ),
        array(
          "<span>A span nested value</span>"
        ),
        array()
      )
    );
    
    $htmlParser = HtmlParser::fromGenerator(call_user_func(function () {
      yield "//div/div[@class='empire']";
      yield "//span";
      yield "//empire";
    }), $sampleDomString);
    $results = iterator_to_array($htmlParser->getValues());
    $this->assertEquals(
      $results,
      array(
        array(
          "<div class=\"empire\">Some Value</div>", 
          "<div class=\"empire\"><span>A span nested value</span></div>"
        ),
        array(
          "<span>A span nested value</span>"
        ),
        array()
      )
    );
  }
  
  /**
  * Tests operation behavior on invalid DOM string
  */
  public function testInvalidDom()
  {
    $sampleDomString = "123";
    
    $htmlParser = new HtmlParser(
      array(
        "//div"
      ),
      $sampleDomString
    );
    $results = iterator_to_array($htmlParser->getValues());
    $this->assertEquals($results, array(array()));
  }
  
}
