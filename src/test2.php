<?php

 include_once __DIR__.'/binson.php';

$src = ['a'=>[[true],3], 'b'=>[6.6]];

$raw = binson_encode($src);
$decoded = binson_decode($raw);

echo "original: \t". json_encode($src).PHP_EOL;
echo "transcoded:\t". json_encode($decoded).PHP_EOL;

?>

