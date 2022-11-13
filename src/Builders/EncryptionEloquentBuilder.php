<?php
/**
 * src/Builders/EncryptionEloquentBuilder.php.
 */

namespace Hatcher\DBEncryption\Builders;

use Hatcher\DBEncryption\Encrypter;
use Illuminate\Database\Eloquent\Builder;
use stdClass;

class EncryptionEloquentBuilder extends Builder
{
    /**
     * Where Encrypted
     * @param $param1
     * @param $param2
     * @param $param3
     * @return EncryptionEloquentBuilder
     */
    public function whereEncrypted($param1, $param2, $param3 = null): EncryptionEloquentBuilder
    {
        $filter = new stdClass();
        $filter->field = $param1;
        $filter->operation = isset($param3) ? $param2 : '=';
        $filter->value = $param3 ?? $param2;

        $salt = Encrypter::getKey();

        return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }

    /**
     * Or Where Encrypted
     * @param $param1
     * @param $param2
     * @param $param3
     * @return EncryptionEloquentBuilder
     */
    public function orWhereEncrypted($param1, $param2, $param3 = null): EncryptionEloquentBuilder
    {
        $filter = new stdClass();
        $filter->field = $param1;
        $filter->operation = isset($param3) ? $param2 : '=';
        $filter->value = $param3 ?? $param2;

        $salt = Encrypter::getKey();

        return self::orWhereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }
}
