<?php

namespace WikiLeaksEmailDump\Parser;

use \Generator;

/**
* Contract for anything that parses a string document 
*   and returns values based on provided selectors
*/
interface ParserContract
{
  
  /**
  * Constructs a Parser from an exhaustible Generator
  * @param Generator $generator - exhaustible generator that generates 
  *   all of the selectors 
  * @param string $document
  */
  public static function fromGenerator(Generator $generator, string $document);
  
  /**
  * Obtains the values of provided selectors on the 
  *   provided document
  * @returns Generator
  */
  public function getValues(): Generator;
  
}