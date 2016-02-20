<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>ABSENSI</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/4-col-portfolio.css" rel="stylesheet">

    </head>
    <body>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="col-sm-8">Absensi Indografika</h3>
                    <h3>{{ date('d F Y') }} | <span id="timeServer">{{ date('H:i:s') }}</span></h3>
                </div>
                <div class="panel-body">
                    <div class="col-sm-7"> 
                        <div class="col-sm-4" style="margin-right: 20px">
                            <img src="http://dummyimage.com/200x200">
                        </div>
                        <div class="col-sm-7">
                            <table>
                                <tbody>
                                    <tr style="font-size: 40px">
                                        <td class="text-right">No Absen :</td>
                                        <td>&nbsp; <span id="noAbsen">-</span></td>
                                    </tr>
                                    <tr style="font-size: 40px">
                                        <td class="text-right">Nama :</td>
                                        <td>&nbsp; <span id="nama">-</span></td>
                                    </tr>
                                </tbody>
                                <tbody id="jam">
                                    <tr style='font-size: 30px'>
                                        <td class='text-right'>Telat (menit) :</td>
                                        <td>&nbsp; </td>
                                    </tr>
                                    <tr style='font-size: 30px'>
                                        <td class='text-right'>Jam Masuk :</td>
                                        <td>&nbsp; </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="status" class="row text-center" style="margin-top: 210px;">
                            <h1>Berhasil Login</h1>
                            <h1>Selamat Berkerja</h1>
                        </div>
                    </div>
                    <div class="col-sm-5"> 
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h4> Note : Sebelum meletekkan jari anda pada alat fingerprint, tekan dahulu angka pada alat fingerprint.</h4>
                                Angka simbol : untuk jam masuk<br>
                                Angka simbol : untuk jam pulang<br>
                                Angka simbol : untuk jam masuk lembur<br>
                                Angka simbol : untuk jam pulang lembur<br>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                Jika karyawan ingin melihat absensi secara keseluruhan dapat dilihat di<br>
                                <a href="">www.something.com</a><br>
                                Login sesuai username dan password yang diberikan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer>
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <p>Copyright &copy; Indografika 2016</p>
                    </div>
                </div>
            </footer>
        </div>
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script>
            $(document).ready(function (){
                console.log( "ready!" );
                setInterval(function () {
                $.get('<?php echo action('FingerprintController@getTimeServer') ?>', function (data) {
                    $('#timeServer').html(data);
                });

                $.getJSON("<?php echo action('FingerprintController@getData') ?>", function (data) {
                    if (data.status) {
                        if (data.hasData) {
                            $('#noAbsen').html(data.content[0].idkar);
                            $('#nama').html(data.content[0].nama);
                            if (data.content[0].abscd == 0) {
                                $('#jam').html("<tr style='font-size: 30px'>" +
                                        "<td class='text-right'>Telat (menit) :</td>" +
                                        "<td>&nbsp; " + data.content[0].lbt + "</td>" +
                                        "</tr><tr style='font-size: 30px'>" +
                                        "<td class='text-right'>Jam Masuk :</td>" +
                                        "<td>&nbsp; " + data.content[0].jammsk + "</td>" +
                                        "</tr>");
                            } else {

                            }
                            $('#status').html('<h1>Selamat Berkerja</h1>');
                            console.log(data.content);
                        }
                    } else {
                        $('#status').html("<h1 style='color: red;'>Fingerprint belum terhubung</h1>");
                    }
                    console.log(data);
                })
                        .done(function () {

                        })
                        .fail(function (jqXHR, textStatus, errorThrown) {
            //                console.log('getJSON request failed! ' + JSON.stringify(jqXHR));
            //                console.log('getJSON request failed! ' + JSON.stringify(textStatus));
            //                console.log('getJSON request failed! ' + JSON.stringify(errorThrown));
            //                console.log('============================');
                        })
                        .always(function () {

                        });

            }, 1000);
            });
        </script>
    </body>
</html>
