<?php include_once('header.php'); ?>
<?php
$databaseVersion = '';
if (in_array($config['prefix'] . 'version', getDBtables($config), true)) {
    $version = query('getgtdphpversion', $config);
    if (is_array($version) && isset($version[0]['version'])) {
        $databaseVersion = $version[0]['version'];
    }
}
?>
<h2>GTD-PHP Version Information</h2>
<table summary='version information'>
    <tbody>
        <tr><th>GTD-PHP revision</th>
            <td><?php echo _GTD_REVISION; ?></td>
        </tr>
        <tr><th>GTD-PHP version</th>
            <td><?php echo _GTDPHP_VERSION; ?></td>
        </tr>
        <tr><th>GTD-PHP database</th>
            <td><?php echo $databaseVersion; ?></td>
        </tr>
        <tr><th>GTD-PHP theme</th>
            <td><?php echo $config['theme']; ?></td>
        </tr>
        <tr><th>PHP</th>
            <td><?php echo PHP_VERSION; ?></td>
        </tr>
        <tr><th>Database</th>
            <td><?php echo getDBVersion(); ?></td>
        </tr>
    </tbody>
</table>
<?php include_once('footer.php'); ?>
