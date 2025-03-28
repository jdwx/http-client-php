<?php


declare( strict_types = 1 );


namespace JDWX\HttpClient;


use Psr\Http\Message\MessageInterface;


class MessageDecorator implements MessageInterface {


    use MessageTrait;


    public function __construct( private MessageInterface $message ) {}


    protected function cloneMessage( MessageInterface $message ) : static {
        $x = clone $this;
        $x->message = $message;
        return $x;
    }


    protected function fromMessage() : MessageInterface {
        return $this->message;
    }


}
