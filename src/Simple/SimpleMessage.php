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


    /**
     * @param string $name Header name to add.
     * @param string|list<string> $value Value (or list of values) to add.
     *
     * This adds a header to the message without removing any existing
     * occurrences of that header.
     */
    public function withAddedHeader( string $name, $value ) : static {
        $name = strtolower( $name );
        $x = clone $this;
        if ( is_string( $value ) ) {
            $x->rHeaders[ $name ][] = $value;
        } elseif ( isset( $x->rHeaders[ $name ] ) ) {
            $x->rHeaders[ $name ] = array_merge( $x->rHeaders[ $name ], $value );
        }
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


    /**
     * @param array<string, string|list<string>> $i_rHeaders Headers to add.
     * @param bool $i_bAdd If true, add to existing headers with the same name
     *                     instead of replacing them.
     */
    public function withHeaders( iterable $i_rHeaders, bool $i_bAdd = false ) : static {
        $x = $this;
        foreach ( $i_rHeaders as $stHeader => $value ) {
            if ( $i_bAdd ) {
                $x = $x->withAddedHeader( $stHeader, $value );
            } else {
                $x = $x->withHeader( $stHeader, $value );
            }
        }
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
