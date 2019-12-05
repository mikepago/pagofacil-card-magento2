<?php

declare(strict_types=1);

namespace PagoFacil\Payment\Source\Client;

use InvalidArgumentException;
use PagoFacil\Payment\Exceptions\HttpException;
use PagoFacil\Payment\Exceptions\PaymentException;
use PagoFacil\Payment\Source\Client\ClientInterface as HTTPInterface;
use PagoFacil\Payment\Source\Client\Interfaces\PagoFacilResponseInterface;

class Response implements PagoFacilResponseInterface
{
    /** @var int $statusCode */
    private $statusCode;
    /** @var string $body */
    private $body;
    /** @var array $arrayTransaction */
    private $arrayTransaction;

    /**
     * Response constructor.
     * @param string $body
     * @param int $statusCode
     * @throws InvalidArgumentException
     */
    public function __construct(string $body, int $statusCode)
    {
        $this->validateStatusCodeRange($statusCode);
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return string
     * @throws HttpException
     */
    public function getStatusCodeText(int $statusCode):string
    {
        if (!array_key_exists($statusCode, HTTPInterface::PHRASES)) {
            throw new HttpException('invalid_http_code');
        }

        return HTTPInterface::PHRASES[$statusCode];
    }

    /**
     * @return mixed|\Psr\Http\Message\StreamInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $code
     * @throws InvalidArgumentException
     */
    protected function validateStatusCodeRange(int $code): void
    {
        if (100 > $code || 600 <= $code) {
            throw new \InvalidArgumentException('status code out of the range');
        }
    }

    private function parceJsonToArray(): void
    {
        $arrayResponse = json_decode($this->body, true);
        $this->arrayTransaction = $arrayResponse['WebServices_Transacciones'];
    }

    /**
     * @throws PaymentException
     */
    public function validateAuthorized(): void
    {
        if (!array_key_exists('autorizacion', $this->arrayTransaction['transaccion'])) {

            throw PaymentException::denied($this->arrayTransaction['transaccion']['pf_message']);

        }
    }
}
