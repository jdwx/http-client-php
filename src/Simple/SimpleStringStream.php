<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\StreamInterface;


class SimpleStringStream implements StreamInterface {


    public int $uOffset = 0;

    public bool $bReadable = true;

    public bool $bSeekable = true;

    public bool $bOpen = true;


    public function __construct( public readonly string $stContents = '' ) {}


    public function __toString() : string {
        if ( $this->bSeekable ) {
            $this->uOffset = 0;
        }
        return $this->getContents();
    }


    public function close() : void {
        $this->uOffset = strlen( $this->stContents );
        $this->bOpen = false;
    }


    public function detach() : null {
        $this->close();
        return null;
    }


    public function eof() : bool {
        return $this->uOffset >= strlen( $this->stContents );
    }


    public function getContents() : string {
        $st = substr( $this->stContents, $this->uOffset );
        $this->uOffset = strlen( $this->stContents );
        return $st;
    }


    public function getMetadata( $key = null ) : null {
        return null;
    }


    public function getSize() : ?int {
        return strlen( $this->stContents );
    }


    public function isReadable() : bool {
        return $this->bReadable;
    }


    public function isSeekable() : bool {
        return $this->bSeekable;
    }


    public function isWritable() : bool {
        return false;
    }


    public function read( $length ) : string {
        $st = substr( $this->stContents, $this->uOffset, $length );
        $this->uOffset += strlen( $st );
        if ( $this->uOffset >= strlen( $this->stContents ) ) {
            $this->uOffset = strlen( $this->stContents );
        }
        return $st;
    }


    public function rewind() : void {
        if ( ! $this->bSeekable && $this->bOpen ) {
            throw new \RuntimeException( 'Stream is not seekable.' );
        }
        $this->uOffset = 0;
    }


    public function seek( int $offset, int $whence = SEEK_SET ) : void {
        if ( ! $this->bSeekable ) {
            throw new \RuntimeException( 'Stream is not seekable.' );
        }
        if ( SEEK_SET === $whence ) {
            $this->uOffset = $offset;
        } elseif ( SEEK_CUR === $whence ) {
            $this->uOffset += $offset;
        } elseif ( SEEK_END === $whence ) {
            $this->uOffset = strlen( $this->stContents ) + $offset;
        }
        if ( $this->uOffset < 0 ) {
            $this->uOffset = 0;
        }
        if ( $this->uOffset > strlen( $this->stContents ) ) {
            $this->uOffset = strlen( $this->stContents );
        }
    }


    public function tell() : int {
        return $this->uOffset;
    }


    public function write( $string ) : int {
        throw new \RuntimeException( 'Stream is not writable.' );
    }


}