# binson-php
A PHP implementation of Binson. Binson is an exceptionally simple data serialization format; see binson.org. 

Quick start
-----------

Just place `src/binson.php` into your project's source directory and "require" it.

You need nothing to know about binson to start using it:

```php
$src = ["a"=>[true, 123, "b", 5, binson::BYTES("\x01\x02")], "b"=>false, "c"=>7];

$binson_raw = binson_encode($src);      // encode arbitrary associative array to binary string 
$decoded = binson_decode($binson_raw);  // decode, if possible, from binson-encoded binary string

// now $decoded should be equal to $src
```

To check if random binary string represents valid well-formed binson object:

```php
if (null !== binson_decode($raw))
{
    echo 'valid!';
}
```

Compatibility
--------------

Required PHP version is at least 7.2.x

Known to be stable on following platforms:
* PHP 7.2.8 (Ubuntu 14.04.5 LTS, x86_64)

Note: no PHP5 compatibility (by design)

Current status
--------------
* `binson_encode()` and `binson_decode()` API calls are ready!.
* Major code cleanups required
* Major code documentation required (? follow [WordPress inline documentation best practices](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/))
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
* No extension dependencies (should work with any custom PHP7 build)
* Instant serialization/deserialization to/from PHP native arrays
* Declarative "state transition matrix" parsing algorithm
* Error handling is PHP7 exception based (see `BinsonException` class)
* Limited 64bit integer parsing on php32 builds supported (see [below](#limitation-64bit-integer-support))

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
make test.serializer
make test.deserializer
```

PHP native arrays serialization constraints:
---------------------------
Not all arbitrary multilevel arrays are serializable. Next rules should be applied to make sure array is serializer-compatible:
* For values only primitive types `bool`, `integer`, `float`, `string`, `array` are allowed. 
* When use instance of the class `BinsonWriter` as value, it's  content (bytes) will be placed inline, without any framing.
* For field names only `string` is allowed. Numeric field names must have dot suffix. E.g. PHP array `['3.' => true]` will be translated to binson object `{'3' => true}`, then to raw byte sequence: `\x40\x14\x01\x33\x44\x41`.


BinsonWriter class usage examples:
-----------

Typical usage (serialization):
```PHP
$writer = new BinsonWriter();
$writer->put( ["a"=>[true, 123, "b", 5, binson::BYTES("\x01\x02")], "b"=>false, "c"=>7] );
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
$writer->put([]);                      // [] - empty binson array
$writer->put([[]]);                    // [[]] - nested empty arrays
$writer->put(binson::EMPTY_OBJECT);    // {} - empty binson object
$writer->put([binson::EMPTY_OBJECT]);  // [{}] - empty object inside the empty array
```
&nbsp;  

Include external writer:
```PHP
$writer_external->put(['a'=>'b']);
...
$writer->put(['ext'=>$writer_external]); 
```

Include raw bytes:
```PHP
$writer_external->putRaw("\x42\x44\x43");
...
$writer->put(['ext'=>$writer_external]); 
```

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

Include external writer:
```PHP
 $writer_ext->arrayBegin()
                ->putFalse()
            ->arrayEnd();

$writer->arrayBegin()
            ->putInline($writer_ext)
       ->arrayEnd();
```

Include raw bytes:
```PHP

$writer->arrayBegin()
            ->putRaw("\x42\x45\x43")
       ->arrayEnd();
```


BinsonParser class usage examples:
-----------

Low-level API:
```PHP
// {"a":[true,123,"b",5],"b":false,"c":7}
$buf = "\x40\x14\x01\x61\x42\x44\x10\x7b\x14\x01\x62\x10\x05\x43\x14\x01\x62\x45\x14\x01\x63\x10\x07\x41";
$parser = new BinsonParser($buf);

$parser->enterObject();
$parser->field("a");
$parser->enterArray();
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

Limitation: 64bit integer support
---------------------------------

64bit integers are fully supported on 64bit PHP7 builds.

Unfortunately, 32bit PHP do not support 64bit integers in its core.
This library implements limited support for parsing (not writing) of 64bit integer via fallback to float type.

By default this support is disabled. When trying to parse int64 field on php32 `BinsonException` (with error code `binson::ERROR_INT_OVERFLOW`) will be thrown.

To enable instant int64 to float conversion during parsing, parser configuration should be updated in runtime.

The code to illustrate above:
```php
$buf = "\x42\x13\x00\x00\x00\x00\x00\x40\x00\x00\x43";  // 2<<45
$parser = new BinsonParser($buf);

// expect integer overflow on 32bit PHP builds
$parser->config['parser_int_overflow_action'] = 'to_float';

$parser->enterArray()->next();

if (PHP_INT_SIZE === 4)  // we are on php32
{
    $val = $parser->getValue(binson::TYPE_INTEGER);  // type check argument is optional 
    if (is_float($val))
        print_r($val);
}
```

There are no guarantee to preserve all significant digits for numbers above 2^53 (9007199254740992) and below -2^53-1 (-9007199254740993). 

> With the 52 bits of the fraction significand appearing in the memory format, the total precision is therefore 53 bits (approximately 16 decimal digits).... Between 2<sup>52</sup>=4,503,599,627,370,496 and 2<sup>53</sup>=9,007,199,254,740,992 the representable numbers are exactly the integers. For the next range, from 2<sup>53</sup> to 2<sup>54</sup>, everything is multiplied by 2, so the representable numbers are the even ones, etc. Conversely, for the previous range from 2<sup>51</sup> to 2<sup>52</sup>, the spacing is 0.5, etc.

Source:  [wikipedia](https://en.wikipedia.org/wiki/Double-precision_floating-point_format#IEEE_754_double-precision_binary_floating-point_format:_binary64)



## Changelog

#### 2018-10-12

* Minimal PHP version now is 7.2.x;  previously claimed backward compatibility with lower versions now is cancaled.

#### 2018-10-08

* Library fixed to be backward compatible with PHP 7.0.x


#### 2018-10-06

* Added ability to mark string values to be encoded into BYTES binson type instead of default STRING by wrapping strings with `binson::BYTES()`.


#### 2018-10-04

* Added functionality to place inline data from external writer in both log and high level API. See: `BinsonWriter::putRaw()` and `BinsonWriter::putInline()`.


#### 2018-09-30

* top-level functions `binson_encode()` and `binson_decode()` are implemented (as wrappers around `BinsonWriter` & `BinsonParser`)
