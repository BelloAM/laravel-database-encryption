<?php
/**
 * src/Encryption.php.
 */

namespace Hatcher\DBEncryption;

use Exception;

class Encrypter
{
    /**
     * Encrypt.
     *
     * @param $value
     *
     * @return string
     * @throws Exception
     */
    public static function encrypt($value): string
    {
        $randomIv = self::randomIv();
        return $randomIv . openssl_encrypt($value, self::getMethod(), self::getKey(), 0, $iv = $randomIv);
    }

    /**
     * Decrypt.
     *
     * @param $value
     *
     * @return string
     * @throws Exception
     */
    public static function decrypt($value): string
    {
        return openssl_decrypt($value, self::getMethod(), self::getKey(), 0, $iv = self::randomIv());
    }

    /**
     * Get Key.
     *
     * @return string
     * @throws Exception
     */
    public static function getKey(): string
    {
        if(empty(config('laravelDatabaseEncryption.encrypt_key'))) throw new Exception('Encryption key is not set');
        return substr(hash(self::getMethod(), config('laravelDatabaseEncryption.encrypt_key')), 0, 16);
    }

    private static function getMethod(){
        if(empty(config('laravelDatabaseEncryption.hash_method'))) throw new Exception('Hash method is not set');
        return config('laravelDatabaseEncryption.hash_method');
    }

    /**
     * Random IV.
     * @throws Exception
     */
    private static function randomIv(): string
    {
        return bin2hex(random_bytes(8));
    }
}
