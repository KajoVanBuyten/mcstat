<?php
namespace randomhost\Minecraft;

require_once realpath(__DIR__ . '/../../vendor') . '/autoload.php';

$hostname = '';
if (array_key_exists('server', $_GET)) {
    $hostname = $_GET['server'];

    $mcStat = new Status($hostname);
    $status = @$mcStat->ping();

    $hostname = htmlspecialchars($hostname);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        Minecraft Server Status
        <?php echo(!empty($hostname) ? ' :: ' . $hostname : '') ?>
    </title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
          integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp"
          crossorigin="anonymous">
    <style>
        .motd {
            text-shadow: 1px 1px 1px #333333;
            filter: dropshadow(color=#333333, offx=1, offy=1);
        }
    </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <h1>Minecraft Server Status</h1>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Query server status</h3>
        </div>
        <div class="panel-body">
            <form class="form-inline" name="stat" method="get" action="">
                <div class="form-group">
                    <label for="server">Server</label>

                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            name="server"
                            id="server"
                            placeholder="Server host name or IP address"
                            onClick="this.select();"
                            <?php echo(!empty($hostname) ? ' value="'
                                . $hostname . '"' : '') ?>
                        >
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-signal"></span>
                                Query
                            </button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($hostname)) { ?>
        <div class="panel panel-<?php if (!empty($status)) { ?>success<?php } else { ?>danger<?php } ?>">
            <div class="panel-heading">
                <h3 class="panel-title">Status for <?php echo $hostname; ?></h3>
            </div>
            <?php if (!empty($status)) { ?>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>MOTD</th>
                        <th>Server version</th>
                        <th>Players</th>
                        <th>Ping</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="motd">
                            <?php echo Format::convertToHTML($status['motd']); ?>
                        </td>
                        <td><?php echo $status['server_version'] ?></td>
                        <td>
                            <?php echo $status['player_count'] ?> /
                            <?php echo $status['player_max'] ?>
                        </td>
                        <td><?php echo $status['latency'] ?></td>
                    </tr>
                    </tbody>

                </table>
            <?php } else { ?>
                <div class="panel-body">
                    <p class="text-danger">
                        <strong>Error:</strong>
                        Could not query <?php echo $hostname ?>
                        (<?php echo $mcStat->getLastError(); ?>)
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<script
    src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script
    src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
    integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
    crossorigin="anonymous"></script>
</body>
</html>
