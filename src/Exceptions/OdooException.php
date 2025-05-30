<?php


namespace Obuchmann\OdooJsonRpc\Exceptions;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class OdooException extends RuntimeException
{

    /**
     * OdooException constructor.
     * @param ResponseInterface|null $response
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    #[Pure] public function __construct(protected ?ResponseInterface $response, string $message = "", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
