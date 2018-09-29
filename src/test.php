<?php

 include_once __DIR__.'/binson.php';


//$buf = "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43";
//$buf = "\x42\x42\x42\x43\x43\x42\x42\x43\x43\x43";
$buf = "\x42\x12\xff\xff\xff\x7f\x43";

$b = "";
$writer = new BinsonWriter($b);

//$src = ["abc", "dcd", ["ddf",["xc"]], "err"];
//$src = [[null=>null],1,true];
$src = ["ac", [false],3,2];
//$src = [true];
//["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7]
// 401401614244107b140162100543 140162 45 140163 100741'
// 401401614244107b140162100543 45 100741'
$writer->put($src);
echo "serialized: ".bin2hex($b).PHP_EOL;

$p = new BinsonParser($b);

$encdec = $p->toString();
echo PHP_EOL;

echo "json_of_orig:\t\t".json_encode($src).PHP_EOL;
echo "toString() --------> \t".$encdec.PHP_EOL;
//echo "json_of_orig: ".str_replace("\"\":null", "", json_encode($src)).PHP_EOL;

$p->reset();
$aaa = $p->deserialize();

print_r("json_of_deserialized:\t".json_encode($aaa).PHP_EOL);
//var_dump($aaa);
//print_r($p->deserialize());

//$p->advance_test1(BinsonParser::ADVANCE_TRAVERSAL, null, 0, null, null);




?>

