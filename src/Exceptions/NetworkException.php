<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient\Exceptions;


use Psr\Http\Client\NetworkExceptionInterface;


class NetworkException extends AbstractRequestException implements NetworkExceptionInterface { }

