<?php
/**
 * src/Traits/EncryptedAttribute.php.
 */

namespace ESolution\DBEncryption\Traits;

use ESolution\DBEncryption\Builders\EncryptionEloquentBuilder;
use ESolution\DBEncryption\Encrypter;

trait EncryptedAttribute
{
    /**
     * @param $key
     *
     * @return bool
     */
    public function isEncryptable($key)
    {
        if (config('laravelDatabaseEncryption.enable_encrypt')) {
            return in_array($key, $this->encryptable);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getEncryptableAttributes()
    {
        return $this->encryptable;
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if ($this->isEncryptable($key) && (! is_null($value) && $value != '')) {
            $value = Encrypter::decrypt($value);
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if ($this->isEncryptable($key)) {
            $value = Encrypter::encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                if ($this->isEncryptable($key) && (! is_null($value)) && $value != '') {
                    $attributes[$key] = $value;
                    $attributes[$key] = Encrypter::decrypt($value);
                }
            }
        }

        return $attributes;
    }

    // Extend EncryptionEloquentBuilder
    public function newEloquentBuilder($query)
    {
        return new EncryptionEloquentBuilder($query);
    }

    public function decryptAttribute($value)
    {
        return $value ? Encrypter::decrypt($value) : '';
    }

    public function encryptAttribute($value)
    {
        return $value ? Encrypter::encrypt($value) : '';
    }
}
