<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;


trait MessageTrait {


    public function getBody() : StreamInterface {
        return $this->getMessage()->getBody();
    }


    public function getHeader( string $name ) : array {
        return $this->getMessage()->getHeader( $name );
    }


    public function getHeaderLine( string $name ) : string {
        return $this->getMessage()->getHeaderLine( $name );
    }


    public function getHeaders() : array {
        return $this->getMessage()->getHeaders();
    }


    public function getProtocolVersion() : string {
        return $this->getMessage()->getProtocolVersion();
    }


    public function hasHeader( string $name ) : bool {
        return $this->getMessage()->hasHeader( $name );
    }


    /**
     * @param string $name
     * @param string|list<string> $value
     * @return static
     * @suppress PhanTypeMismatchReturn
     */
    public function withAddedHeader( string $name, $value ) : static {
        return $this->cloneMessage( $this->getMessage()->withAddedHeader( $name, $value ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withBody( StreamInterface $body ) : static {
        return $this->cloneMessage( $this->getMessage()->withBody( $body ) );
    }


    /**
     * @param string $name
     * @param string|list<string> $value
     * @return static
     * @suppress PhanTypeMismatchReturn
     */
    public function withHeader( string $name, $value ) : static {
        return $this->cloneMessage( $this->getMessage()->withHeader( $name, $value ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withProtocolVersion( string $version ) : static {
        return $this->cloneMessage( $this->getMessage()->withProtocolVersion( $version ) );
    }


    /** @suppress PhanTypeMismatchReturn */
    public function withoutHeader( string $name ) : static {
        return $this->cloneMessage( $this->getMessage()->withoutHeader( $name ) );
    }


    abstract public function getMessage() : MessageInterface;


    abstract protected function cloneMessage( MessageInterface $message ) : static;


}