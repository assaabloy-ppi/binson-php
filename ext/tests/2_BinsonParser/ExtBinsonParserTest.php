<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../out/binson.php');

class ExtBinsonParserTest extends TestCase
{

    public function testSimpleObjectParsing()
    {   
        $buf = "\x40\x41";
        $parser = new BinsonParser($buf);

        $this->assertSame($parser->getDepth(), 0);

        $parser->goInto();

        $this->assertSame($parser->getDepth(), 1);
        $this->assertSame($parser->getType(), binson::BINSON_ID_OBJECT);

        $parser->goUp();

        $this->assertSame($parser->getDepth(), 0);
        $this->assertSame($parser->getType(), binson::BINSON_ID_UNKNOWN);

    }    
 
    public function testToString()
    {   
        $buf = "\x40\x41";
        $parser = new BinsonParser($buf);

        $this->assertSame($parser->toString(false), "{}");
    }

}

?>