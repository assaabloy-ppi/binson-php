/*TEST(verify_complex_object)
{
    /*
    {
      "A": "B", 
      "B": {
        "A": "B"
      }, 
      "C": [
        "A",
        "A",
        {
          "A": "B", 
          "B": [
            "A",
            "A",
            {
              "A": "B"
            },
            [
              [
                [
                  [
                    {
                      "A": "B"
                    }
                  ]
                ]
              ]
            ]
          ]
        },
        "A"
      ], 
      "D": 3.141592653589793, 
      "E": false, 
      "F": 127, 
      "G": "0x0202"
    }
    */
    uint8_t binson_bytes[104] = "\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x40\x14\x01\x41\x14\x01\x42\x41\x14\x01\x43\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x14\x01\x42\x42\x14\x01\x41\x14\x01\x41\x40\x14\x01\x41\x14\x01\x42\x41\x42\x42\x42\x42\x40\x14\x01\x41\x14\x01\x42\x41\x43\x43\x43\x43\x43\x41\x14\x01\x41\x43\x14\x01\x44\x46\x18\x2d\x44\x54\xfb\x21\x09\x40\x14\x01\x45\x45\x14\x01\x46\x10\x7f\x14\x01\x47\x18\x02\x02\x02\x41";
    binson_parser p;



        /*
     * input = {
     *   A := "B",
     *   B := [
     *     "A",
     *     {}
     *   ],
     *   C := {
     *     A := {
     *       A := 2
     *     }
     *   }
     * }
     */
    uint8_t buffer2[33] = { /* 401401411401421401424214014140414314014340140141401401411002414141*/
        0x40, 0x14, 0x01, 0x41, 0x14, 0x01, 0x42, 0x14,
        0x01, 0x42, 0x42, 0x14, 0x01, 0x41, 0x40, 0x41,
        0x43, 0x14, 0x01, 0x43, 0x40, 0x14, 0x01, 0x41,
        0x40, 0x14, 0x01, 0x41, 0x10, 0x02, 0x41, 0x41,
        0x41
    };

    binson_parser_init(&p, buffer2, sizeof(buffer2));
    ASSERT_TRUE(binson_parser_verify(&p));
    ASSERT_TRUE(binson_parser_go_into_object(&p));
    ASSERT_TRUE(binson_parser_field_ensure(&p, "B", BINSON_TYPE_ARRAY));
    ASSERT_TRUE(binson_parser_field_ensure(&p, "C", BINSON_TYPE_OBJECT));

    */

    // {"b":[true,13,"cba",{"abc":false, "b":"0x008100ff00", "cba":"abc"},9223372036854775807]}  