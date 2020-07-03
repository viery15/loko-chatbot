<?php

    function init($userId){
        include "/app/db.php";

        $sql_cek = "SELECT * FROM chatbot.jadwal WHERE id_user='$userId'";
        $result_cek = pg_query($connect, $sql_cek);

        $row = pg_fetch_array($result_cek);
       
        if($row > 1){
            hapusHistory($userId);
        }

        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','init','inisialisasi')";
        $result = pg_query($connect, $sql);
    }

    function dataStasiun($kota){
        $string = file_get_contents("/app/jadwal/data.json");
        $data = json_decode($string, true);
        $stasiun = "";
        foreach ($data['data']['kota'] as $key => $value) {
            if($kota == $key){
                for ($j=0; $j < count($value['stasiun']); $j++) { 
                    $stasiun .= $j+1 . ". " . $value['stasiun'][$j]['nama'] . "\n";
                }
            }
        }
        return $stasiun;
    }

    function tambahKotaAsal($userId, $kota){
        include "/app/db.php";
        $jenis = "kota asal";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $kota ."')";
        $result = pg_query($connect, $sql);
    }

    function tambahStasiunAsal($stasiun, $userId){
        include "/app/db.php";
        $jenis = "stasiun asal";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $stasiun ."')";
        $result = pg_query($connect, $sql);
    }

    function tambahKotaTujuan($kota, $userId){
        include "/app/db.php";
        $jenis = "kota tujuan";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $kota ."')";
        $result = pg_query($connect, $sql);
    }

    function tambahStasiunTujuan($stasiun, $userId){
        include "/app/db.php";
        $jenis = "stasiun tujuan";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $stasiun ."')";
        $result = pg_query($connect, $sql);
    }

    function cekInit($userId){
        include "/app/db.php";
        $sql = "SELECT * FROM chatbot.jadwal WHERE id_user='".$userId."' ORDER BY id DESC";
        $result = pg_query($connect, $sql);
        $row = pg_fetch_array($result);
        return $row;
    }

    function cekKota($kota){
        $string = file_get_contents("/app/jadwal/data.json");
        $data = json_decode($string, true);
        $response = "kota tidak ditemukan";
        foreach ($data['data']['kota'] as $key => $value) {
            if($kota == $key){
                $response = "tersedia";
            }
        }
        return $response;
    }

    function cekStasiun($input, $userId){
        include "/app/db.php";
        $string = file_get_contents("/app/jadwal/data.json");
        $data = json_decode($string, true);
        $response = "stasiun tidak ditemukan";

        $sql = "SELECT * FROM chatbot.jadwal WHERE id_user='".$userId."' ORDER BY id DESC";
        $result = pg_query($connect, $sql);
        $row = pg_fetch_array($result);
        $kota = $row['value'];

        $sinonim_stasiun = array(
            ['surabaya gubeng', 'sgu', 'gubeng', 'gbng'],
            ['malang', 'ml', 'ML', 'mlg']
        );

        for ($j=0; $j < count($sinonim_stasiun) ; $j++) { 
            for ($k=0; $k < count($sinonim_stasiun[$j]); $k++) { 
                if ($input == $sinonim_stasiun[$j][$k]) {
                    $input = $sinonim_stasiun[$j][0];
                }
            }
        }

        foreach ($data['data']['kota'] as $key => $value) {
            for ($i=0; $i < count($data['data']['kota'][$kota]['stasiun']); $i++) { 
                if($data['data']['kota'][$kota]['stasiun'][$i]['nama'] == $input || $i+1 == $input){
                    $response = $data['data']['kota'][$kota]['stasiun'][$i]['nama'];
                }
            }
        }
        return $response;
    }

    function tambahKursiDewasa($jml_dewasa, $userId){
        include "/app/db.php";
        $jenis = "kursi dewasa";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $jml_dewasa ."')";
        $result = pg_query($connect, $sql);
    }

    function tambahKursiAnak($jml_anak, $userId){
        include "/app/db.php";
        $jenis = "kursi anak";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $jml_anak ."')";
        $result = pg_query($connect, $sql);
    }

    function tanggalKeberangkatan($tanggal, $userId){
        include "/app/db.php";
        $jenis = "tanggal berangkat";
        $sql = "INSERT INTO chatbot.jadwal (id_user, jenis, value) VALUES ('". $userId ."','". $jenis ."','". $tanggal ."')";
        $result = pg_query($connect, $sql);

        $data_output = ambilData($userId);

        return $data_output;
    }

    function kodeStasiun($kota,$stasiun){
        $string = file_get_contents("/app/jadwal/data.json");
        $data = json_decode($string, true);
        foreach ($data['data']['kota'] as $key => $value) {
            for ($i=0; $i < count($data['data']['kota'][$kota]['stasiun']); $i++) { 
                if($data['data']['kota'][$kota]['stasiun'][$i]['nama'] == $stasiun){
                    $kode_stasiun = $data['data']['kota'][$kota]['stasiun'][$i]['kode'];
                }
            }
        }

        return $kode_stasiun;
    }

    function ambilData($userId){
        include "/app/db.php";

        $sql = "SELECT * FROM chatbot.jadwal WHERE id_user='".$userId."' ORDER BY id ASC";
        $result = pg_query($connect, $sql);
        $array = array();
        while($row = pg_fetch_assoc($result)){
            $array[] = $row;
        }

        $stasiun_asal = kodeStasiun($array[1]['value'], $array[2]['value']);
        $stasiun_tujuan = kodeStasiun($array[3]['value'], $array[4]['value']);
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://loko-crawler.herokuapp.com/");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, 
            http_build_query(array(
                'from' => $array[1]['value'],
                'to' => $array[3]['value'],
                'ori' => $stasiun_asal,
                'dest' => $stasiun_tujuan,
                'date' => $array[7]['value'],
                'infant' => $array[6]['value'],
                'adult' => $array[5]['value'],
                'stationfrom' => $array[2]['value'],
                'stationto' => $array[4]['value'],
            )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        
        $data = json_decode($server_output);
        if(isset($data->response->msg)){
            $new_output['msg'] = $data->response->msg;
            $new_output['status'] = "gagal";
        }
        else {
            $url = $data->response->url;
            $data = $data->response->trains;
            

            $output = "";
            for ($i=0; $i < 10; $i++) {
                // if ($data[$i]->status != "PENUH") {
                $output .= $i+1 . ". " . $data[$i]->train . " (" . $data[$i]->status . ")" . "\n";

                $new_output[$i] = 
                    array (
                        'thumbnailImageUrl' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcShulYD7LxuiAQgQeZrxweotLdOrVEkKhBQp7v7Vjx0opq-fsrV',
                        'imageBackgroundColor' => '#FFFFFF',
                        'title' => $data[$i]->train . "\n[" . $data[$i]->class . "]",
                        'text' => $data[$i]->dept_time . " - " . $data[$i]->arr_time . "\n" . $data[$i]->status . "\n " . $data[$i]->price,
                        'defaultAction' => 
                        array (
                            'type' => 'uri',
                            'label' => 'View detail',
                            'uri' => $url,
                        ),
                        'actions' => 
                        array (
                            0 => 
                            array (
                            'type' => 'uri',
                            'label' => 'Pesan Sekarang',
                            'uri' => $url,
                            ),
                        ),
                    );
                // }   
            }    
        }
        
        hapusHistory($userId);
        return $new_output;
    }

    function hapusHistory($userId){
        include "/app/db.php";

        $sql = "DELETE FROM chatbot.jadwal WHERE id_user='$userId'";
        $result = pg_query($connect, $sql);
        return $result;
    }

    // init('U8fe0a36ff0efa00cd3f36f7c00ff915f');
    

?>