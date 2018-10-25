<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class ComplexParserTest extends TestCase
{
    public function testMinimalComplex()
    { 
        // {"a":[true,123,"b",5],"b":false,"c":7}
        $buf = "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41";
        $parser = new BinsonParser($buf);
        
        $parser->enterObject();
        $parser->field("a");
        $parser->enterArray();
        $parser->next();
        $this->assertSame(true, $parser->getValue(binson::TYPE_BOOLEAN));
        $parser->next();
        $this->assertSame(123, $parser->getValue(binson::TYPE_INTEGER));
        $parser->next();
        $this->assertSame("b", $parser->getValue(binson::TYPE_STRING));
        $parser->leaveArray();
        $parser->field("b");
        $this->assertSame("b", $parser->getName());
        $this->assertSame(false, $parser->getValue(binson::TYPE_BOOLEAN));
        $parser->field("c");
        $this->assertSame(7, $parser->getValue(binson::TYPE_INTEGER));
        $parser->leaveObject();

        $this->assertSame(true, $parser->isDone());
        $this->assertSame(true, $parser->verify()); 
    }
  
    public function testComplex1()
    { 
           /*
            {
              "A": "B", 
              "B": {
                "A": "B"
              }, 
              "C": [
                "A",
                "A",
                {
                  "A": "B", 
                  "B": [
                    "A",
                    "A",
                    {
                      "A": "B"
                    },
                    [
                      [
                        [
                          [
                            {
                              "A": "B"
                            }
                          ]
                        ]
                      ]
                    ]
                  ]
                },
                "A"
              ], 
              "D": 3.141592653589793, 
              "E": false, 
              "F": 127, 
              "G": "0x0202"
            }
            */
      $buf = "\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x40\x14\x01\x41\x14\x01\x42\x41\x14\x01\x43\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x41\x42\x42\x42\x42\x40\x14\x01\x41\x14\x01\x42\x41\x43\x43\x43\x43\x43\x41\x14\x01\x41\x43\x14\x01\x44\x46\x18\x2d\x44\x54\xfb\x21\x09\x40\x14\x01\x45\x45\x14\x01\x46\x10\x7f\x14\x01\x47\x18\x02\x02\x02\x41";
      $parser = new BinsonParser($buf);

      $this->assertSame(true, $parser->verify());   
    }
}
