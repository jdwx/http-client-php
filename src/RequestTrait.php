<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;


trait RequestTrait {


    use MessageTrait;


    abstract public function getRequest() : RequestInterface;


    public function getMethod() : string {
        return $this->getRequest()->getMethod();
    }


    public function getRequestTarget() : string {
        return $this->getRequest()->getRequestTarget();
    }


    public function getUri() : UriInterface {
        return $this->getRequest()->getUri();
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withMethod( string $method ) : static {
        return $this->cloneRequest( $this->getRequest()->withMethod( $method ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withRequestTarget( mixed $requestTarget ) : static {
        return $this->cloneRequest( $this->getRequest()->withRequestTarget( $requestTarget ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withUri( UriInterface $uri, bool $preserveHost = false ) : static {
        return $this->cloneRequest( $this->getRequest()->withUri( $uri, $preserveHost ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    protected function cloneMessage( MessageInterface $message ) : static {
        assert( $message instanceof RequestInterface );
        return $this->cloneRequest( $message );
    }


    abstract protected function cloneRequest( RequestInterface $request ) : static;


    protected function getMessage() : MessageInterface {
        return $this->getRequest();
    }


}