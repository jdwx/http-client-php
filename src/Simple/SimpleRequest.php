<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


class SimpleRequest extends SimpleMessage implements RequestInterface {


    public ?UriInterface $uri;


    public function __construct( StreamInterface|string   $i_body = '', string $i_stProtocolVersion = '1.1',
                                 array                    $i_rHeaders = [],
                                 public string            $stMethod = 'GET',
                                 UriInterface|string|null $i_uri = null,
                                 public ?string           $nstRequestTarget = null ) {
        parent::__construct( $i_body, $i_stProtocolVersion, $i_rHeaders );
        if ( is_string( $i_uri ) ) {
            $i_uri = SimpleUri::fromString( $i_uri );
        }
        $this->uri = $i_uri;
    }


    public function getMethod() : string {
        return $this->stMethod;
    }


    public function getRequestTarget() : string {
        if ( is_string( $this->nstRequestTarget ) ) {
            return $this->nstRequestTarget;
        }
        $st = $this->getUri()->getPath();
        $stQuery = $this->getUri()->getQuery();
        $st .= $stQuery ? '?' . $stQuery : '';
        return $st;
    }


    public function getUri() : UriInterface {
        return $this->uri ?? SimpleUri::fromString( '/' );
    }


    public function withMethod( string $method ) : static {
        $x = clone $this;
        $x->stMethod = $method;
        return $x;
    }


    public function withRequestTarget( string $requestTarget ) : static {
        $x = clone $this;
        $x->nstRequestTarget = $requestTarget;
        return $x;
    }


    public function withUri( UriInterface $uri, bool $preserveHost = false ) : static {
        $x = clone $this;
        $x->uri = $uri;
        return $x;
    }


}
