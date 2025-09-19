<?php

namespace Buyme\MadelineProtoIntegration\Facades;

use Illuminate\Support\Facades\Facade;

class MPIChannel extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'mpi-channel';
	}
}