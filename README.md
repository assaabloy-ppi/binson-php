# binson-php
A PHP implementation of Binson. Binson is an exceptionally simple data serialization format; see binson.org. 

Quick start
-----------

Just place `src/binson.php` into your project's source directory and "require" it.

Compatibility
--------------
Known to be stable on following platforms:
* PHP 7.1.20 (Ubuntu 14.04.5 LTS, x86_64)

Note: no PHP5 compatibility (by design)

Current status
--------------
* Basic `BinsonWriter` & `BinsonParser` API used in Java/C ports works as expected with no known issues. 
* (De-)serialization is NOT ready to use (some issue fixes required).
* `binson_encode()` and `binson_decode()` API calls are NOT ready.
* Major code cleanups required
* Major code documentation required (? follow [WordPress inline docsumentation best practices](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/))
* Unit test coverage improvement is still required.
* Logging/debug output standartization required.

Known ports
------------
* [binson-java (reference implementation)](https://github.com/franslundberg/binson-java)
* [binson-java-light](https://github.com/franslundberg/binson-java-light)
* [binson-c-light (reference implementation)](https://github.com/assaabloy-ppi/binson-c-light)
* [binson-js](https://github.com/assaabloy-ppi/binson-js)
* [binson-swift](https://github.com/assaabloy-ppi/binson-swift)
* [binson-erlang](https://github.com/assaabloy-ppi/binson-erlang)

Features
-----------
* Iterative parsing (no recursion)
* Declarative "state transition matrix" based parsing algorithm
* Error handling is PHP7 exception based (see `BinsonException` class)

Testing
-----------

Current implementation is not mature, is it why before any usage highly recommended to run full unit test suite.

Initialize environment first:
```
make init
make update
```

Now run PHPUnit tests:
```
make test.writer
make test.parser
```

BinsonWriter class usage examples:
-----------

Typical usage (serialization):
```PHP
$writer = new BinsonWriter();
$writer->put( ["a"=>[true, 123, "b", 5], "b"=>false, "c"=>7] );
```
&nbsp;  
"Streaming" to existing string:
```PHP
$buffer = "";
$writer = new BinsonWriter($buffer);
...
```
&nbsp;  
Serializing multiple variables/literals:
```PHP
$arr = [1,2,3];
$writer->put($arr, ["a"=>1, "b"=>"c"], true);
```
&nbsp;  
Specifying binson OBJECT instead of ARRAY:
```PHP
$writer->put([]);                // [] - empty binson array
$writer->put([null => null]);    // {} - empty binson object
$writer->put([[]]);              // [[]] - nested empty arrays
$writer->put([[null => null]]);  // [{}] - empty object inside the empty array
```
&nbsp;  
Low-level API:
```PHP
$writer->objectBegin();
$writer->objectEnd();

$writer->arrayBegin();
$writer->arrayEnd();

$writer->putBoolean(true);
$writer->putInteger(123);
$writer->putDouble(1.23);
$writer->putName("aaa");
$writer->putString("abc");
$writer->putBytes("\x00\x3f\xff");
$writer->putInline($src_writer);

$len = $writer->length();
$str = $writer->toBytes();
$res = $writer->verify();
```
&nbsp;  
Method chaining:
```PHP
$writer->objectBegin()
            ->putName("aaa")
            ->arrayBegin()
                ->putBoolean(false)
                ->putInteger(123)
            ->arrayEnd()
       ->objectEnd();
```

BinsonParser class usage examples:
-----------

Low-level API:
```PHP
// {"a":[true,123,"b",5],"b":false,"c":7}
$buf = "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41";
$parser = new BinsonParser($buf);

$parser->goIntoObject();
$parser->field("a");
$parser->goIntoArray();
$parser->next();

$out = [];
$out[] = $parser->getValue(binson::TYPE_BOOLEAN);  // type checks are optional
$parser->next();
$out[] = $parser->getValue(binson::TYPE_INTEGER);  // type checks are optional
$parser->next();
$out[] = $parser->getValue(binson::TYPE_STRING);  // type checks are optional
$parser->leaveArray();
$parser->field("c");
$out[] = $parser->getName();
$out[] = $parser->getValue(binson::TYPE_INTEGER);  // type checks are optional
$parser->leaveObject();
$out[] = $parser->isDone();
        
echo implode(PHP_EOL, $out);
```

Will output:
```
1
123
b
c
7
1
```

To check for data before parsing for being valid binson:
```PHP
$is_valid = $parser->verify();
```
