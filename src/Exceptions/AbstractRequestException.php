<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Message\RequestInterface;
use Throwable;


abstract class AbstractRequestException extends ClientException {


    public function __construct( private readonly RequestInterface $request, string $message = '', int $code = 0,
                                 ?Throwable                        $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


    /**
     * @suppress PhanTypeInstantiateAbstractStatic,PhanUndeclaredMethod
     */
    public static function from( Throwable $i_ex, ?RequestInterface $i_request = null ) : static {
        if ( $i_ex instanceof static ) {
            return $i_ex;
        }
        # We put this one first because you can control it to decide whether
        # to return the request from a compatible exception or override it
        # with your own.
        if ( $i_request instanceof RequestInterface ) {
            /** @phpstan-ignore new.static */
            return new static( $i_request, $i_ex->getMessage(), $i_ex->getCode(), $i_ex );
        }
        if ( method_exists( $i_ex, 'getRequest' ) ) {
            $request = $i_ex->getRequest();
            /** @noinspection PhpConditionAlreadyCheckedInspection You don't know that! */
            if ( $request instanceof RequestInterface ) {
                /** @phpstan-ignore new.static */
                return new static( $request, $i_ex->getMessage(), $i_ex->getCode(), $i_ex );
            }
        }
        throw new ClientException( 'Request exception without request: ' . $i_ex->getMessage(),
            $i_ex->getCode(), $i_ex );
    }


    public function getRequest() : RequestInterface {
        return $this->request;
    }


}
