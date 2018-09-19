<?php

 include_once __DIR__.'/binson.php';


 $b = "\x42\x43";
//$p = new BinsonParser($b);


//$encdec = $p->tostr();

//echo PHP_EOL;
//echo json_encode($src).PHP_EOL;
//echo $encdec.PHP_EOL;

$parser = new BinsonParser($b);
//$this->assertSame(true, $parser->validate());

//$this->assertSame(0, $parser->depth);
$parser->goIntoArray();
//$this->assertSame(1, $parser->depth);
//$this->assertSame(binson::TYPE_ARRAY, $parser->getType());
$parser->leaveArray();
//$this->assertSame(0, $parser->depth);
//$parser->next();
//$this->assertSame(true, $parser->isDone());
echo "done:".(int)$parser->isDone();



//$p->advance_test1(BinsonParser::ADVANCE_TRAVERSAL, null, 0, null, null);




?>

