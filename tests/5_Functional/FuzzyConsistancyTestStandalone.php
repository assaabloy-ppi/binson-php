<?php declare(strict_types=1);

require_once(__DIR__ . '/../../src/binson.php');


class MyFuzzyConsistencyTest
{
    public $counter;
    
    public $level;
    public $iter_per_level;

    const cfg = [
        'level_object' =>  2,
        'level_empty' =>  1,
        'level_nested' =>  1,
        'level_boolean' => 3,
        'level_integer' => 4,
        'level_double'  => 6,
        'level_string'  => 4,
        'level_bytes'  => 4,

        'max_count_mult'  => 1,   // level * 'max_count_mult' = maximum item count per block
        'max_count_add'  => 3,

        'strlen_mult'  => 1, 
        'strlen_add'  => 2,
    ];

    public function testIt($arr) : bool
    {
        //var_dump($arr);
        //$this->assertTrue($arr[0] < 100000);
        return true;
    }

    public function runNext() : bool
    {

        $this->counter++;
    }

    public function randomArrayProvider()
    {

        for ($i=0; $i<30000 ;$i++)
        {
          $aa = $this->buildArraySample();
          yield json_encode($aa) => [$aa];
        }
    }       

    public function arrayGenerator()
    {
        while (true)
        {
            yield $this->buildArraySample();
        }
    }

    public function resetArraySample()
    {
        $this->level = 1;
        $this->iter_per_level = 10*1000;
    }

    private function buildArraySample(int $type = binson::TYPE_NONE, int $current_depth = 0) : array
    {
        $a = [];
        $a[] = rand(1,10000);
        $a[] = rand(1,10000);
        return $a;
    }
}
  
//////////////////

$t = new MyFuzzyConsistencyTest();
$t->resetArraySample();
$json_str = '';


foreach($t->arrayGenerator() as $arr)
{
    $json_str = json_encode($arr);
    echo "Sample: ".substr($json_str, 0, 60)." ...".PHP_EOL;

    try{    
      if (!$t->testIt($arr))
        break;
    }
    catch (Exception $ex)
    {
        echo PHP_EOL.$ex.PHP_EOL;
        break;                
    }
} 
    
echo PHP_EOL."Last sample: ".$json_str.PHP_EOL.PHP_EOL;
echo "Tests done: ".$t->counter.PHP_EOL;
