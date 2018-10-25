<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group tostring
*/
class BooleanToStringTest extends TestCase
{
    public function testBooleanTrue()
    {
        $buf = "\x42\x44\x43";
        $parser = new BinsonParser($buf);
        $this->assertSame('[true]', $parser->toString());
    }

    public function testBooleanFalse()
    {
        $buf = "\x42\x45\x43";
        $parser = new BinsonParser($buf);
        $this->assertSame('[false]', $parser->toString());
    }
}
