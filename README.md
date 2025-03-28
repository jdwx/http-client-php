# http-client

Module for PHP HTTP clients. Builds as much as possible off of PSR without introducing dependencies on any specific HTTP client implementation.

# Installation

You can require it directly with Composer:

```bash
composer require jdwx/http-client
```

Or download the source from GitHub: https://github.com/jdwx/http-client.git

## Requirements

This module requires PHP 8.3 or later.

## Usage

This module doesn't really do anything on its own. It provides simple implementations of the PSR HTTP client interfaces, which can be useful for testing or for providing a base implementation that can be extended by other modules.

It also provides RequestDecorator and ResponseDecorator classes that can be used to modify requests and responses that implement RequestInterface and ResponseInterface respectively. This allows adding functionality without tying yourself to a specific HTTP client implementation.

The best usage example is the Response class, which inherits ResponseDecorator and adds some commonly-used functionality for working with HTTP responses.

Here is a basic usage example of using the Response class:

```php
use JDWX\HttpClient\Response;

$client = new \Some\Http\Client();
$response = $client->request('GET', 'https://api.example.com/resource');
$httpResponse = new Response($response);
if (!$httpResponse->isSuccess()) {
    $statusCode = $httpResponse->getStatusCode();
    echo "Error: HTTP Status {$statusCode}\n";
    exit(1);
}
if (!$httpResponse->isContentType('application/json')) {
    echo "Error: Expected JSON response, got: " . $httpResponse->getContentType() . "\n";
    exit(1);
}
$data = json_decode( $httpResponse->body(), true );
var_dump( $data );
```

## Stability

This module is designed to stick as closely as possible to the PSR HTTP client interfaces, so it should be fairly stable. Additional functionality may be somewhat more likely to evolve over time as more use cases are encountered.

A ClientDecorator class may appear in a future version.

## History

This module was refactored out of code adapted from the jdwx/json-api-client module in early 2025 and then extensively modified to conform to various PSR interfaces wherever possible.
