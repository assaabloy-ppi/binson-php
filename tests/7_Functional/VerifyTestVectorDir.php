<?php declare(strict_types=1);

require_once(__DIR__ . '/../../src/binson.php');

function testIt(string $raw, bool $expect_valid) : bool
{
    return (binson_verify($raw))? $expect_valid : !$expect_valid;
}

$src_dir = getcwd()."/".$argv[1];

echo PHP_EOL."=================================".PHP_EOL;
echo "Binson binary files located at: ".$src_dir.PHP_EOL;
echo "Expected to be: ".$argv[2].PHP_EOL;
echo "=================================".PHP_EOL;

switch ($argv[2]) {
    case 'valid':   
        $src_dir .= '/valid_objects';
        $expect_valid = true;
        break;

    case 'invalid':
        $src_dir .= '/bad_objects';
        $expect_valid = false;
        break;

    default:
        throw new RuntimeException();
}

$cnt = 0;
$dir = new DirectoryIterator($src_dir);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        //echo "Test data file name: ".$fileinfo->getPathname().PHP_EOL;
        $raw = file_get_contents($fileinfo->getPathname());

        if (!testIt($raw, $expect_valid))
        {
            echo "TEST FAILURE !!!".PHP_EOL;
            echo "Hex: ".bin2hex($raw).PHP_EOL.PHP_EOL;
            //exit(1);
        }    
    }
    $cnt++;
}

echo $cnt." test vectors processed.".PHP_EOL;