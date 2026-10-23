<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MadelineRequestException extends Exception
{
    private ?string $errorCode;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?string $errorCode = null,
    ) {
        $this->errorCode = $errorCode;

        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function render(Request $request): Response
    {
        $data = [
            'message' => $this->getMessage(),
            'message_code' => $this->getErrorCode(),
        ];

        if (config('app.debug')) {
            $data['exception'] = get_class($this);
            $data['file'] = $this->getFile();
            $data['line'] = $this->getLine();
            $data['trace'] = collect($this->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all();
        }

        $status = $this->getCode() >= 400 && $this->getCode() < 600 ? $this->getCode() : Response::HTTP_BAD_GATEWAY;

        return response()->json($data, $status);
    }
}
