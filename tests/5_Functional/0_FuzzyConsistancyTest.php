<?php
use PHPUnit\Framework\TestCase;

require_once(SRC_DIR . 'binson.php');

/**
* @group fuzzy
*/
class FuzzyConsistancyTest extends TestCase
{
/**
     * @dataProvider randomArrayProvider
     */
    public function testAdd($arr)
    {
        //var_dump($arr);
        $this->assertTrue($arr[0] < 100000);
    }

    public function randomArrayProvider()
    {

        for ($i=0; $i<300 ;$i++)
        {
          $aa = $this->buildArraySample();
          yield json_encode($aa) => [$aa];
        }
    }       

    private function buildArraySample() : array
    {
        $a = [];
        $a[] = rand(1,100);
        return $a;
    }
}
