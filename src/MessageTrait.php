<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;


trait MessageTrait {


    public function getBody() : StreamInterface {
        return $this->fromMessage()->getBody();
    }


    public function getHeader( string $name ) : array {
        return $this->fromMessage()->getHeader( $name );
    }


    public function getHeaderLine( string $name ) : string {
        return $this->fromMessage()->getHeaderLine( $name );
    }


    public function getHeaders() : array {
        return $this->fromMessage()->getHeaders();
    }


    public function getProtocolVersion() : string {
        return $this->fromMessage()->getProtocolVersion();
    }


    public function hasHeader( string $name ) : bool {
        return $this->fromMessage()->hasHeader( $name );
    }


    /**
     * @param string $name
     * @param string|list<string> $value
     * @return static
     * @suppress PhanTypeMismatchReturn
     */
    public function withAddedHeader( string $name, $value ) : static {
        return $this->cloneMessage( $this->fromMessage()->withAddedHeader( $name, $value ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withBody( StreamInterface $body ) : static {
        return $this->cloneMessage( $this->fromMessage()->withBody( $body ) );
    }


    /**
     * @param string $name
     * @param string|list<string> $value
     * @return static
     * @suppress PhanTypeMismatchReturn
     */
    public function withHeader( string $name, $value ) : static {
        return $this->cloneMessage( $this->fromMessage()->withHeader( $name, $value ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withProtocolVersion( string $version ) : static {
        return $this->cloneMessage( $this->fromMessage()->withProtocolVersion( $version ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withoutHeader( string $name ) : static {
        return $this->cloneMessage( $this->fromMessage()->withoutHeader( $name ) );
    }


    abstract protected function cloneMessage( MessageInterface $message ) : static;


    abstract protected function fromMessage() : MessageInterface;


}