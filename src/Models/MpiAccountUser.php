<?php

namespace Buyme\MadelineProtoIntegration\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MpiAccountUser extends Model
{
	protected $fillable = [
		'login',
		'password',
		'token'
	];

	protected function password(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => Crypt::decryptString($value),
			set: fn ($value) => Crypt::encryptString($value),
		);
	}

}
