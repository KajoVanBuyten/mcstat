<?php
namespace randomhost\Minecraft;

/**
 * Provides status information about the given Minecraft server.
 *
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      http://github.random-host.com/mcstat/
 */
class Status
{
    /**
     * Function "Server List Ping".
     */
    const SERVER_LIST_PING = 'Server List Ping';

    /**
     * Function "Server List Ping" for Minecraft versions since 1.7.
     */
    const SERVER_LIST_PING_1_7 = 'Server List Ping 1.7';

    /**
     * Function "Basic Query".
     */
    const BASIC_QUERY = 'Basic Query';

    /**
     * Function "Full Query".
     */
    const FULL_QUERY = 'Full Query';

    /**
     * Server hostname.
     *
     * @var string
     */
    protected $hostname = '';

    /**
     * Server port.
     *
     * @var int
     */
    protected $port = 25565;

    /**
     * Last error.
     *
     * @var string
     */
    protected $lastError = '';

    /**
     * Server stats.
     *
     * @var array
     */
    protected $stats = array();

    /**
     * Maps method descriptions to function calls.
     *
     * @var array[]
     */
    protected $methodTable = array();

    /**
     * Constructor.
     *
     * @param string $hostname Server hostname.
     * @param int    $port     Query port.
     */
    public function __construct($hostname, $port = 25565)
    {
        $this->hostname = $hostname;
        $this->port = $port;

        $this->methodTable = array(
            self::SERVER_LIST_PING => array(
                __NAMESPACE__ . '\ServerListPing',
                'ping'
            ),
            self::SERVER_LIST_PING_1_7 => array(
                __NAMESPACE__ . '\ServerListPing',
                'ping17'
            ),
            self::BASIC_QUERY => array(
                __NAMESPACE__ . '\Query',
                'basicQuery'
            ),
            self::FULL_QUERY => array(
                __NAMESPACE__ . '\Query',
                'fullQuery'
            ),
        );
    }

    /**
     * Returns the ping of the Minecraft server.
     *
     * @param bool $useLegacy Use legacy protocol (versions < 1.7).
     *
     * @return bool|mixed
     */
    public function ping($useLegacy = true)
    {
        if ($useLegacy) {
            return $this->performStatusMethod(self::SERVER_LIST_PING);
        } else {
            return $this->performStatusMethod(self::SERVER_LIST_PING_1_7);
        }
    }

    /**
     * Queries the status of the Minecraft server.
     *
     * @param bool $fullQuery Use full query.
     *
     * @return bool|mixed
     */
    public function query($fullQuery = true)
    {
        if ($fullQuery) {
            return $this->performStatusMethod(self::FULL_QUERY);
        } else {
            return $this->performStatusMethod(self::BASIC_QUERY);
        }
    }

    /**
     * Returns the last error.
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Returns server stats.
     *
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Returns the output of the given status method.
     *
     * @param string $statusMethodName Name of the status method.
     *
     * @return bool|mixed
     */
    protected function performStatusMethod($statusMethodName)
    {
        $method = $this->methodTable[$statusMethodName];
        $arguments = array($this->hostname, $this->port);
        try {
            $status = call_user_func_array($method, $arguments);
        } catch (\Exception $e) {
            $status = false;
            $this->lastError = $e->getMessage();
        }
        $this->stats[microtime()] = array(
            'stats' => $status,
            'method' => $statusMethodName,
            'hostname' => $this->hostname,
            'port' => $this->port,
        );

        return $status;
    }
}
