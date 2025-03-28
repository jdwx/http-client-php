<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Client\ClientExceptionInterface;


class ClientException extends \RuntimeException implements ClientExceptionInterface {


    /**
     * Used to import a RequestExceptionInterface to make sure it conforms to
     * this hierarchy. Note this method typically has to be reimplemented
     * in any subclass where the constructor signature changes.
     */
    public static function from( \Throwable $i_ex ) : static {
        if ( $i_ex instanceof static ) {
            return $i_ex;
        }
        /** @phpstan-ignore new.static */
        return new static( $i_ex->getMessage(), $i_ex->getCode(), $i_ex );
    }


}
