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
		'token',
		'is_banned',
		'banned_at',
	];

	protected function casts(): array
	{
		return [
			'is_banned' => 'boolean',
			'banned_at' => 'datetime',
		];
	}

	protected function password(): Attribute
	{
		return Attribute::make(
			get: fn ($value) => Crypt::decryptString($value),
			set: fn ($value) => Crypt::encryptString($value),
		);
	}

	public function markAsBanned(): void
	{
		$this->update([
			'is_banned' => true,
			'banned_at' => now(),
		]);
	}

	public function markAsUnbanned(): void
	{
		$this->update([
			'is_banned' => false,
			'banned_at' => null,
		]);
	}
}
