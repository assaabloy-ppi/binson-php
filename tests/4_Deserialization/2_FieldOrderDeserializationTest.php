<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group deserializer
*/
class FieldOrderDeserializationTest extends TestCase
{
    //++ cases with numeric fields, etc

    public function testSimpleArrayInnerFieldReorder() 
    {
        $b = "\x42\x40\x14\x01\x78\x44\x14\x01\x61\x44\x14\x03\x7a\x7a\x7a\x44\x41\x43";
        $parser = new BinsonParser($b);

        $this->expectException(BinsonException::class);
        $arr = $parser->deserialize();
    }

}
