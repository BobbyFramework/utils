<?php
namespace BobbyFramework\Utils;

class Crypt
{

    public static function NTLMHash($Input)
    {
        $Input = iconv('UTF-8', 'UTF-16LE', $Input);

        // Encrypt it with the MD4 hash
        $MD4Hash = bin2hex(mhash(MHASH_MD4, $Input));

        $NTLMHash = strtoupper($MD4Hash);

        // Return the result
        return ($NTLMHash);
    }
}