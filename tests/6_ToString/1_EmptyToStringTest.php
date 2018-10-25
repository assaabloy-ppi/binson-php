<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class EmptyToStringTest extends TestCase
{
    private function process(string $str, string $arg)
    {
        $parser = new BinsonParser($arg);
        $this->assertSame($str, $parser->toString());
    }    

    public function testObjectEmpty()
    { $this->process('{}', "\x40\x41"); } 

    public function testArrayEmpty()
    { $this->process('[]', "\x42\x43"); } 

    public function testArraysNested2Empty()
    { $this->process('[[]]', "\x42\x42\x43\x43"); } 

    public function testObjectNested2Empty()
    { $this->process('{"a":{}}', "\x40\x14\x01\x61\x40\x41\x41"); } 

    public function testObjectNested4EmptyWithEmptyNames()
    { $this->process('{"":{"":{"":{}}}}', "\x40\x14\x00\x40\x14\x00\x40\x14\x00\x40\x41\x41\x41\x41"); } 

    public function testObjectNested3EmptyWithNames()
    { $this->process('{"a":{"b":{}}}', "\x40\x14\x01\x61\x40\x14\x01\x62\x40\x41\x41\x41"); } 

    public function testObjectWith3NestedEmptyArrays()
    { $this->process('{"b":[[[]]]}', "\x40\x14\x01\x62\x42\x42\x42\x43\x43\x43\x41"); } 

    public function testObjectWithNestsedMultiEmpty()
    { $this->process('{"b":[[{}],[{}]]}', "\x40\x14\x01\x62\x42\x42\x40\x41\x43\x42\x40\x41\x43\x43\x41"); } 

    public function testArraysNestsed3Empty()
    { $this->process('[[[]]]', "\x42\x42\x42\x43\x43\x43"); } 

    public function testArraysSequentialEmpty()
    { $this->process('[[],[]]', "\x42\x42\x43\x42\x43\x43"); } 

    public function testArraysSequentialNestedEmpty()
    { $this->process('[[[]],[[]]]', "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43"); } 

}
