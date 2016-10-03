#!/usr/bin/env php
<?php
namespace randomhost\Minecraft;

require_once realpath(__DIR__ . '/../../vendor') . '/autoload.php';

/**
 * Munin plugin to monitor the player count on a Minecraft server.
 *
 * Install it like any other munin plugin:
 * - cp users /usr/share/munin/plugins/minecraft_users_
 * - chmod 755 /usr/share/munin/plugins/minecraft_users_
 * - ln -s /usr/share/munin/plugins/minecraft_users_ /etc/munin/plugins/minecraft_users_<hostname>:<port>
 *
 * You have to update the require_once statement to include the composer
 * auto loader.
 *
 * No further configuration is necessary because minecraft_users_ is a wildcard
 * plugin.
 */

error_reporting(E_ERROR | E_PARSE);

$pluginName = $argv[0];
if (preg_match(
    '/minecraft_users_([^[:blank:][:cntrl:]]+)(?::([0-9]+))?$/U',
    $pluginName,
    $matches
)) {
    $host = $matches[1];
    $port = empty($matches[2]) ? '25565' : $matches[2];
} else {
    error_log("Warning: Plugin {$pluginName} not configured correctly");
    exit();
}

if ((count($argv) > 1) && ($argv[1] == 'config')) {
    print("graph_title Players connected to {$host}:{$port}\n");
    print("graph_vlabel players\n");
    print("players.label Number of players\n");
    print("max_players.label Max players\n");
    print("graph_info Number of players connected to Minecraft. " .
        "If Max players is 0, the server is unreachable.\n");
    print("graph_scale no\n");
    print("graph_category minecraft\n");
    exit();
}

$m = new Status($host, $port);
$reply = $m->ping();
$player_count = empty($reply['player_count']) ? '0' : $reply['player_count'];
$player_max = empty($reply['player_max']) ? '0' : $reply['player_max'];

print('players.value ' . $player_count . "\n");
print('max_players.value ' . $player_max . "\n");
