<?php

namespace Hatcher\DBEncryption\Tests;

use Illuminate\Database\Eloquent\Model;
use Hatcher\DBEncryption\Traits\EncryptedAttribute;
use Hatcher\DBEncryption\Tests\Database\Factories\TestUserFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestUser extends Model
{
    use HasFactory;
    use EncryptedAttribute;

    protected $fillable = ['email', 'name', 'password'];
    protected $encryptable = ['email', 'name'];
    protected $camelcase = ['name'];

    protected static function newFactory()
    {
        return TestUserFactory::new();
    }
}