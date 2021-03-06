<?php

class FingerprintController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return View::make('finger');
    }

    public function getData() {

        $data = $this->get_logs("192.168.100.30");
        if (count($data) > 0) {
            try {
                DB::beginTransaction();
                date_default_timezone_set('Asia/Jakarta');
                $date = Date('Y-m-d H:i:s');
                $day = strtolower(Date('D'));
                $time = Date('H:i:s');
                
                if($day != "sat" && $day != "sun"){
                    $day = "mon-fri";
                }
                
                $data = $data[0];

                $TJ01 = DB::table('tj01')
                        ->where('tgl', date("Y-m-d"))
                        ->first();

                if ($TJ01 == NULL) {
                    $sql = "SELECT SUM(jam1) as jam1, SUM(jam2) as jam2
                            FROM (
                                    (SELECT mj02.idjk as jam1, 0 as jam2 FROM mj02
                                 INNER JOIN mj03 on mj02.idjk = mj03.mj02_id
                                 WHERE mj02.tipe = 1 AND mj02.jmmsk >= '" . $time . "' AND mj03.mk01_id = " . $data['PIN'] . " AND ( mj02.day = '".$day."' OR mj02.day = 'all' ) AND mj02.status = 'Y' ORDER BY mj02.jmmsk ASC LIMIT 1)
                                    UNION
                                    (SELECT 0 as jam1, mj02.idjk as jam2 FROM mj02
                                     INNER JOIN mj03 on mj02.idjk = mj03.mj02_id
                                     WHERE mj02.tipe = 1 AND mj03.mk01_id = " . $data['PIN'] . " AND ( mj02.day = '".$day."' OR mj02.day = 'all' ) AND mj02.status = 'Y' ORDER BY mj02.jmmsk DESC LIMIT 1)) as tabel1";
                    $IDJK = DB::select(DB::raw($sql));
                    dd($sql);
                    $IDJK = $IDJK[0];
                    $IDJK = ($IDJK->jam1 == 0 ? $IDJK->jam2 : $IDJK->jam1);
                    DB::table('tj01')->insert(
                            array('mj02_id' => $IDJK,
                                'mk01_id' => $data['PIN'],
                                'tgl' => $date,
                                'created_at' => $date,
                                'updated_at' => $date)
                    );

                    $TJ01 = DB::table('tj01')
                        ->where('tgl', date("Y-m-d"))
                        ->first();
                }else{
                    $IDJK = $TJ01->mj02_id;
                }


                $TA01 = DB::table('ta01')
                        ->where('tglabs', date("Y-m-d"))
                        ->where('idjk', $IDJK)
                        ->first();

                if ($TA01 == null) {
                    $MJ02 = DB::table('mj02')
                            ->where('idjk', $IDJK)
                            ->first();
                    $sql = "SELECT AUTO_INCREMENT as idabs FROM information_schema.tables WHERE  TABLE_SCHEMA = 'absensi' AND TABLE_NAME = 'ta01'";
                    $TA01 = DB::select(DB::raw($sql));
                    $TA01 = $TA01[0];
                    DB::table('ta01')->insert(
                            array('tglabs' => date("Y-m-d"),
                                'tipe' => $MJ02->tipe,
                                'idjk' => $IDJK,
                                'created_at' => $date,
                                'updated_at' => $date)
                    );

                }
                $absen = DB::table('ta02')
                        ->where('mk01_id', $data['PIN'])
                        ->where('abscd', $data['Status'])
                        ->where('ta01_id', $TA01->idabs)
                        ->whereDate('tglmsk', '=', strftime("%Y-%m-%d", strtotime($data['DateTime'])))
                        ->first();

                if ($absen == null) {                    
                    DB::table('ta02')->insert(
                            array('ta01_id' => $TA01->idabs,
                                'mk01_id' => $data['PIN'],
                                'tglmsk' => $data['DateTime'],
                                'abscd' => $data['Status'],
                                'created_at' => $date,
                                'updated_at' => $date)
                    );
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
            }
        }
        if (!array_key_exists("fp", $data)) {
            $data['fp'] = TRUE;
        }
        return View::make('finger', $data);
    }

    public function get_logs($IP) {
        $logs = array();
        try {
            $Connect = fsockopen($IP, "80", $errno, $errstr, 1);
            if (!stream_set_timeout($Connect, 1))
                die("Could not set timeout");
        } catch (Exception $e) {
            return array('fp' => FALSE);
        }
        if ($Connect) {
            $soap_request = "<GetAttLog>
                            <ArgComKey xsi:type=\"xsd:integer\">0</ArgComKey>
                            <Arg>
                            <PIN xsi:type=\"xsd:integer\">ALL</PIN>
                            </Arg>
                            </GetAttLog>";
            $newLine = "\r\n";
            fputs($Connect, "POST /iWsService HTTP/1.0" . $newLine);
            fputs($Connect, "Content-Type: text/xml" . $newLine);
            fputs($Connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
            fputs($Connect, $soap_request . $newLine);
            $buffer = "";
            while ($Response = fgets($Connect, 1024)) {
                $buffer = $buffer . $Response;
            }
        }
        $buffer = $this->Parse_Data($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");

        $buffer = explode("\r\n", $buffer);
        for ($a = 0; $a < count($buffer); $a++) {
            $data = $this->Parse_Data($buffer[$a], "<Row>", "</Row>");
            $log = array();
            $log['PIN'] = $this->Parse_Data($data, "<PIN>", "</PIN>");
            $log['DateTime'] = $this->Parse_Data($data, "<DateTime>", "</DateTime>");
            $log['Verified'] = $this->Parse_Data($data, "<Verified>", "</Verified>");
            $log['Status'] = $this->Parse_Data($data, "<Status>", "</Status>");
            array_push($logs, $log);
        }
        array_shift($logs);
        array_pop($logs);
        if (count($logs) > 0) {
            $this->clear_log($IP);
        }
        return $logs;
    }

    function clear_log($IP) {
        try {
            $Connect = fsockopen($IP, "80", $errno, $errstr, 1);
            if ($Connect) {
                $soap_request = "<ClearData>
                                <ArgComKey xsi:type=\"xsd:integer\">ComKey</ArgComKey>
                                <Arg><Value xsi:type=\"xsd:integer\">3</Value></Arg>
                                </ClearData>";
                $newLine = "\r\n";
                fputs($Connect, "POST /iWsService HTTP/1.0" . $newLine);
                fputs($Connect, "Content-Type: text/xml" . $newLine);
                fputs($Connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
                fputs($Connect, $soap_request . $newLine);
                $buffer = "";
                if ($Response = fgets($Connect, 1024)) {
                    $buffer = $buffer . $Response;
                }
            }
        } catch (Exception $e) {
            
        }
    }

    function Parse_Data($data, $p1, $p2) {
        $data = " " . $data;
        $hasil = "";
        $awal = strpos($data, $p1);
        if ($awal != "") {
            $akhir = strpos(strstr($data, $p1), $p2);
            if ($akhir != "") {
                $hasil = substr($data, $awal + strlen($p1), $akhir - strlen($p1));
            }
        }
        return $hasil;
    }

}
