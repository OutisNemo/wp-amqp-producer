<?php

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire;

/**
 * @package    Wp_Amqp_Producer
 * @subpackage Wp_Amqp_Producer/public
 * @author     outisnemo <hello@outisnemo.com>
 */
class Wp_Amqp_Producer_Public {
    /** @var AMQPChannel */
	private $channel;

    /** @var string */
	private $exchange;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $pass;

    /** @var string */
    private $vhost;

    /** @var int */
    private $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;

    /** @var string[] */
    private $extraHeaders = [];

    /** @var string[] */
    private $allowedTypes = [];

    /** @var string[] */
    private $outputFields = ['post'];

    public function __construct($url, $exchange, $deliveryMode, $extraHeaders, $allowedTypes, $outputFields) {
        $this->host = parse_url($url, PHP_URL_HOST);
        $this->port = parse_url($url, PHP_URL_PORT) ?? 5672;
        $this->user = parse_url($url, PHP_URL_USER);
        $this->pass = parse_url($url, PHP_URL_PASS);
        $this->vhost = parse_url($url, PHP_URL_PATH) ?? '/';

        $this->exchange = $exchange;

        if ($deliveryMode) {
            switch ($deliveryMode){
                case 'NON_PERSISTENT':
                    $this->deliveryMode = AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;
                    break;
                case 'PERSISTENT':
                    $this->deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;
                    break;
                default:
                    throw new \Exception('wp-amqp-producer configuration error: invalid deliveryMode');
            }
        }

        if($extraHeaders) {
            foreach (explode(PHP_EOL, $extraHeaders) as $header) {
                $kvp = explode(': ', $header);
                if(count($kvp) != 2) {
                    throw new \Exception('wp-amqp-producer configuration error: invalid extraHeaders');
                }

                $this->extraHeaders[$kvp[0]] = $kvp[1];
            }
        }

        if ($allowedTypes) {
            foreach (explode(',', $allowedTypes) as $type) {
                $this->allowedTypes[] = trim($type);
            }
        }

        if ($outputFields) {
            foreach (explode(',', $outputFields) as $field) {
                $this->outputFields[] = trim($field);
            }
        }
    }

    public function save_post_callback($post_id, $post, $update) {
        if (wp_is_post_revision($post->ID)) {
            return;
        }

        if (count($this->allowedTypes) > 0 && !in_array($post->post_type, $this->allowedTypes)) {
            return;
        }

        $this->add_message([
            'post' => $post,
            'post_meta' => in_array('post_meta', $this->outputFields) ? get_post_meta($post_id) : null,
            'author' => in_array('author', $this->outputFields) ? new WP_User($post->post_author) : null,
            'current_user' => in_array('current_user', $this->outputFields) ? wp_get_current_user() : null,
        ]);
    }

    public function shutdown_callback() {
        $this->send_messages();
    }

    private function setup_connection() : bool {
        $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass, $this->vhost);

        if (!$connection->isConnected()) {
            error_log(sprintf('Failed to connect to AMQP at %s:%d', $this->host, $this->port));
            return false;
        }

        $this->channel = $connection->channel();
        $this->channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, false, false);

        return true;
    }

    private function add_message($object) : bool {
        if(!$this->channel && !$this->setup_connection()) {
            return false;
        }

        $headers = [
            'delivery_mode' => $this->deliveryMode
        ];

        foreach ($this->extraHeaders as $k => $v) {
            $headers[$k] = $v;
        }

        $message = new AMQPMessage(json_encode($object, JSON_FORCE_OBJECT));
        $message->set('application_headers', new Wire\AMQPTable($headers));

        $this->channel->batch_basic_publish($message, $this->exchange);
        return true;
    }

    private function send_messages() : bool {
        if(!$this->channel) {
            return false;
        }

        $this->channel->publish_batch();
        $this->channel->close();

        return true;
    }
}
