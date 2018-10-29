<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group deserializer
*/
class ObjectDeserializationTest extends TestCase
{
    private function distill(array $src_arr)
    {
        $b = "";
        $writer = new BinsonWriter($b);
        $parser = new BinsonParser($b);

        $writer->put($src_arr);
        $this->assertSame($src_arr, $parser->deserialize());
    }

    public function testEmptyObject() 
    {
        $this->distill(binson::EMPTY_OBJECT);  // {}, encoded as [-1 => null]
    }

    public function testEmptyObjectInArray() 
    {
        $this->distill([binson::EMPTY_OBJECT]);  // [{}]
    }

    public function testEmptyObjectInTwoArrays() 
    {
        $this->distill([[binson::EMPTY_OBJECT]]);  // [[{}]]
    }

    public function testEmptySomeObjectsInTwoArrays() 
    {
        // [[{}],[{}],{}]
        $this->distill([[binson::EMPTY_OBJECT], [binson::EMPTY_OBJECT],binson::EMPTY_OBJECT]);
    }    

    public function testEmptyObjectInArrayAsValueOfObject() 
    {
        $this->distill(['a'=>[binson::EMPTY_OBJECT]]);  // {'a':[{}]}
    }

    public function testObjectOneItem() 
    {
        $this->distill(['a'=>true]);
    }

    public function testObjectOneItemNumericFieldname() 
    {
        $this->distill(['123.' => true]);
    }    

    public function testObjectOneItemNumericFieldnameZero() 
    {
        $this->distill(['0.' => 0 ]);
    }        

    /*  enable this test in v2.0
    public function testObjectOneItemNumericFieldnameMixed() 
    {        
        $this->distill(['7.' => '7', '123.' => '123', '1.' => '0.']);
    } */   

    public function testObjectOneItemInArrayWrap()  
    {
        $this->distill(['a'=>[true]]);   // {'a':[true]}
    }

    public function testObjectOneItemInArray2Wrap()  
    {
        $this->distill(['a'=>[[true]]]);   // {'a':[[true]]}
    }
    
    public function testObjectOneItemInArray2Wrap2()  
    {
        $this->distill(['a'=>[[true],[false]]]);   // {'a':[[true],[false]]}
    }

    public function testNestedObjects() 
    {
        $this->distill(['a'=>['b'=>binson::EMPTY_OBJECT]]);
    }

    public function testDeeplyNestedObjects() 
    {
        $this->distill(['a'=>['b'=>['c'=>['d'=>['e'=>['f'=>['g'=>binson::EMPTY_OBJECT]]]]]]]);
    }

    public function testPrimitiveTypeList() 
    {
        $this->distill(['a'=>true, 'b'=>0, 'c'=>123456, 'd'=>-1.2345, 'e'=>"abcde", 'f'=>"\x01\x02\x03"]);
    }

    public function testNestedObjectsCombination() 
    {
        $this->distill(['a'=>['a'=>[],'b'=>[]], 'b'=>['a'=>[],'b'=>[]]]);
    }


    public function testNestedObjectsCombinationWithEmptyObjects() 
    {
        $this->distill(['a'=>['a'=>binson::EMPTY_OBJECT,'b'=>binson::EMPTY_OBJECT], 
                       'b'=>['a'=>binson::EMPTY_OBJECT,'b'=>binson::EMPTY_OBJECT]]);
    }

    public function testNestedObjectsCombinationWithEmptyObjectsInArray() 
    {
        $this->distill(['a'=>['b'=>[binson::EMPTY_OBJECT],'c'=>[binson::EMPTY_OBJECT]], 
                       'd'=>['e'=>[binson::EMPTY_OBJECT],'f'=>[binson::EMPTY_OBJECT]]]);
    }

    public function testObjectWithEmptyNames() 
    {
        $this->distill([''=>'']);
    }    

    public function testObjectWithEmptyNameAndEmptyObjectAsValue() 
    {
        $this->distill([''=>binson::EMPTY_OBJECT]);
    }    

    public function testNestedObjectsWithEmptyNames() 
    {
        $this->distill([''=>[''=>[],'b'=>[]], 'b'=>[''=>[],'b'=>[]]]);
    }

    public function testListWithEmpty() 
    {
        $this->distill(['a'=>true, 'b'=>0, 'c'=>123456, 'd'=>binson::EMPTY_OBJECT, 'e'=>[],
                        'f'=>-1.2345, 'g'=>"abcde", 'h'=>"\x01\x02\x03"]);
    }

    public function testItemAfterNestedObjects() 
    {
        $this->distill(['a'=>['a'=>['a'=>"xc"]], 'b'=>"err"]);
    }
}

?>