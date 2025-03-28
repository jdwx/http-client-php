<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;


class AbstractResponseException extends AbstractRequestException implements ResponseExceptionInterface {


    public function __construct( private readonly ResponseInterface $response, RequestInterface $request,
                                 string                             $message = '', int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $request, $message, $code, $previous );
    }


    /** @suppress PhanUndeclaredMethod */
    public static function from( Throwable          $i_ex, ?RequestInterface $i_request = null,
                                 ?ResponseInterface $i_response = null ) : static {
        if ( $i_ex instanceof static ) {
            return $i_ex;
        }
        if ( method_exists( $i_ex, 'getRequest' ) ) {
            $request = $i_ex->getRequest();
            /** @noinspection PhpConditionAlreadyCheckedInspection You don't know that! */
            if ( $request instanceof RequestInterface ) {
                $i_request = $request;
            }
        }
        if ( method_exists( $i_ex, 'getResponse' ) ) {
            $response = $i_ex->getResponse();
            if ( $response instanceof ResponseInterface ) {
                $i_response = $response;
            }
        }
        if ( ! $i_request instanceof RequestInterface ) {
            if ( $i_response instanceof \JDWX\HttpClient\ResponseInterface ) {
                $i_request = $i_response->getRequest();
            } else {
                throw new ClientException( 'Response exception without request: ' . $i_ex->getMessage(),
                    $i_ex->getCode(), $i_ex );
            }
        }
        if ( ! $i_response instanceof ResponseInterface ) {
            throw new ClientException( 'Response exception without response: ' . $i_ex->getMessage(),
                $i_ex->getCode(), $i_ex );
        }
        /** @phpstan-ignore new.static */
        return new static( $i_response, $i_request, $i_ex->getMessage(), $i_ex->getCode(), $i_ex );
    }


    public function getResponse() : ResponseInterface {
        return $this->response;
    }


}
