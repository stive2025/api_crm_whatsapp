<?php

namespace App\Services;

use WebSocket\Client;
use Exception;

class SocketService
{
    private Client $client;

    public function __construct()
    {
        try {
            $this->client = new Client(config('services.websocket.ip'));
        } catch (Exception $e) {
            throw new \RuntimeException("WebSocket connection failed: " . $e->getMessage());
        }
    }

    public function send(array $data): void
    {
        try {
            $this->client->send(json_encode($data));
        } catch (Exception $e) {
            // Manejar error de envío
            throw new \RuntimeException("WebSocket send failed: " . $e->getMessage());
        }
    }
    
    public function close(): void
    {
        $this->client->close();
    }

    public function __destruct()
    {
        if (isset($this->client)) {
            $this->client->close();
        }
    }
}
