<?php

 include_once __DIR__.'/binson.php';


//$buf = "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43";
//$buf = "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43";
$buf = "\x42\x12\xff\xff\xff\x7f\x43";

$b = "";
$writer = new BinsonWriter($b);

$src = ["abc", "dcd", ["ddf"], "err"];

$writer->put($src);

echo bin2hex($b).PHP_EOL;
$p = new BinsonParser($b);

$encdec = $p->tostr();

echo PHP_EOL;
echo json_encode($src).PHP_EOL;
echo $encdec.PHP_EOL;



//$p->advance_test1(BinsonParser::ADVANCE_TRAVERSAL, null, 0, null, null);




?>

