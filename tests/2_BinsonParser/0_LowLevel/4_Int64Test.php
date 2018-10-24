<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group lowlevel
* @group parser
*/
class Int64ParserTest extends TestCase
{    
    public function testIntegerINT64_MAX()
    { 
        $buf = "\x42\x13\xff\xff\xff\xff\xff\xff\xff\x7f\x43";
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(binson::INT64_MAX, $parser->getValue(binson::TYPE_INTEGER));
    }
    
    public function testIntegerINT64_MIN()
    { 
        $buf = "\x42\x13\x00\x00\x00\x00\x00\x00\x00\x80\x43";
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(binson::INT64_MIN, $parser->getValue(binson::TYPE_INTEGER));
    }

    public function testIntegerINT64_Pos_NoLostPrecision()
    { 
        $buf = "\x42\x13\x00\x00\x00\x00\x00\x40\x00\x00\x43";  // 2<<45
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(2<<45, $parser->getValue(binson::TYPE_INTEGER));
    }
    
    public function testIntegerINT64_Pos_LastDoubleIEEE()
    { 
        $buf = "\x42\x13\x00\x00\x00\x00\x00\x00\x20\x00\x43";  // 2<<52
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(2<<52, $parser->getValue(binson::TYPE_INTEGER));
    }

    public function testIntegerINT64_Neg_LastDoubleIEEE()
    { 
        $buf = "\x42\x13\xff\xff\xff\xff\xff\xff\xdf\xff\x43";  // -(2<<52) - 1
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(-(2<<52)-1, $parser->getValue(binson::TYPE_INTEGER));
    }


    public function testIntegerINT64_Neg_NoLostPrecision()
    { 
        $buf = "\x42\x13\x00\x00\x00\x00\x00\xc0\xff\xff\x43";  // -2<<45
        $parser = new BinsonParser($buf);

        $parser->enterArray()->next();
        $this->assertSame(binson::TYPE_INTEGER, $parser->getType());
        $this->assertSame(-2<<45, $parser->getValue(binson::TYPE_INTEGER));
    }

}
