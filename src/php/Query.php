<?php
namespace randomhost\Minecraft;

/**
 * Utilizes the UT3 Query protocol to query a Minecraft server.
 *
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      http://github.random-host.com/mcstat/
 *
 * @see http://wiki.vg/Query
 */
class Query
{
    /**
     * Returns basic information about the given Minecraft server.
     *
     * @param string $hostname Server hostname.
     * @param int    $port     Query port.
     *
     * @return array
     * @throws \Exception
     */
    public static function basicQuery($hostname, $port = 25565)
    {
        $vars = self::startQuery($hostname, $port, false);
        $fp = $vars['fp'];

        $stats = array(
            'motd' => self::getString($fp),
            'gametype' => self::getString($fp),
            'map' => self::getString($fp),
            'player_count' => self::getString($fp),
            'player_max' => self::getString($fp),
            'port' => self::unpackBasicPort($fp),
            'ip' => self::getString($fp),
            'latency' => $vars['time'],
        );
        fclose($fp);
        return $stats;
    }

    /**
     * Starts a query session with the given Minecraft server.
     *
     * @param string $hostname  Server hostname.
     * @param int    $port      Query port.
     * @param bool   $fullQuery Use full query.
     *
     * @return array
     * @throws \Exception
     */
    private static function startQuery($hostname, $port, $fullQuery)
    {
        $sessionId = self::makeSessionId();

        $fp = stream_socket_client(
            'udp://' . $hostname . ':' . $port,
            $errorNumber,
            $errorMessage,
            Common::NETWORK_TIMEOUT
        );
        stream_set_timeout($fp, Common::NETWORK_TIMEOUT);
        if (!$fp) {
            throw new \Exception($errorMessage);
        }

        $time = microtime(true);

        $challengeToken = self::handleHandshake($fp, $sessionId);
        if (!$challengeToken) {
            fclose($fp);
            throw new \Exception('Bad handshake response');
        }

        $time = round((microtime(true) - $time) * 1000);

        $statRequest = pack(
            'cccNN',
            0xFE,
            0xFD,
            0,
            $sessionId,
            $challengeToken
        );
        if ($fullQuery) {
            $statRequest .= pack('N', 0);
        }
        fwrite($fp, $statRequest);
        $statResponseHeader = self::readResponseHeader($fp);

        if (!self::validateResponse($statResponseHeader, 0, $sessionId)) {
            fclose($fp);
            throw new \Exception('Bad query response');
        }

        return array(
            'sessionId' => $sessionId,
            'challengeToken' => $challengeToken,
            'fp' => $fp,
            'time' => $time,
        );
    }

    /**
     * Returns a session ID.
     *
     * @return int
     */
    private static function makeSessionId()
    {
        return rand(1, 0xFFFFFFFF) & 0x0F0F0F0F;
    }

    /**
     * Handles the query connection handshake.
     *
     * @param resource $fp        Stream resource.
     * @param int      $sessionId Session ID.
     *
     * @return bool
     */
    private static function handleHandshake($fp, $sessionId)
    {
        $handshakeRequest = pack('cccN', 0xFE, 0xFD, 9, $sessionId);

        fwrite($fp, $handshakeRequest);
        $handshakeResponse = self::readResponseHeader($fp, true);
        if (!self::validateResponse($handshakeResponse, 9, $sessionId)) {
            return false;
        }

        return $handshakeResponse['challengeToken'];
    }

    /**
     * Reads the response header from the given stream resource.
     *
     * @param resource $fp                 Stream resource.
     * @param bool     $withChallengeToken Use challenge token.
     *
     * @return array
     */
    private static function readResponseHeader(
        $fp,
        $withChallengeToken = false
    ) {
        $header = fread($fp, 5);
        $unpacked = unpack('ctype/NsessionId', $header);
        if ($withChallengeToken) {
            $unpacked['challengeToken'] = (int)self::getString($fp);
        }
        return $unpacked;
    }

    /**
     * Returns a line from the given stream resource.
     *
     * @param resource $fp Stream resource.
     *
     * @return string
     */
    private static function getString($fp)
    {
        $string = '';
        while (($lastChar = fread($fp, 1)) !== chr(0)) {
            $string .= $lastChar;
        }
        return $string;
    }

    /**
     * Verify packet type and ensure it references our session ID.
     *
     * @param array $response  Response array.
     * @param int   $type      Response type.
     * @param int   $sessionId Session ID.
     *
     * @return bool
     */
    private static function validateResponse(
        array $response,
        $type,
        $sessionId
    ) {
        $invalidType = ($response['type'] !== $type);
        $invalidSessionId = ($response['sessionId'] !== $sessionId);
        if ($invalidType || $invalidSessionId) {
            $errorMessage = 'Invalid Response:';
            $errorMessage .= ($invalidType) ? " {$response['type']} !== {$type}"
                : '';
            $errorMessage .= ($invalidSessionId)
                ? " {$response['sessionId']} !== {$sessionId}" : '';
            error_log($errorMessage);
            return false;
        }
        return true;
    }

    /**
     * Reads the port from the given stream resource and returns it unpacked.
     *
     * @param resource $fp Stream resource.
     *
     * @return string
     */
    private static function unpackBasicPort($fp)
    {
        $unpacked = unpack('vport', fread($fp, 2));
        return (string)$unpacked['port'];
    }

    /**
     * Returns detailed information about the given Minecraft server.
     *
     * @param string $hostname Server hostname.
     * @param int    $port     Query port.
     *
     * @return array
     * @throws \Exception
     */
    public static function fullQuery($hostname, $port = 25565)
    {
        $vars = self::startQuery($hostname, $port, true);
        $fp = $vars['fp'];

        $stats = array();
        $stats['latency'] = $vars['time'];

        Common::expect($fp, "\x73\x70\x6C\x69\x74\x6E\x75\x6D\x00\x80\x00");

        foreach (self::parseKeyValueSection($fp) as $key => $value) {
            switch ($key) {
                case 'numplayers':
                    $key = 'player_count';
                    break;
                case 'maxplayers':
                    $key = 'player_max';
                    break;
                case 'hostname':
                    $key = 'motd';
                    break;
                case 'hostip':
                    $key = 'ip';
                    break;
                case 'hostport':
                    $key = 'port';
                    break;
            }
            $stats[$key] = $value;
        }

        Common::expect($fp, "\x01\x70\x6C\x61\x79\x65\x72\x5F\x00\x00");

        $stats['players'] = array();
        while (($player = self::getString($fp)) !== '') {
            $stats['players'][] = $player;
        }
        fclose($fp);
        return $stats;
    }

    /**
     * Parses the key value section from the given stream resource.
     *
     * @param resource $fp Stream resource.
     *
     * @return array
     */
    private static function parseKeyValueSection($fp)
    {
        $keyValuePairs = array();
        while (($key = self::getString($fp)) !== '') {
            $value = self::getString($fp);
            $keyValuePairs[$key] = $value;
        }
        return $keyValuePairs;
    }
}
