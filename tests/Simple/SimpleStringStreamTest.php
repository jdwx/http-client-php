<?php


declare( strict_types = 1 );


namespace Simple;


use JDWX\HttpClient\Simple\SimpleStringStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( SimpleStringStream::class )]
final class SimpleStringStreamTest extends TestCase {


    public function testClose() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        $stream->close();
        self::assertSame( strlen( $st ), $stream->uOffset );
    }


    public function testDetach() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        $stream->detach();
        self::assertSame( strlen( $st ), $stream->uOffset );
    }


    public function testEof() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        self::assertFalse( $stream->eof() );
        $stream->uOffset = strlen( $st );
        self::assertTrue( $stream->eof() );
    }


    public function testGetContents() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertSame( 'TEST_CONTENT', $stream->getContents() );
    }


    public function testGetContentsForPartial() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        $stream->uOffset = 5;
        self::assertSame( 'CONTENT', $stream->getContents() );
    }


    public function testGetMetadata() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        /** @phpstan-ignore-next-line */
        self::assertNull( $stream->getMetadata() );
    }


    public function testGetSize() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        self::assertSame( strlen( $st ), $stream->getSize() );
    }


    public function testIsReadable() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertTrue( $stream->isReadable() );
        $stream->bReadable = false;
        self::assertFalse( $stream->isReadable() );
    }


    public function testIsSeekable() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertTrue( $stream->isSeekable() );
        $stream->bSeekable = false;
        self::assertFalse( $stream->isSeekable() );
    }


    public function testIsWritable() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertFalse( $stream->isWritable() );
    }


    public function testRead() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertSame( 'TEST_', $stream->read( 5 ) );
        self::assertSame( 'CON', $stream->read( 3 ) );
        self::assertSame( 'TENT', $stream->read( 100 ) );
    }


    public function testRewind() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        $stream->uOffset = 5;
        $stream->rewind();
        self::assertSame( 0, $stream->uOffset );

        $stream->uOffset = 5;
        $stream->bSeekable = false;
        self::expectException( \RuntimeException::class );
        $stream->rewind();
    }


    public function testSeek() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        $stream->seek( 5 );
        self::assertSame( 5, $stream->uOffset );

        $stream->seek( 2, SEEK_CUR );
        self::assertSame( 7, $stream->uOffset );

        $stream->seek( -4, SEEK_CUR );
        self::assertSame( 3, $stream->uOffset );

        $stream->seek( -2, SEEK_END );
        self::assertSame( strlen( $st ) - 2, $stream->uOffset );

        $stream->seek( 2, SEEK_END );
        self::assertSame( strlen( $st ), $stream->uOffset );

        $stream->seek( -1 );
        self::assertSame( 0, $stream->uOffset );

    }


    public function testSeekForNotSeekable() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        $stream->bSeekable = false;
        self::expectException( \RuntimeException::class );
        $stream->seek( 5 );
    }


    public function testTell() : void {
        $stream = new SimpleStringStream( 'TEST_CONTENT' );
        self::assertSame( 0, $stream->tell() );
        $stream->uOffset = 5;
        self::assertSame( 5, $stream->tell() );
    }


    public function testToString() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        self::assertSame( $st, (string) $stream );
    }


    public function testToStringForPartialNotSeekable() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        $stream->bSeekable = false;
        $stream->uOffset = 5;
        self::assertSame( 'CONTENT', (string) $stream );
    }


    public function testToStringForPartialSeekable() : void {
        $st = 'TEST_CONTENT';
        $stream = new SimpleStringStream( $st );
        $stream->uOffset = 5;
        self::assertSame( $st, (string) $stream );
    }


    public function testWrite() : void {
        $stream = new SimpleStringStream( '' );
        self::expectException( \RuntimeException::class );
        $stream->write( 'TEST' );
    }


}
