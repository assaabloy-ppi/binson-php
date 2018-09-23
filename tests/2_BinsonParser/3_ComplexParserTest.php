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
        $parser->next();
        $this->assertSame("b", $parser->getName());
        $this->assertSame(false, $parser->getValue(binson::TYPE_BOOLEAN));
        $parser->field("c");
        $this->assertSame(7, $parser->getValue(binson::TYPE_INTEGER));
        $parser->leaveObject();

        $this->assertSame(true, $parser->isDone());
        $this->assertSame(true, $parser->verify()); 
      }
  
}
?>

/*TEST(verify_complex_object)
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
    uint8_t binson_bytes[104] = "\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x40\x14\x01\x41\x14\x01\x42\x41\x14\x01\x43\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x41\x42\x42\x42\x42\x40\x14\x01\x41\x14\x01\x42\x41\x43\x43\x43\x43\x43\x41\x14\x01\x41\x43\x14\x01\x44\x46\x18\x2d\x44\x54\xfb\x21\x09\x40\x14\x01\x45\x45\x14\x01\x46\x10\x7f\x14\x01\x47\x18\x02\x02\x02\x41";
    binson_parser p;



        /*
     * input = {
     *   A := "B",
     *   B := [
     *     "A",
     *     {}
     *   ],
     *   C := {
     *     A := {
     *       A := 2
     *     }
     *   }
     * }
     */
    uint8_t buffer2[33] = { /* 401401411401421401424214014140414314014340140141401401411002414141*/
        0x40, 0x14, 0x01, 0x41, 0x14, 0x01, 0x42, 0x14,
        0x01, 0x42, 0x42, 0x14, 0x01, 0x41, 0x40, 0x41,
        0x43, 0x14, 0x01, 0x43, 0x40, 0x14, 0x01, 0x41,
        0x40, 0x14, 0x01, 0x41, 0x10, 0x02, 0x41, 0x41,
        0x41
    };

    binson_parser_init(&p, buffer2, sizeof(buffer2));
    ASSERT_TRUE(binson_parser_verify(&p));
    ASSERT_TRUE(binson_parser_go_into_object(&p));
    ASSERT_TRUE(binson_parser_field_ensure(&p, "B", BINSON_TYPE_ARRAY));
    ASSERT_TRUE(binson_parser_field_ensure(&p, "C", BINSON_TYPE_OBJECT));

    */

    // {"b":[true,13,"cba",{"abc":false, "b":"0x008100ff00", "cba":"abc"},9223372036854775807]}  


      // {"a":[{"d":false},{"e":true},9223372036854775807]}
  const uint8_t b1[]  =
    "\x40\x14\x01\x61"
        "\x42"
           "\x40"
                "\x14\x01\x64\x45"
            "\x41"
            "\x40"
                "\x14\x01\x65\x44"
            "\x41"
            "\x13\xff\xff\xff\xff\xff\xff\xff\x7f"
       "\x43"
    "\x41";