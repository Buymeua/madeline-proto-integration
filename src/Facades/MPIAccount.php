<?php

namespace Buyme\MadelineProtoIntegration\Facades;

use Illuminate\Support\Facades\Facade;

class MPIAccount extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'mpi-account';
	}
}
