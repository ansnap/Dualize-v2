<?php

namespace Dualize\UserMessageBundle\Realtime;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Wamp\Topic;

/**
 * 1. Send new private messages
 * 2. Notify when user is typing
 */
class Messaging implements WampServerInterface, MessageComponentInterface
{

    protected $messageSubscribers = [];
    protected $typingSubscribers = [];
    protected $markReadSubscribers = [];
    protected $forumPostTopic;

    public function onOpen(ConnectionInterface $conn)
    {
        $userId = $conn->Session->get('userId');
        $this->removeDisconnectedSubscribers();

        echo "User " . $userId . " connected\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $userId = $conn->Session->get('userId');

        echo "User " . $userId . " disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();

        echo "Error: " . $e->getMessage() . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {

    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $parts = explode(':', $topic->getId());

        // If correct Websocket URI
        if (count($parts) != 2) {
            $conn->close();
            return false;
        }

        $subject = $parts[0];
        $userId = $parts[1];

        // If user authorized
        if ($userId != $conn->Session->get('userId')) {
            $conn->close();
            return false;
        }

        switch ($subject) {
            case 'typing':
                // $event = [recipientId, current_dialog_id]
                $event = json_decode($event, true);
                $this->sendTypingToBrowser($event['recipientId'], $event['dialogId']);
                break;
        }
    }

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        // $topic collects all connections, that's why it is replaced on every subscription
        $parts = explode(':', $topic->getId());

        $subject = $parts[0];
        $userId = '';

        if (count($parts) == 2) {
            $userId = $parts[1];
        }

        // If user authorized
        if ($userId != $conn->Session->get('userId') && in_array($subject, ['messages', 'typing', 'mark_read'])) {
            $conn->close();
            return false;
        }

        switch ($subject) {
            case 'messages':
                $this->messageSubscribers[$userId] = $topic;
                break;
            case 'typing':
                $this->typingSubscribers[$userId] = $topic;
                break;
            case 'mark_read':
                $this->markReadSubscribers[$userId] = $topic;
                break;
            case 'forum_posts':
                $this->forumPostTopic = $topic;
                break;
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {

    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {

    }

    /* Custom Methods */

    /**
     * When message saved to DB it is passed here through the ZMQ server
     * - $entryData structure in MessageSubscriber class
     */
    public function onNewServerEvent($entryData)
    {
        $event = json_decode($entryData, true);

        switch ($event['subject']) {
            case 'messages':
                $this->sendMessageToBrowser($event['recipientId'], $entryData);
                break;
            case 'mark_read':
                $this->sendMarkReadToBrowser($event['recipientId'], $event['messageId']);
                break;
            case 'forum_posts':
                $this->sendForumPostToBrowser($event['contentHTML']);
                break;
        }
    }

    public function sendMessageToBrowser($recipientId, $entryData)
    {
        if (array_key_exists($recipientId, $this->messageSubscribers)) {
            $topic = $this->messageSubscribers[$recipientId];
            $topic->broadcast($entryData);
        }
    }

    public function sendTypingToBrowser($recipientId, $dialogId)
    {
        if (array_key_exists($recipientId, $this->typingSubscribers)) {
            $topic = $this->typingSubscribers[$recipientId];
            $topic->broadcast($dialogId);
        }
    }

    public function sendMarkReadToBrowser($recipientId, $messageId)
    {
        if (array_key_exists($recipientId, $this->markReadSubscribers)) {
            $topic = $this->markReadSubscribers[$recipientId];
            $topic->broadcast($messageId);
        }
    }

    public function sendForumPostToBrowser($postHTML)
    {
        if ($this->forumPostTopic) {
            $this->forumPostTopic->broadcast($postHTML);
        }
    }

    public function removeDisconnectedSubscribers()
    {
        foreach ($this->messageSubscribers as $userId => $topic) {
            if ($topic->count() == 0) {
                unset($this->messageSubscribers[$userId]);
            }
        }

        foreach ($this->typingSubscribers as $userId => $topic) {
            if ($topic->count() == 0) {
                unset($this->typingSubscribers[$userId]);
            }
        }

        foreach ($this->markReadSubscribers as $userId => $topic) {
            if ($topic->count() == 0) {
                unset($this->markReadSubscribers[$userId]);
            }
        }
    }

}
