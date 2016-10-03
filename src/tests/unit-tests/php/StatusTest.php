<?php
namespace randomhost\Minecraft;

use PHPUnit_Framework_TestCase;

require_once APP_DATADIR . '/config.php';

/**
 * Unit test for Status.
 *
 * @author    Winston Weinert <WinstonOne@fastmail.fm>
 * @author    Ch'Ih-Yu <chi-yu@web.de>
 * @copyright 2016 random-host.com
 * @license   http://opensource.org/licenses/mit-license.html The MIT License (MIT)
 * @link      http://github.random-host.com/mcstat/
 */
class StatusTest extends PHPUnit_Framework_TestCase
{
    public function testServerListPing()
    {
        global $hostname, $port, $mcVersion, $motd;

        $status = new Status($hostname, $port);
        $ping = $status->ping();

        $this->assertEquals('', $status->getLastError());
        $this->assertEquals($mcVersion, $ping['server_version']);
        $this->assertEquals($motd, $ping['motd']);
        $this->assertEquals(0, $ping['player_count']);
        $this->assertEquals(20, $ping['player_max']);
        $this->assertEquals(true, is_float($ping['latency']));
    }

    public function testBasicQuery()
    {
        global $hostname, $port, $mcVersion, $motd;

        $status = new Status($hostname, $port);
        $query = $status->query(false);

        $this->assertEquals('', $status->getLastError());
        $this->assertEquals($motd, $query['motd']);
        $this->assertEquals(0, $query['player_count']);
        $this->assertEquals(20, $query['player_max']);
        $this->assertEquals(true, is_float($query['latency']));
        $this->assertEquals($port, $query['port']);
    }

    public function testFullQuery()
    {
        global $hostname, $port, $mcVersion, $motd;

        $status = new Status($hostname, $port);
        $query = $status->query(true);

        $this->assertEquals('', $status->getLastError());
        $this->assertEquals($motd, $query['motd']);
        $this->assertEquals(0, $query['player_count']);
        $this->assertEquals(20, $query['player_max']);
        $this->assertEquals(true, is_float($query['latency']));
        $this->assertEquals($port, $query['port']);
    }
}
