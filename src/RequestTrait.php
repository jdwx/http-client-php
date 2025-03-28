<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;


trait RequestTrait {

    use MessageTrait;


    /** @suppress PhanTypeMismatchReturn */
    protected function cloneMessage( MessageInterface $message ) : static {
        assert( $message instanceof RequestInterface );
        return $this->cloneRequest( $message );
    }


    abstract protected function cloneRequest( RequestInterface $request ) : static;


    protected function fromMessage() : MessageInterface {
        return $this->fromRequest();
    }


    abstract protected function fromRequest() : RequestInterface;


    public function getMethod() : string {
        return $this->fromRequest()->getMethod();
    }


    public function getRequestTarget() : string {
        return $this->fromRequest()->getRequestTarget();
    }


    public function getUri() : UriInterface {
        return $this->fromRequest()->getUri();
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withMethod( string $method ) : static {
        return $this->cloneRequest( $this->fromRequest()->withMethod( $method ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withRequestTarget( mixed $requestTarget ) : static {
        return $this->cloneRequest( $this->fromRequest()->withRequestTarget( $requestTarget ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withUri( UriInterface $uri, bool $preserveHost = false ) : static {
        return $this->cloneRequest( $this->fromRequest()->withUri( $uri, $preserveHost ) );
    }


}