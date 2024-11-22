<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class PusherCheck extends Check
{
    /**
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * @param string|null $connection
     * @return self
     */
    public function connection(?string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @throws \Exception
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        if ($error = $this->checkWs()) {
            return $result->failed($error);
        }

        return $result->ok();
    }

    /**
     * @throws \Exception
     * @return ?string
     */
    private function checkWs(): ?string
    {
        $connection = $this->connection;
        if (! $connection) {
            $connection = config('broadcasting.default');
        }

        $config = config("broadcasting.connections.$connection");
        if ($config['driver'] != 'pusher') {
            throw new \Exception('Unsupported driver for connection '.$connection);
        }

        $socket = sprintf(
            '%s:%d/app/%s?protocol=7&client=js&version=7.0.3&flash=false',
            $config['options']['host'],
            $config['options']['port'],
            $config['key']
        );

        try {
            $remoteSocket = $config['options']['scheme'] == 'https' ? "ssl://$socket" : "tcp://$socket";
            $fp = stream_socket_client($remoteSocket, $errno, $errstr, 1);
            stream_set_blocking($fp, false);
        } catch (\Exception $e) {
            return 'WS is not reachable.';
        }

        $data = $this->hybi10Encode('{"event":"pusher:subscribe","data":{"channel":"public-test-channel"}}');
        $data = <<<HERE
        GET {$config['options']['scheme']}://$socket HTTP/1.1
        Host: {$config['options']['host']}
        Connection: Upgrade
        Pragma: no-cache
        Cache-Control: no-cache
        Upgrade: websocket
        Accept-Encoding: gzip, deflate
        Sec-WebSocket-Version: 13
        Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
        Sec-WebSocket-Extensions: permessage-deflate; client_max_window_bits
        
        $data
        HERE;

        fwrite($fp, $data);

        // {"event":"pusher:ping","data":{}}

        try {
            \Broadcast::broadcast(['public-test-channel'], 'test-event-01', ['foo' => 'bar']);
        } catch (\Exception $e) {
            fclose($fp);
            return 'HTTP API is not reachable.';
        }

        $microtime = microtime(true);
        $data = '';
        while (microtime(true) - $microtime < 3) {
            $data .= fgets($fp, 1024);

            if (strpos($data, '{\"foo\":\"bar\"}')) {
                fclose($fp);
                return null;
            }
        }

        fclose($fp);
        return 'WS unexpected response: ' . mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    }

    /**
     * @see https://github.com/varspool/php-websocket/blob/master/client/lib/class.websocket_client.php
     *
     * @param mixed $payload
     * @param string $type
     * @param boolean $masked
     * @return mixed
     */
    private function hybi10Encode($payload, $type = 'text', $masked = true): mixed
    {
        $frameHead = array();
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }
}
