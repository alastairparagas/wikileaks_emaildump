<?php

namespace WikiLeaksEmailDump\Parser;

use \Generator;
use \DOMDocument;
use \DOMNode;
use \DOMXPath;
use \InvalidArgumentException;

/**
* Parses an HTML string
* @package WikiLeaksEmailDump\Parser
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
    return call_user_func(function () {
      $asHtmlString = function (DOMNode $domNode): string {
        return $this->document->saveHTML($domNode);
      };
      
      foreach ($this->selectors as $selector) {
        $domNodeList = $this->documentXPath->query($selector);
        
        // Provided selector didn't get a match. Return an empty
        //  result set. If we do find a match, turn the obtained
        //  iterator to an array.
        if ($domNodeList === false) {
          yield array();
          continue;
        } else {
          $domNodeList = iterator_to_array($domNodeList);
        }
        
        // Map over matched values (each provided selector has 
        //  a list of matches). Get the innerHtml value. 
        //  Remove newlines and trim
        yield array_map(function ($domNode) use ($asHtmlString) {
          $domNodeString = '';
          foreach ($domNode->childNodes as $domChildNode) {
            $domNodeString .= $asHtmlString($domChildNode);
          }
          return implode('', array_map(function ($domNodeStringPortion) {
            return trim($domNodeStringPortion);
          }, explode(PHP_EOL, $domNodeString)));
        }, $domNodeList);
      }
    });
  }
  
}
  