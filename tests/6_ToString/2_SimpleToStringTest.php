<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class Value1ToStringTest extends TestCase
{
    private function process(string $str, string $arg)
    {
        $parser = new BinsonParser($arg);
        $this->assertSame($str, $parser->toString());
    }  

    public function testSimpleStringArray()
    {
        $this->process('["a","a","a","a"]', "\x42\x14\x01\x61\x14\x01\x61\x14\x01\x61\x14\x01\x61\x43");
    } 

    public function testSimpleArray2()
    {
        $this->process('[[],true,[]]', "\x42\x42\x43\x44\x42\x43\x43");
    } 

    public function testSimpleObject1()
    {
        $this->process('{"a":"b","c":"d"}', "\x40\x14\x01\x61\x14\x01\x62\x14\x01\x63\x14\x01\x64\x41");
    } 

    public function testSimpleObject2()
    {
        $this->process('{"a":{},"b":[{}],"c":false}', "\x40\x14\x01\x61\x40\x41\x14\x01\x62\x42\x40\x41\x43\x14\x01\x63\x45\x41");
    } 

    public function testSimpleValuesNested()
    {
        $this->process('[[false],true,[]]', "\x42\x42\x45\x43\x44\x42\x43\x43");
    } 

    public function testSimpleValuesNestedRightAlign()
    {
        $this->process('[false,[false,[false,[false]]]]', "\x42\x45\x42\x45\x42\x45\x42\x45\x43\x43\x43\x43");
    } 

    public function testSimpleValuesNestedLeftAlign()
    {
        $this->process('[[[[false],false],false],false]', "\x42\x42\x42\x42\x45\x43\x45\x43\x45\x43\x45\x43");
    } 

    public function testSimpleValuesNestedCentered()
    {
        $this->process('[false,[false,[false],false],false]', "\x42\x45\x42\x45\x42\x45\x43\x45\x43\x45\x43");
    } 

    public function testSimpleValueCheck()
    {
        $this->process('[true,-12345,3.415,"abcde","008100ff00"]',
            "\x42\x44\x11\xc7\xcf\x46\x52\xb8\x1e\x85\xeb\x51\x0b\x40\x14\x05\x61\x62".
            "\x63\x64\x65\x18\x05\x00\x81\x00\xff\x00\x43");
    } 

    public function testSimpleValuesNestedObject1()
    {
        $this->process('[{"A":["A"]}]', "\x42\x40\x14\x01\x41\x42\x14\x01\x41\x43\x41\x43");
    } 

    public function testSimpleValuesNestedObject2()
    {
        $this->process('{"A":[{"A":"A"}]}', "\x40\x14\x01\x41\x42\x40\x14\x01\x41\x14\x01\x41\x41\x43\x41");
    } 

    public function testDeepNestedObjectLastEmpty()
    {
        $this->process('{"a":{"a":{}}}',
        "\x40\x14\x01\x61\x40\x14\x01\x61\x40\x41\x41\x41");
    }

    public function testDeepNestedObjectLastNotEmpty()
    {
        $this->process('{"a":{"a":{"a":"a"}}}',
        "\x40\x14\x01\x61\x40\x14\x01\x61\x40\x14\x01\x61\x14\x01\x61\x41\x41\x41");
    }

    public function testFieldAfterDeepNestedObject()
    { 
        $this->process('{"A":{"A":{"A":{"A":{"A":"A"}}}},"B":1}',
                "\x40\x14\x01\x41\x40\x14\x01\x41\x40\x14\x01\x41\x40\x14\x01\x41".
                "\x40\x14\x01\x41\x14\x01\x41\x41\x41\x41\x41\x14\x01\x42\x10\x01\x41");
    }     
}
