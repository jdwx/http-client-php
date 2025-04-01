<?php


declare( strict_types = 1 );


use JDWX\HttpClient\ClientDecorator;
use JDWX\PsrHttp\Request as PsrRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Support\MyTestClient;


require_once __DIR__ . '/Support/MyTestClient.php';


#[CoversClass( ClientDecorator::class )]
final class ClientDecoratorTest extends TestCase {


    public function testSendRequest() : void {
        $backend = new MyTestClient( [ '/' => 'TEST_RESPONSE' ] );
        $request = new PsrRequest();
        $client = new ClientDecorator( $backend );
        $response = $client->sendRequest( $request );
        self::assertSame( 'TEST_RESPONSE', $response->getBody()->getContents() );
    }


}
