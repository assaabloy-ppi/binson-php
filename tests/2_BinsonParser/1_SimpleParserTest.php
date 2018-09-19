<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class SimpleParserTest extends TestCase
{
    public function testArrayEmpty()
    { 
        $buf = "\x42\x43";  // []
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

    public function testArraysNestsed2Empty()
    { 
        $buf = "\x42\x42\x43\x43";  // [[]]
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(1, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(2, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

    public function testArraysNestsed3Empty()
    { 
        $buf = "\x42\x42\x42\x43\x43\x43";  // [[[]]]
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(1, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(2, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(3, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(2, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }    

    public function testArraysSequentialEmpty()
    { 
        $buf = "\x42\x42\x43\x42\x43\x43";  // [[],[]]
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->goIntoArray();
            $this->assertSame(2, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->goIntoArray();
            $this->assertSame(2, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());

        $parser->reset();
            $this->assertSame(0, $parser->depth);
        $parser->goIntoArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());

            $this->assertSame(true, $parser->verify());        
    }    
}

?>