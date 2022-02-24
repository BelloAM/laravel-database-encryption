<?php
/**
 * src/Encryption.php.
 */

namespace ESolution\DBEncryption;

class Encrypter
{
    public static function encrypt($value): string
    {
        $randomIv = self::randomIv();

        return $randomIv . openssl_encrypt($value, config('laravelDatabaseEncryption.encrypt_method'), self::getKey(), 0, $iv = $randomIv);
    }

    public static function decrypt($value): string
    {
        return openssl_decrypt($value, config('laravelDatabaseEncryption.encrypt_method'), self::getKey(), 0, $iv = self::randomIv());
    }

    public static function getKey(): string
    {
        return substr(hash(config('laravelDatabaseEncryption.hash_method'), config('laravelDatabaseEncryption.encrypt_key')), 0, 16);
    }

    private static function randomIv(): string
    {
        return bin2hex(random_bytes(8));
    }
}
