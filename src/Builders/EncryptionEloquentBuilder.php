<?php
/**
 * src/Builders/EncryptionEloquentBuilder.php.
 */

namespace ESolution\DBEncryption\Builders;

use ESolution\DBEncryption\Encrypter;
use Illuminate\Database\Eloquent\Builder;
use stdClass;

class EncryptionEloquentBuilder extends Builder
{
    public function whereEncrypted($param1, $param2, $param3 = null)
    {
        $filter = new stdClass();
        $filter->field = $param1;
        $filter->operation = isset($param3) ? $param2 : '=';
        $filter->value = isset($param3) ? $param3 : $param2;

        $salt = Encrypter::getKey();

        return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }

    public function orWhereEncrypted($param1, $param2, $param3 = null)
    {
        $filter = new stdClass();
        $filter->field = $param1;
        $filter->operation = isset($param3) ? $param2 : '=';
        $filter->value = isset($param3) ? $param3 : $param2;

        $salt = Encrypter::getKey();

        return self::orWhereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }
}
