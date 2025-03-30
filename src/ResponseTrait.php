<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;


trait ResponseTrait {


    use MessageTrait;


    public function getReasonPhrase() : string {
        return $this->getResponse()->getReasonPhrase();
    }


    public function getStatusCode() : int {
        return $this->getResponse()->getStatusCode();
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withStatus( int $code, string $reasonPhrase = '' ) : static {
        return $this->cloneResponse( $this->getResponse()->withStatus( $code, $reasonPhrase ) );
    }


    abstract public function getResponse() : ResponseInterface;


    /** @suppress PhanTypeMismatchReturn */
    protected function cloneMessage( MessageInterface $response ) : static {
        assert( $response instanceof ResponseInterface );
        return $this->cloneResponse( $response );
    }


    abstract protected function cloneResponse( ResponseInterface $response ) : static;


    protected function getMessage() : MessageInterface {
        return $this->getResponse();
    }


}