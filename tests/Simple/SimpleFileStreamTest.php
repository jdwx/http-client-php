<?php


declare( strict_types = 1 );


namespace Simple;


use JDWX\HttpClient\Simple\SimpleFileStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( SimpleFileStream::class )]
final class SimpleFileStreamTest extends TestCase {


    private ?SimpleFileStream $stream = null;

    private ?string $nstFileName = null;


    public function testConstructForNoSuchFile() : void {
        $this->expectException( \RuntimeException::class );
        $x = new SimpleFileStream( '/no/such/file' );
        unset( $x );
    }


    public function testDetach() : void {
        $f = fopen( '/dev/zero', 'r' );
        $this->stream = new SimpleFileStream( $f );
        self::assertSame( $f, $this->stream->detach() );
        fclose( $f );
        $this->stream = null;
    }


    public function testEof() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertFalse( $this->stream->eof() );
        $this->stream->getContents();
        self::assertTrue( $this->stream->eof() );
    }


    public function testGetContents() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( $st, $this->stream->getContents() );
    }


    public function testGetContentsForPartial() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        $this->stream->seek( 5 );
        self::assertSame( 'CONTENT', $this->stream->getContents() );
    }


    public function testGetMetadata() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        $rMeta = $this->stream->getMetadata();
        self::assertIsArray( $rMeta );
        self::assertSame( $this->nstFileName, $rMeta[ 'uri' ] );
        self::assertSame( 'r', $rMeta[ 'mode' ] );
    }


    public function testGetSize() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( strlen( $st ), $this->stream->getSize() );
    }


    public function testGetSizeForNotSeekable() : void {
        $stream = new SimpleFileStream( '/dev/zero' );
        self::expectException( \RuntimeException::class );
        $stream->getSize();
    }


    public function testIsReadableForFalse() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st, false, true );
        self::assertFalse( $this->stream->isReadable() );
    }


    public function testIsReadableForTrue() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertTrue( $this->stream->isReadable() );
    }


    public function testIsWriteableForFalse() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertFalse( $this->stream->isWritable() );
    }


    public function testIsWriteableForTrue() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st, false, true );
        self::assertTrue( $this->stream->isWritable() );
    }


    public function testRead() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( $st, $this->stream->read( strlen( $st ) ) );
    }


    public function testRewind() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        $this->stream->getContents();
        self::assertNotSame( 0, $this->stream->tell() );
        $this->stream->rewind();
        self::assertSame( 0, $this->stream->tell() );
    }


    public function testSeek() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( 0, $this->stream->tell() );
        $this->stream->seek( 5 );
        self::assertSame( 5, $this->stream->tell() );
        $this->stream->seek( -5, SEEK_CUR );
        self::assertSame( 0, $this->stream->tell() );
        self::expectException( \RuntimeException::class );
        $this->stream->seek( -500 );
    }


    public function testSeekableForFalse() : void {
        $stream = new SimpleFileStream( '/dev/zero' );
        self::assertFalse( $stream->isSeekable() );
    }


    public function testSeekableForTrue() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertTrue( $this->stream->isSeekable() );
    }


    public function testTell() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( 0, $this->stream->tell() );
        $this->stream->seek( 5 );
        self::assertSame( 5, $this->stream->tell() );
    }


    public function testToString() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        self::assertSame( $st, strval( $this->stream ) );
    }


    public function testToStringForPartial() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( $st );
        $this->stream->seek( 5 );
        self::assertSame( 'TEST_CONTENT', strval( $this->stream ) );
    }


    public function testWrite() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( '', false, true );
        self::assertSame( strlen( $st ), $this->stream->write( $st ) );
        self::assertSame( $st, file_get_contents( strval( $this->nstFileName ) ) );
    }


    public function testWriteForFailure() : void {
        $st = 'TEST_CONTENT';
        $this->newFileStream( '' );
        self::expectException( \RuntimeException::class );
        self::assertSame( strlen( $st ), $this->stream->write( $st ) );
    }


    protected function tearDown() : void {
        if ( is_string( $this->nstFileName ) && file_exists( $this->nstFileName ) ) {
            unlink( $this->nstFileName );
        }
        if ( $this->stream instanceof SimpleFileStream ) {
            $this->stream->close();
        }
    }


    private function newFileStream( string $i_stContent, bool $i_bReadable = true,
                                    bool   $i_bWriteable = false ) : void {
        $this->nstFileName = tempnam( sys_get_temp_dir(), 'test' );
        file_put_contents( $this->nstFileName, $i_stContent );
        if ( $i_bReadable && $i_bWriteable ) {
            $stMode = 'r+';
        } elseif ( $i_bReadable ) {
            $stMode = 'r';
        } elseif ( $i_bWriteable ) {
            $stMode = 'a';
        } else {
            throw new \InvalidArgumentException( 'File must be readable or writeable' );
        }
        $f = fopen( $this->nstFileName, $stMode );
        if ( false === $f ) {
            throw new \RuntimeException( "Failed to open file: {$this->nstFileName}" );
        }
        $this->stream = new SimpleFileStream( $f );
    }


}
