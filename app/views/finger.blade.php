<?php
    $url1=$_SERVER['REQUEST_URI'];
    header("Refresh: 1; URL=$url1");
?>
<html>
    <head>
        <title>ABSENSI - FINGERPRINT</title>
    </head>
    <body>
        <h1>Fingeprint : <span style="color: {{ $fp ? '#28BF79' : 'Red' }} " > {{ $fp ? 'Connected' : 'Disconnected' }} </span></h1>
        <table  border='1'>
            <tr>
                <td>PIN :</td>
                <td><?php echo isset($PIN) ? $PIN : "tidak ada"; ?></td>
            </tr>
            <tr>
                <td>DateTime :</td>
                <td><?php echo isset($DateTime) ? $DateTime : "tidak ada"; ?></td>
            </tr>
            <tr>
                <td>Verified :</td>
                <td><?php echo isset($Verified) ? $Verified : "tidak ada"; ?></td>
            </tr>
            <tr>
                <td>Status :</td>
                <td><?php echo isset($Status) ? $Status : "tidak ada"; ?></td>
            </tr>
        </table>
    
    </body>
</html>
