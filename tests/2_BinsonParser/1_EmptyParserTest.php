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
        $parser->enterObject();
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
        $parser->enterArray();
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
        $parser->enterArray();
            $this->assertSame(1, $parser->depth);
        $parser->enterArray();
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
        $parser->enterObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("a", $parser->getName());
        $parser->enterObject();
            $this->assertSame(2, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

    public function testObjectNested4EmptyWithEmptyNames()
    { 
        $buf = "\x40\x14\x00\x40\x14\x00\x40\x14\x00\x40\x41\x41\x41\x41";  // {"":{"":{"":{}}}}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->enterObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("", $parser->getName());
        $parser->enterObject();
            $this->assertSame(2, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(2, $parser->depth);
            $this->assertSame("", $parser->getName());
        $parser->enterObject();
            $this->assertSame(3, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(3, $parser->depth);
            $this->assertSame("", $parser->getName());
        $parser->enterObject();
            $this->assertSame(4, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->leaveArray();            
            $this->assertSame(3, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(2, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }


    public function testObjectNested3EmptyWithNames()
    { 
        $buf = "\x40\x14\x01\x61\x40\x14\x01\x62\x40\x41\x41\x41";  // {'a':{'b':{}}}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->enterObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("a", $parser->getName());
        $parser->enterObject();
            $this->assertSame(2, $parser->depth);

            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
            $parser->next();
            $this->assertSame(2, $parser->depth);
            $this->assertSame("b", $parser->getName());
        $parser->enterObject();
            $this->assertSame(3, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(2, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveArray();
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

    public function testObjectWith3NestedEmptyArrays()
    { 
        $buf = "\x40\x14\x01\x62\x42\x42\x42\x43\x43\x43\x41";  // {"b":[[[]]]}
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->enterObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("b", $parser->getName());
        $parser->enterArray();
        $parser->enterArray();
        $parser->enterArray();
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());            
            $this->assertSame(4, $parser->depth);
        $parser->leaveArray();
        $parser->leaveArray();
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
        $parser->leaveObject();            
            $this->assertSame(0, $parser->depth);
            $this->assertSame(true, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }

    
    public function testObjectWithNestsedMultiEmpty()
    { 
        $buf = "\x40\x14\x01\x62\x42\x42\x40\x41\x43\x42\x40\x41\x43\x43\x41";  // {"b":[[{}],[{}]]} 
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->enterObject();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
        $parser->next();
            $this->assertSame(1, $parser->depth);
            $this->assertSame("b", $parser->getName());
        $parser->enterArray();
            $parser->next();
            $parser->next();
            $parser->enterArray();
            $parser->enterObject();
        $this->assertSame(binson::TYPE_OBJECT, $parser->getType());            
            $this->assertSame(4, $parser->depth);

            $this->assertSame(false, $parser->isDone());
            $this->assertSame(true, $parser->verify());        
    }    


    public function testArraysNestsed3Empty()
    { 
        $buf = "\x42\x42\x42\x43\x43\x43";  // [[[]]]
        $parser = new BinsonParser($buf);

            $this->assertSame(0, $parser->depth);
        $parser->enterArray();
            $this->assertSame(1, $parser->depth);
        $parser->enterArray();
            $this->assertSame(2, $parser->depth);
        $parser->enterArray();
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
        $parser->enterArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->enterArray();
            $this->assertSame(2, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->leaveArray();
            $this->assertSame(1, $parser->depth);
            $this->assertSame(binson::TYPE_ARRAY, $parser->getType());
        $parser->enterArray();
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
        $parser->enterArray();
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
    

  // {"A":{"A":{"A":{"A":{"A":{}}}}}, "B":1} 
/*  uint8_t buffer[] = {
    0x40,
    0x14, 0x01, 0x41,
        0x40,
            0x14, 0x01, 0x41,
                0x40,
                    0x14, 0x01, 0x41,
                        0x40,
                            0x14, 0x01, 0x41,
                                0x40,
                                    0x14, 0x01, 0x41,
                                    0x14, 0x01, 0x41,
                                0x41,
                        0x41,
                0x41,
        0x41,
    0x14, 0x01, 0x42, 0x10, 0x01,
    0x41
};
ASSERT_TRUE(binson_parser_init(&p, buffer, sizeof(buffer)));
ASSERT_TRUE(binson_parser_verify(&p));
ASSERT_TRUE(binson_parser_go_into_object(&p));
ASSERT_TRUE(binson_parser_field_ensure(&p, "B", BINSON_TYPE_INTEGER));
*/
}

?>