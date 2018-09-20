<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group parser
*/
class SimpleParserTest extends TestCase
{
    public function testObjectEmpty()
    { 
        $buf = "\x40\x41";  // {}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());
        $parser->leaveObject();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

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

    public function testArraysNested2Empty()
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

    public function testObjectNested2Empty()
    { 
        $buf = "\x40\x14\x01\x61\x40\x41\x41";  // {'a':{}}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->goIntoObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("a", $parser->getName());
        $parser->goIntoObject();
            $this->assertSame(2, $parser->depth);
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

   /* public function testArraysSequentialEmpty()
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
    }    */
    


}

?>