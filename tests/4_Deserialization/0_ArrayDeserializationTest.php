<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group deserializer
*/
class ArrayDeserializationTest extends TestCase
{
    private function distill(array $src_arr)
    {
        $b = "";
        $writer = new BinsonWriter($b);
        $parser = new BinsonParser($b);

        $writer->put($src_arr);
        $this->assertSame($src_arr, $parser->deserialize());
    }

    public function testEmptyArray() 
    {
        $this->distill([]);
    }

    public function testArrayOneItem() 
    {
        $this->distill([true]);
    }

    public function testPrimitiveTypeList() 
    {
        $this->distill([true, 0, 123456, -1.2345, "abcde", "\x01\x02\x03"]);
    }

    public function testNestedArrays() 
    {
        $this->distill([[[],[]],[[],[]]]);
    }

    public function testListWithEmpty() 
    {
        $this->distill([true, [], 123456, [[]], "abcde", "\x01\x02\x03",[]]);
    }

    public function testItemAfterNestedArrays() 
    {
        $this->distill([[["xc"]], "err"]);
    }
    
}

?>