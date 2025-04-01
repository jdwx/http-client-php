<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Simple;


use Psr\Http\Message\StreamInterface;


class SimpleFileStream implements StreamInterface {


    /** @var ?resource */
    private $fileHandle;

    private bool $bIsClosed = false;


    /**
     * @param resource|string $file
     * @suppress PhanTypeMismatchDeclaredParamNullable
     */
    public function __construct( mixed $file ) {
        if ( ! is_resource( $file ) ) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $f = @fopen( $file, 'r' );
            if ( false === $f ) {
                throw new \RuntimeException( "Failed to open file: {$file}" );
            }
            $this->fileHandle = $f;
        } else {
            $this->fileHandle = $file;
        }
    }


    public static function fromString( string $string ) : static {
        $stream = fopen( 'php://temp', 'r+' );
        assert( is_resource( $stream ) ); # This assertion is untestable.
        fwrite( $stream, $string );
        rewind( $stream );
        return new static( $stream );
    }


    public function __destruct() {
        if ( ! $this->bIsClosed ) {
            $this->close();
        }
    }


    public function __toString() : string {
        fseek( $this->fileHandle, 0 );
        return $this->getContents();
    }


    public function close() : void {
        fclose( $this->fileHandle );
        $this->bIsClosed = true;
    }


    /** @return resource */
    public function detach() {
        $x = $this->fileHandle;
        $this->fileHandle = null;
        $this->bIsClosed = true;
        return $x;
    }


    public function eof() : bool {
        return feof( $this->fileHandle );
    }


    public function getContents() : string {
        return stream_get_contents( $this->fileHandle );
    }


    public function getMetadata( ?string $key = null ) : mixed {
        $r = stream_get_meta_data( $this->fileHandle );
        if ( ! is_string( $key ) ) {
            return $r;
        }
        return $r[ $key ] ?? null;
    }


    public function getSize() : int {
        $buSavedPos = ftell( $this->fileHandle );
        if ( ! is_int( $buSavedPos ) ) {
            throw new \RuntimeException( 'Failed to get file size' );
        }

        $this->seek( 0, SEEK_END );
        $buSize = ftell( $this->fileHandle );
        assert( is_int( $buSize ) ); # This assertion is untestable.

        $this->seek( $buSavedPos );
        return $buSize;
    }


    public function isReadable() : bool {
        return match ( $this->getMode() ) {
            'r', 'r+', 'w+', 'a+', 'x+', 'c+' => true,
            default => false,
        };
    }


    public function isSeekable() : bool {
        return $this->getMetadata( 'seekable' ) ?? false;
    }


    public function isWritable() : bool {
        return match ( $this->getMode() ) {
            'r' => false,
            default => true,
        };
    }


    public function read( int $length ) : string {
        $bst = fread( $this->fileHandle, $length );
        assert( is_string( $bst ) ); # This assertion is untestable.
        return $bst;
    }


    public function rewind() : void {
        $this->seek( 0 );
    }


    public function seek( int $offset, int $whence = SEEK_SET ) : void {
        $i = fseek( $this->fileHandle, $offset, $whence );
        if ( -1 === $i ) {
            throw new \RuntimeException( 'Failed to seek in file' );
        }
    }


    public function tell() : int {
        $bi = ftell( $this->fileHandle );
        assert( is_int( $bi ) ); # This assertion is untestable.
        return $bi;
    }


    public function write( string $string ) : int {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $bi = @fwrite( $this->fileHandle, $string );
        if ( ! is_int( $bi ) ) {
            throw new \RuntimeException( 'Failed to write to file' );
        }
        return $bi;
    }


    private function getMode() : string {
        return strtolower( $this->getMetadata( 'mode' ) ?? '' );
    }


}
