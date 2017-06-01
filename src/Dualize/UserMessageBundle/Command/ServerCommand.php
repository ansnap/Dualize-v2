<?php

namespace Dualize\UserMessageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dualize\UserMessageBundle\Realtime\Messaging;

class ServerCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('dualize:message:server:run')
                ->setDescription('WAMP server for realtime notifications')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "Starting realtime message server...\n";

        $loop = \React\EventLoop\Factory::create();
        $pusher = new Messaging();

        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:8082'); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'onNewServerEvent')); // 'message' is not out entity, it's ratchet's stuff
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new \React\Socket\Server($loop);
        $webSock->listen(8081, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
                new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                new \Ratchet\Session\SessionProvider(
                new \Ratchet\Wamp\WampServer($pusher), $this->getContainer()->get('session.handler')
                )
                )
                ), $webSock
        );

        $loop->run();
    }

    public static function pushToSubscribers($entryData)
    {
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH);
        $socket->connect("tcp://localhost:8082");
        $socket->send(json_encode($entryData));
    }

}
