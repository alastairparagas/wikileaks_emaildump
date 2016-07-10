<?php

namespace WikiLeaksEmailDump\Parser;

use \Generator;
use \DOMDocument;
use \DOMXPath;
use \InvalidArgumentException;

/**
* Parses an HTML string
* @package WikiLeaksEmailDump
*/
class HtmlParser implements ParserContract
{
  
  private $selectors;
  private $document;
  private $documentXPath;
  
  /**
  * Constructs an HTML Document Parser
  * @param array $selectors - String[] of document selectors whose 
  *   values should be obtained
  * @param string $document - Document to scan over
  */
  public function __construct(array $selectors, string $document)
  {
    if (empty($document)) {
      throw new InvalidArgumentException("Document cannot be empty.");
    }
    $this->selectors = $selectors;
    $this->document = DOMDocument::loadHTML($document);
    $this->documentXPath = new DOMXPath($this->document);
  }
  
  /**
  * Constructs HtmlParser from a generator
  * @param Generator $generator
  * @param string $document
  */
  public static function fromGenerator(Generator $generator, string $document)
  {
    return new self(iterator_to_array($generator), $document);
  }
  
  /**
  * Obtains the values, in-order, based 
  *   on the provided selectors
  */
  public function getValues(): Generator
  {
    $generator = function () {
      foreach ($this->selectors as $selector) {
        $domNodeList = $this->documentXPath->query($selector);
        
        if ($domNodeList === false) {
          yield array();
          continue;
        }
        
        // Map over matched values (each selector has a list of matches)
        //  Remove newlines and trim each string portion
        yield array_map(function ($domNode) {
          $domNodeString = $domNode->ownerDocument->saveHTML($domNode);
          return implode('', array_map(function ($domNodeStringPortion) {
            return trim($domNodeStringPortion);
          }, explode(PHP_EOL, $domNodeString)));
        }, iterator_to_array($domNodeList));
      }
    };
    
    return $generator();
  }
  
}
  