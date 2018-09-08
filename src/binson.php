<?php

declare(strict_types=1);
//namespace org\binson;

abstract class binson {
    const BINSON_API_VERSION = 'binson_php_v0.0.1a';

    const BINSON_ID_OBJECT         = 0x01;
    const BINSON_ID_ARRAY          = 0x02;
    const BINSON_ID_BLOCK          = 0x03;  /* Meta ID. BINSON_ID_OBJECT or BINSON_ID_ARRAY */

    const BINSON_ID_OBJ_BEGIN      = 0x40;
    const BINSON_ID_OBJ_END        = 0x41;
    const BINSON_ID_ARRAY_BEGIN    = 0x42;
    const BINSON_ID_ARRAY_END      = 0x43;

    const BINSON_ID_BOOLEAN        = 0x47;  /* Meta ID. Translated to one of 2 next consts during serialization */
    const BINSON_ID_TRUE           = 0x44;
    const BINSON_ID_FALSE          = 0x45;
    const BINSON_ID_DOUBLE         = 0x46;

    const BINSON_ID_INTEGER        = 0x0f;  /* Meta ID. Translated to one of 4 next consts during serialization */
    const BINSON_ID_INTEGER_8      = 0x10;
    const BINSON_ID_INTEGER_16     = 0x11;
    const BINSON_ID_INTEGER_32     = 0x12;
    const BINSON_ID_INTEGER_64     = 0x13;

    const BINSON_ID_STRING         = 0x12;  /* Meta ID. Translated to one of 4 next consts during serialization */
    const BINSON_ID_STRING_LEN     = 0x13; /* indicates stringLen part of STRING object */
    const BINSON_ID_STRING_8       = 0x14;
    const BINSON_ID_STRING_16      = 0x15;
    const BINSON_ID_STRING_32      = 0x16;

    const BINSON_ID_BYTES          = 0x16;  /* Meta ID. Translated to one of 3 next consts during serialization */
    const BINSON_ID_BYTES_LEN      = 0x17; /* indicates bytesLen part of BYTES object */
    const BINSON_ID_BYTES_8        = 0x18;
    const BINSON_ID_BYTES_16       = 0x19;
    const BINSON_ID_BYTES_32       = 0x1a;    
}


//custom exceptions ???


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
    	$this->writeToken(binson::BINSON_ID_OBJ_BEGIN);
    	return $this;
    }

    public function objectEnd() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_OBJ_END);
    	return $this;
    }

    public function arrayBegin() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_ARRAY_BEGIN);
    	return $this;
    }

    public function arrayEnd() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_ARRAY_END);
    	return $this;
    }

    public function putBoolean(bool $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_BOOLEAN, $val);
    	return $this;
    }

    public function putTrue() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_BOOLEAN, true);
    	return $this;
    }

    public function putFalse() : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_BOOLEAN, false);
    	return $this;
    }

    public function putInteger(int $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_INTEGER, $val);
    	return $this;
    }

    public function putDouble(float $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_DOUBLE, $val);
    	return $this;
    }

    public function putString(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_STRING, $val);
    	return $this;
    }

    public function putName(string $val) : BinsonWriter
    {
    	return $this->putString($val);
    }

    public function putBytes(string $val) : BinsonWriter
    {
    	$this->writeToken(binson::BINSON_ID_BYTES, $val);
    	return $this;
    }

    public function putInline(BinsonWriter $sub_writer) : BinsonWriter
    {
    	$this->data .= $sub_writer->data;
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


    /*private methods */

    private function writeToken(int $token_type, $val = null) : void
    {
       switch ($token_type) {

        case binson::BINSON_ID_INTEGER:
        case binson::BINSON_ID_STRING_LEN:
        case binson::BINSON_ID_BYTES_LEN:
        case binson::BINSON_ID_DOUBLE: {
            /*uint8_t pack_buf[sizeof(int64_t) + 1];

            if (!val) {
                res = BINSON_ID_INVALID_ARG;
                break;
            }*/

            /*isize = _binson_util_pack_integer(val->int_val, &(pack_buf[1]), (token_type == BINSON_ID_DOUBLE) ? 1 : 0);
            pack_buf[0] = (uint8_t)(token_type
                                    + ((token_type == BINSON_ID_DOUBLE) ? 0 : _binson_util_sizeof_idx((uint8_t)isize)));
            isize++;
            res = _binson_io_write(&(pwriter->io), pack_buf, isize);*/

            break;
        }

        case binson::BINSON_ID_STRING:
        case binson::BINSON_ID_BYTES: {
            /*if (!val) {
                res = BINSON_ID_INVALID_ARG;
                break;
            }

            binson_tok_size tok_size = val->bbuf_val.bsize;
            binson_value tval;

            tval.int_val = tok_size;

            _binson_writer_write_token(pwriter, (uint8_t)(token_type + 1), &tval); // writes type+len/
            res = _binson_io_write(&(pwriter->io),
                                   val->bbuf_val.bptr,
                                   tok_size); // writes payload: string (without \0) or bytearray 
            */
            break;
        }

        case binson::BINSON_ID_OBJ_BEGIN:
        case binson::BINSON_ID_ARRAY_BEGIN:
        case binson::BINSON_ID_OBJ_END:
        case binson::BINSON_ID_ARRAY_END:
        case binson::BINSON_ID_TRUE:
        case binson::BINSON_ID_FALSE:
            $this->data .= chr($token_type);
            break;

        case binson::BINSON_ID_BOOLEAN:            
            /*if (!val) {
                res = BINSON_ID_INVALID_ARG;
            }
            else {
                res = _binson_io_write_byte(&(pwriter->io), val->bool_val ? BINSON_ID_TRUE : BINSON_ID_FALSE);
            }*/
            $this->data .= $val ? chr(binson::BINSON_ID_TRUE) : chr(binson::BINSON_ID_FALSE);
            break;
        }
    }    
}

class BinsonParser
{
}

function util_sizeof_idx(int $n) : int
{
    $idx = $n;

    if ($n == 4) {
        $idx = 3;
    }
    else if ($n > 4) {
        $idx = 4;
    }

    return $idx;
}

function util_pack_integer(int $val, bool $is_double) : array
{
    //define( "$const" , "$value ",$sensitivity);
    /*const INT8_MIN   = (-0x7f - 1);
    const INT16_MIN  = (-0x7fff - 1);
    const INT32_MIN  = (-0x7fffffff - 1);
    const INT64_MIN  = (-0x7fffffffffffffff - 1);
    const INT8_MAX   = 0x7f;
    const INT16_MAX  = 0x7fff;
    const INT32_MAX  = 0x7fffffff;
    const INT64_MAX  = 0x7fffffffffffffff;

    $val_bytes = range(0,7);
    $size = 0;

    if ($is_double) {
        $size = 8;
    }
    else {
        if (($val >= INT8_MIN) && ($val <= INT8_MAX)) {
            size = 1; // sizeof(int8_t);
        }
        else if (($val >= INT16_MIN) && ($val <= INT16_MAX)) {
            buffer[0] += 1;
            size = 2; // sizeof(int16_t);
        }
        else if (($val >= INT32_MIN) && ($val <= INT32_MAX)) {
            buffer[0] += 2;
            size = 4; // sizeof(int32_t);
        }
        else {
            size = 8; // sizeof(int64_t);
            buffer[0] += 3;
        }
    }

   return array($pbuf, $size);


////////





    uint64_t uval = (uint64_t) length;

    uint8_t i;
    for (i = 0; i < size; i++) {
        buffer[1 + i] = (uint8_t) (uval & 0xFFU);
        uval >>= 8U;
    }

    return 1 + size;*/


}


?>


 