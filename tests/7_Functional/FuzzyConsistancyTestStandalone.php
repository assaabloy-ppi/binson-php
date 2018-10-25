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
    
        $binson_raw = binson_encode($arr);
        $decoded = binson_decode($binson_raw); 

        return $arr === $decoded && json_encode($arr) === json_encode($decoded);
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
        $this->iter_per_level = 100*1000;
    }

    /******* Array generation related methods ****************/
    private function genBoolean() : bool
    {
        return (rand(0, 1) === 1)? true : false;
    }

    private function genInteger() : int
    {
        return random_int(PHP_INT_MIN , PHP_INT_MAX);
    }

    private function genDouble($min=0, $max=1, $mul=1000000) : float
    {
        if ($min>$max) return null;
        return mt_rand($min*$mul, $max*$mul) / $mul;
    }

    private function genString(bool $ascii = false, bool $is_name = false) : string
    {
        $key = '';
        $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));

        for($i=0; $i < $this->level+1; $i++) {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }

    private function genBytes() : string
    {
        return random_bytes($this->level * 2);
    }


    public function buildArraySample(int $type = binson::TYPE_NONE, int $current_depth = 0) : array
    {   
        $arr = [];
        $item = 0;
    
        if ($type === binson::TYPE_NONE || $current_depth == 0)
            $type = rand(0, 1)? binson::TYPE_ARRAY : binson::TYPE_OBJECT;

        $items = rand(0, $this->level+1);
        for ($i=0; $i<$items; $i++)
        {
            while (true)
            {
                switch (rand(0, 2+$this->level))
                {

                        case 0: // nested
                        case 1: // nested
                        case 2: // nested                                                
                            if ($current_depth > 10)
                                break 2;
                                
                            $item = $this->buildArraySample(binson::TYPE_NONE, $current_depth+1);
                            break 2;

                        case 3: // empty block
                            $item =  ($type === binson::TYPE_OBJECT)? binson::EMPTY_OBJECT : binson::EMPTY_ARRAY;
                            break 2;

                        case 4: // boolean             
                            $item = $this->genBoolean();
                            break 2;

                        case 5: // integer                    
                            $item = $this->genInteger();
                            break 2;

                        case 6: // double                                        
                            $item = $this->genDouble();
                            break 2;

                        case 7: // string                    
                            $item = $this->genString();
                            break 2;

                        //case 8: // bytes                    
                        //    $item = $this->genBytes();
                        //    break 2;

                        default:
                        break 2;
                }
            }

            if (isset($item))
            {
                if ($type === binson::TYPE_OBJECT)
                    $arr[fixNumField($this->genString())] = $item;
                else
                    $arr[] = $item;

                unset($item);  
            }

        }
      
        uksort($arr, "strcmp");

        if ($current_depth === 0)
        {
            $this->counter++;
            if ($this->counter > $this->iter_per_level)
            {
                $this->counter = 0;
                $this->level++;
            }
        }

        return $arr;
    }
}
  
//////////////////

$t = new MyFuzzyConsistencyTest();
$t->resetArraySample();
$json_str = '';

//$item = $t->buildArraySample(binson::TYPE_NONE, 0);

foreach($t->arrayGenerator() as $arr)
{
    //echo round(memory_get_usage()/1048576,2)." megabytes".PHP_EOL; 
    $json_str = json_encode($arr);
    if (!is_string($json_str))  // unable to serialize to json
    {
        var_dump($arr);
        return;
    }

    //echo "Lvl: ".$t->level.", sample: ".substr($json_str, 0, 60)." ...".PHP_EOL;
    echo "Lvl: ".$t->level.", sample: ".$json_str.PHP_EOL.PHP_EOL;

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
