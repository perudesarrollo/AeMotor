<?php
namespace perudesarrollo\AeMotor;

use MongoDB\Client;

class Mongo
{
    protected $config;

    public function __construct($config = [])
    {
        if (!is_array($config['mongodb'])) {
            throw new Exception("MongoDB: Config empty", 1);
        }
        $this->config = $config['mongodb'];
        $this->autoConnect();
    }

    public function autoConnect()
    {
        try {
            $string = "mongodb://" . $this->config['host'] . ":" . $this->config['port'];
            $mongo  = new Client($string, [], [
                'typeMap' => [
                    'array'    => 'array',
                    'document' => 'array',
                    'root'     => 'array',
                ],
            ]);
            $mongo = $mongo->{$this->config['db']};
            return $mongo;
        } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            throw new Exception('MongoDB: ' . $e->getMessage());
        }
    }
}
