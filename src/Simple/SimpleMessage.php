<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;


class SimpleMessage implements MessageInterface {


    public StreamInterface $body;


    /** @param array<string, list<string>> $rHeaders */
    public function __construct( StreamInterface|string $body = '', public string $stProtocolVersion = '1.1',
                                 public array           $rHeaders = [] ) {
        if ( ! $body instanceof StreamInterface ) {
            $body = new SimpleStringStream( $body );
        }
        $this->body = $body;
    }


    public function getBody() : StreamInterface {
        return $this->body;
    }


    /** @return list<string> */
    public function getHeader( string $name ) : array {
        if ( ! isset( $this->rHeaders[ strtolower( $name ) ] ) ) {
            return [];
        }
        return $this->rHeaders[ strtolower( $name ) ];
    }


    public function getHeaderLine( string $name ) : string {
        return implode( ', ', $this->getHeader( $name ) );
    }


    public function getHeaders() : array {
        return $this->rHeaders;
    }


    public function getProtocolVersion() : string {
        return $this->stProtocolVersion;
    }


    public function hasHeader( string $name ) : bool {
        return isset( $this->rHeaders[ strtolower( $name ) ] );
    }


    public function withAddedHeader( $name, $value ) : static {
        $x = clone $this;
        $x->rHeaders[ strtolower( $name ) ][] = $value;
        return $x;
    }


    public function withBody( StreamInterface $body ) : static {
        $x = clone $this;
        $x->body = $body;
        return $x;
    }


    /** @param string|list<string> $value */
    public function withHeader( string $name, $value ) : static {
        $x = clone $this;
        $x->rHeaders[ strtolower( $name ) ] = is_array( $value ) ? $value : [ $value ];
        return $x;
    }


    public function withProtocolVersion( $version ) : static {
        $x = clone $this;
        $x->stProtocolVersion = $version;
        return $x;
    }


    public function withoutHeader( $name ) : static {
        $x = clone $this;
        unset( $x->rHeaders[ strtolower( $name ) ] );
        return $x;
    }


}
