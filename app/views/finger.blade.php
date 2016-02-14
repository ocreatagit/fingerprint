<?php
    $url1=$_SERVER['REQUEST_URI'];
    header("Refresh: 1; URL=$url1");
?>
<table border="1">
    <tr>
        <td>Kode Karyawan</td>
        <td>@if(isset($PIN)) 
                {{$PIN}}
            @else
                tidak ada
            @endif
        </td>
    </tr>
    <tr>
        <td>Date & Time</td>
        <td>@if(isset($DateTime)) 
                {{$DateTime}}
            @else
                tidak ada
            @endif
        </td>
    </tr>
    <tr>
        <td>Status</td>
        <td>@if(isset($Status)) 
                {{$Status}}
            @else
                tidak ada
            @endif
        </td>
    </tr>
</table>

