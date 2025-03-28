<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use JDWX\HttpClient\Exceptions\HttpHeaderException;
use JDWX\HttpClient\Exceptions\NoBodyException;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;


class Response extends ResponseDecorator implements ResponseInterface, \Stringable {


    private ?string $nstBody = null;

    private bool $bShortBody = false;


    public function __construct( private readonly RequestInterface   $request,
                                 \Psr\Http\Message\ResponseInterface $response,
                                 private readonly ?LoggerInterface   $logger = null ) {
        parent::__construct( $response );
    }


    public function __toString() : string {
        $stOut = "HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()} {$this->getReasonPhrase()}\n";
        foreach ( $this->getHeaders() as $stName => $rValues ) {
            $stOut .= "$stName: " . implode( ', ', $rValues ) . "\n";
        }
        $stOut .= "\n";
        try {
            $stBody = $this->body();
            if ( $this->bShortBody ) {
                $stBody = '[...] ' . $this->nstBody;
            }
            $stOut .= $stBody;
        } catch ( NoBodyException ) {
            $stOut .= '[Body not available.]';
        }
        return $stOut;
    }


    public function body() : string {
        if ( is_string( $this->nstBody ) ) {
            return $this->nstBody;
        }
        $this->bShortBody = false;
        $stream = $this->getBody();
        if ( $stream->isSeekable() ) {
            return strval( $stream );
        }
        if ( $stream->eof() ) {
            throw new NoBodyException( $this, $this->request, 'Stream is not seekable and is at EOF.' );
        }
        if ( $stream->tell() > 0 ) {
            $this->logger?->warning( 'Retrieved body from non-seekable stream with non-zero position.' );
            $this->bShortBody = true;
        }
        $this->nstBody = $stream->getContents();
        return $this->nstBody;
    }


    public function getBareContentType() : ?string {
        $nstType = $this->getHeaderOne( 'content-type' );
        if ( is_null( $nstType ) ) {
            return null;
        }
        $r = explode( ';', $nstType );
        return trim( $r[ 0 ] );
    }


    public function getHeaderOne( string $i_stName ) : ?string {
        $rOut = $this->getHeader( $i_stName );
        if ( empty( $rOut ) ) {
            return null;
        }
        if ( 1 === count( $rOut ) ) {
            return $rOut[ 0 ];
        }
        $this->logger?->warning( 'Unexpected multiple headers found.', [ $i_stName => $rOut ] );
        return null;
    }


    public function getHeaderOneEx( string $i_stName ) : string {
        $rOut = $this->getHeader( $i_stName );
        if ( 1 === count( $rOut ) ) {
            return $rOut[ 0 ];
        }
        if ( empty( $rOut ) ) {
            throw new HttpHeaderException( $this, $this->request, "No header found for {$i_stName}" );
        }
        throw new HttpHeaderException( $this, $this->request,
            "Multiple headers found for {$i_stName}: " . implode( ', ', $rOut ) );
    }


    public function getRequest() : RequestInterface {
        return $this->request;
    }


    public function isContentType( string $i_stType, ?string $i_stSubtype = null ) : bool {
        $nstType = $this->getBareContentType();
        if ( is_null( $nstType ) ) {
            return false;
        }
        if ( is_string( $i_stSubtype ) ) {
            $i_stType .= '/' . $i_stSubtype;
        }
        return $nstType === $i_stType;
    }


    public function isContentTypeLoose( string $i_stType, string $i_stSubtype ) : bool {
        return $this->isContentTypeType( $i_stType ) && $this->isContentTypeSubtype( $i_stSubtype );
    }


    public function isContentTypeSubtype( string $i_stSubtype ) : bool {
        $nstType = $this->getBareContentType();
        if ( is_null( $nstType ) ) {
            return false;
        }
        $r = explode( '/', $nstType );
        if ( 2 !== count( $r ) ) {
            return false;
        }
        $r = explode( '+', $r[ 1 ] );
        return in_array( $i_stSubtype, $r );
    }


    public function isContentTypeType( string $i_stType ) : bool {
        $nstType = $this->getBareContentType();
        if ( is_null( $nstType ) ) {
            return false;
        }
        return str_starts_with( $nstType, $i_stType . '/' );
    }


    public function isRedirect() : bool {
        $uStatus = $this->getStatusCode();
        return 3 === intval( $uStatus / 100 ) && $uStatus != 304;
    }


    public function isSuccess() : bool {
        $uStatus = $this->getStatusCode();
        return 2 === intval( $uStatus / 100 ) || 304 === $uStatus;
    }


}
