<?php

declare(strict_types=1);
//namespace org\binson;

abstract class binson {
    const BINSON_API_VERSION = 'binson_php_v0.0.1a';

    /* ported from stdint.h */
    const INT8_MIN   = (-0x7f - 1);
    const INT16_MIN  = (-0x7fff - 1);
    const INT32_MIN  = (-0x7fffffff - 1);
    const INT64_MIN  = (-0x7fffffffffffffff - 1);
    const INT8_MAX   = 0x7f;
    const INT16_MAX  = 0x7fff;
    const INT32_MAX  = 0x7fffffff;
    const INT64_MAX  = 0x7fffffffffffffff;

    const BINSON_OBJECT_MINIMUM_SIZE  = 2;

    const BINSON_DEF_OBJECT_BEGIN     = 0x40;
    const BINSON_DEF_OBJECT_END       = 0x41;
    const BINSON_DEF_ARRAY_BEGIN      = 0x42;
    const BINSON_DEF_ARRAY_END        = 0x43;
    const BINSON_DEF_TRUE             = 0x44;
    const BINSON_DEF_FALSE            = 0x45;
    const BINSON_DEF_DOUBLE           = 0x46;
    const BINSON_DEF_INT8             = 0x10;
    const BINSON_DEF_INT16            = 0x11;
    const BINSON_DEF_INT32            = 0x12;
    const BINSON_DEF_INT64            = 0x13;
    const BINSON_DEF_STRINGLEN_INT8   = 0x14;
    const BINSON_DEF_STRINGLEN_INT16  = 0x15;
    const BINSON_DEF_STRINGLEN_INT32  = 0x16;
    const BINSON_DEF_BYTESLEN_INT8    = 0x18;
    const BINSON_DEF_BYTESLEN_INT16   = 0x19;
    const BINSON_DEF_BYTESLEN_INT32   = 0x1A;

    const BINSON_TYPE_NONE            = 0;
    const BINSON_TYPE_OBJECT          = 1;
    const BINSON_TYPE_OBJECT_END      = 2;
    const BINSON_TYPE_ARRAY           = 3;
    const BINSON_TYPE_ARRAY_END       = 4;
    const BINSON_TYPE_BOOLEAN         = 5;
    const BINSON_TYPE_INTEGER         = 6;
    const BINSON_TYPE_DOUBLE          = 7;
    const BINSON_TYPE_STRING          = 8;
    const BINSON_TYPE_BYTES           = 9;

    const BINSON_ERROR_NONE           = 0;
    const BINSON_ERROR_RANGE          = 1;
    const BINSON_ERROR_FORMAT         = 2;
    const BINSON_ERROR_EOF            = 3;
    const BINSON_ERROR_END_OF_BLOCK   = 4;
    const BINSON_ERROR_NULL           = 5;
    const BINSON_ERROR_STATE          = 6;
    const BINSON_ERROR_WRONG_TYPE     = 7;
    const BINSON_ERROR_MAX_DEPTH      = 8;
}



class BinsonWriter
{
    private $data_len;
	private $data;
    
    public function __construct(string &$dst = null)
    {
    	$this->data = &$dst ?? '';
        $this->data_len = strlen($this->data);
    }

    public function objectBegin() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_OBJECT, binson::BINSON_DEF_OBJECT_BEGIN);
    	return $this;
    }

    public function objectEnd() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_OBJECT_END, binson::BINSON_DEF_OBJECT_END);
    	return $this;
    }

    public function arrayBegin() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_ARRAY, binson::BINSON_DEF_ARRAY_BEGIN);
    	return $this;
    }

    public function arrayEnd() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_ARRAY_END, binson::BINSON_DEF_ARRAY_END);
    	return $this;
    }

    public function putBoolean(bool $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_BOOLEAN, $val? binson::BINSON_DEF_TRUE : binson::BINSON_DEF_FALSE);
    	return $this;
    }

    public function putTrue() : BinsonWriter
    {   
        return $this->putBoolean(true);
    }

    public function putFalse() : BinsonWriter
    {
        return $this->putBoolean(false);
    }

    public function putInteger(int $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_INTEGER, $val);
    	return $this;
    }

    public function putDouble(float $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_DOUBLE, $val);
    	return $this;
    }

    public function putString(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_STRING, $val);
    	return $this;
    }

    public function putName(string $val) : BinsonWriter
    {
    	return $this->putString($val);
    }

    public function putBytes(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_TYPE_BYTES, $val);
    	return $this;
    }

    public function putInline(BinsonWriter $src_writer) : BinsonWriter
    {
    	$this->data .= $src_writer->data;
    	return $this;
    }

	public function length() : int
    {
    	return strlen($this->data) - $this->data_len;
    }

	public function counter() : int
    {
    	return $this->length();
    }


    public function toBytes() : string
    {
    	return substr($this->data, $this->data_len);
    }

    public function verify() : bool
    {
        //$p = new BinsonParser($this->toBytes());
        //return $p->verify();
    }


    /*======= Private method implementations ====================================*/

    private function writeToken(int $token_type, $val = null) : void
    {
        switch ($token_type) {
                case binson::BINSON_TYPE_OBJECT:
                case binson::BINSON_TYPE_OBJECT_END:
                case binson::BINSON_TYPE_ARRAY:
                case binson::BINSON_TYPE_ARRAY_END:
                case binson::BINSON_TYPE_BOOLEAN:
                    $this->data .= chr($val);
                    return;

                case binson::BINSON_TYPE_DOUBLE:
                case binson::BINSON_TYPE_INTEGER:
                    $this->data .= util_pack_size($val, $token_type);
                    return;

                case binson::BINSON_TYPE_STRING:
                case binson::BINSON_TYPE_BYTES:
                    $this->data .= util_pack_size(strlen($val), $token_type);
                    $this->data .= $val;
                    return;

                default:
                    throw_binson_exception(binson::BINSON_ERROR_STATE);
            }
    }
}

class BinsonParser
{
}


function util_pack_size($val, int $type_hint) : string
{
    $val_bytes = array_fill(0, 9, 0);
    $size = 0;
    $val_unpack_code = 'P'; // 64bit unsigned LE

    switch ($type_hint)
    {
        case binson::BINSON_TYPE_INTEGER:
            $val_bytes[0] = binson::BINSON_DEF_INT8; break;            
        case binson::BINSON_TYPE_DOUBLE:
            $val_bytes[0] = binson::BINSON_DEF_DOUBLE; 
            $val_unpack_code = 'e'; // 64bit double LE
            break;
        case binson::BINSON_TYPE_STRING:
            $val_bytes[0] = binson::BINSON_DEF_STRINGLEN_INT8; break;
        case binson::BINSON_TYPE_BYTES:
            $val_bytes[0] = binson::BINSON_DEF_BYTESLEN_INT8; break;

        default: break;
    }


    if ($type_hint == binson::BINSON_TYPE_DOUBLE) {
        $size = 8;
    }
    else {
        if (($val >= binson::INT8_MIN) && ($val <= binson::INT8_MAX)) {
            $size = 1; // sizeof(int8_t);
        }
        else if (($val >= binson::INT16_MIN) && ($val <= binson::INT16_MAX)) {
            $val_bytes[0] += 1;
            $size = 2; // sizeof(int16_t);
        }
        else if (($val >= binson::INT32_MIN) && ($val <= binson::INT32_MAX)) {
            $val_bytes[0] += 2;
            $size = 4; // sizeof(int32_t);
        }
        else {
            $size = 8; // sizeof(int64_t);
            $val_bytes[0] += 3;
        }
    }

    return chr($val_bytes[0]) . substr(pack($val_unpack_code, $val), 0, $size);
}

function throw_binson_exception(int $exc_code) : void
{
    switch ($exc_code) {
        case binson::BINSON_ERROR_NONE:
            return;

        case binson::BINSON_ERROR_RANGE:        $msg = 'Range error (buffer is full)'; break;
        case binson::BINSON_ERROR_FORMAT:       $msg = 'Format error'; break;
        case binson::BINSON_ERROR_EOF:          $msg = 'End of file detected'; break;
        case binson::BINSON_ERROR_END_OF_BLOCK: $msg = 'End of block detected'; break;
        case binson::BINSON_ERROR_NULL:         $msg = 'NULL ref'; break;
        case binson::BINSON_ERROR_STATE:        $msg = 'Wrong state'; break;
        case binson::BINSON_ERROR_WRONG_TYPE:   $msg = 'Wrong type'; break;
        case binson::BINSON_ERROR_MAX_DEPTH:    $msg = 'Max nesting depth reached'; break;

        default: 
            $msg = 'Unknown binson exception with code: ' . $exc_code; break;
    }

    throw new Exception($msg, $exc_code);
}


?>


 