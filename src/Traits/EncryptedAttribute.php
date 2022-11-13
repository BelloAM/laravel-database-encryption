<?php
/**
 * src/Traits/EncryptedAttribute.php.
 */

namespace Hatcher\DBEncryption\Traits;

use Exception;
use Hatcher\DBEncryption\Builders\EncryptionEloquentBuilder;
use Hatcher\DBEncryption\Encrypter;

trait EncryptedAttribute
{
    /**
     * is Encryptable
     * @param $key
     * @return bool
     */
    public function isEncryptable($key): bool
    {
        if (config('laravelDatabaseEncryption.enable_encrypt')) {
            return in_array($key, $this->encryptable);
        }

        return false;
    }

    /**
     * Get Encrypted Attribute
     * @return mixed
     */
    public function getEncryptableAttributes()
    {
        return $this->encryptable;
    }

    /**
     * Get Attribute
     * @param $key
     * @return mixed|string
     * @throws Exception
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if ($this->isEncryptable($key) && (! is_null($value) && $value != '')) {
            $value = Encrypter::decrypt($value);
        }

        return $value;
    }

    /**
     * Set Attribute
     * @param $key
     * @param $value
     * @return mixed
     * @throws Exception
     */
    public function setAttribute($key, $value)
    {
        if ($this->isEncryptable($key)) {
            $value = Encrypter::encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Attributes to Array
     * @return array
     * @throws Exception
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                if ($this->isEncryptable($key) && (!is_null($value)) && $value != '') {
                    $attributes[$key] = Encrypter::decrypt($value);
                }
            }
        }

        return $attributes;
    }

    // Extend EncryptionEloquentBuilder

    /**
     * New Eloquent Builder
     * @param $query
     * @return EncryptionEloquentBuilder
     */
    public function newEloquentBuilder($query): EncryptionEloquentBuilder
    {
        return new EncryptionEloquentBuilder($query);
    }

    /**
     * Decrypt Attribute
     * @param $value
     * @return string
     * @throws Exception
     */
    public function decryptAttribute($value): string
    {
        return $value ? Encrypter::decrypt($value) : '';
    }

    /**
     * Encrypt Attribute
     * @param $value
     * @return string
     * @throws Exception
     */
    public function encryptAttribute($value): string
    {
        return $value ? Encrypter::encrypt($value) : '';
    }
}
