[![Build Status][0]][1]

randomhost/mcstat
=================

PHP class, web page, CLI tool, and [Munin][2] plugin to get information from a
[Minecraft][3] server.

This package is a fork of [winny-/mcstat][4] which cleans up the package and
adds Composer support so it can be used within other Composer packages.

Protocol Support
----------------

mcstat supports [Server List Ping][5] as seen in `1.7` and later, and `1.5.2`.
Server List Ping `1.5.2` works for older Minecraft servers (all the way back to
`1.4.2`), while the `1.7` Server List Ping should be used for newer setups.
mcstat also supports the UDP full and basic [Query][6] protocols.

Usage
-----

### minecraft_users_ — A Munin plugin

![Screenshot of the minecraft_users_ plugin][7]

Install `minecraft_users_` like any other munin plugin:

```bash
# compile stand-alone minecraft_users_ script
make

# copy the compiled script file to the munin plugins directory
cp src/bin/minecraft_users_ /usr/share/munin/plugins/minecraft_users_

# ensure that munin can execute the script
chmod 755 /usr/share/munin/plugins/minecraft_users

# create symlinks for the desired Minecraft server hostname and port
export hostname="localhost"
export port=25565
ln -s /usr/share/munin/plugins/minecraft_users_ \
    /etc/munin/plugins/minecraft_users_${hostname}:${port}
    
# reload munin service
service munin-node reload
```

No configuration is necessary because `minecraft_users_` is a wildcard plugin.

### mcstat as a program

`src/bin/mcstat.php` is a script for querying Minecraft servers. You can install
a stand-alone version like so:

```bash
make
cp src/bin/mcstat ~/bin/mcstat
```

It's very simple and gets the job done:

```bash
mcstat play.gotpvp.com
```

This will output the status of the given server like so:

```
play.gotpvp.com 1.7.4 2714/5000 131ms
Uberminecraft Cloud | 22 Games
1.7 Play Now!
```

*Please note: [`TERM` must be set to a known terminal][8], otherwise PHP spams
stderr unconditionally.*

### stat.php

`src/www/stat.php` is a simple web page that lets users query a given server.

**Note:** `stat.php` shouldn't be used on a public server as it's not well tested!

![Screenshot of stat.php][9]

### Usage as a PHP Class

```php
<?php
namespace randomhost\Minecraft;

require_once realpath(__DIR__ . '/../../vendor') . '/autoload.php';

$status = new Status('play.gotpvp.com');
var_dump($status->ping());
```

This outputs server information in the following format:

```
array(6) {
    ["player_count"]=>
    string(3) "162"
    ["player_max"]=>
    string(4) "5000"
    ["motd"]=>
    string(182) "§f§f       §f§m-§a§m-§c§m-§d§m-§b§m] §b§l    GotPVP-Network    §b§m-[§d§m-§c§m-§a§m-§e§m-§f"
    ["server_version"]=>
    string(31) "BungeeCord 1.8.x, 1.9.x, 1.10.x"
    ["protocol_version"]=>
    string(3) "127"
    ["latency"]=>
    float(293)
}
```

## Testing

The testing script requires `bash`, [`phpunit`][10], and `java`. The tests are
ran against against a live server running on localhost.

Run the script as follows:

```php
make test
```

By default `src/tests/unit-tests/bin/testrunner.sh` tests against all server
versions `1.4.2` and later.

Override this like so:

```bash
env Versions='1.7.4 1.7.5' make test
```


[0]: https://travis-ci.org/randomhost/mcstat.svg?branch=master
[1]: https://travis-ci.org/randomhost/mcstat
[2]: http://munin-monitoring.org/
[3]: http://www.minecraft.net/
[4]: https://github.com/winny-/mcstat
[5]: http://wiki.vg/Server_List_Ping
[6]: http://wiki.vg/Query
[7]: src/data/munin-plugin.png
[8]: https://github.com/nodesocket/commando/issues/9
[9]: src/data/minecraft-server-status.png
[10]: http://phpunit.de/
