<?php

require_once('./line/line_class.php');
require_once('./config.php');

include "./jadwal/data.php";
include "db.php";

$client = new LINEBot($channelAccessToken, $channelSecret);

$userId         = $client->parseEvents()[0]['source']['userId'];
$replyToken     = $client->parseEvents()[0]['replyToken'];
$timestamp      = $client->parseEvents()[0]['timestamp'];
$message        = $client->parseEvents()[0]['message'];
$messageid      = $client->parseEvents()[0]['message']['id'];
$profil         = $client->profil($userId);

$msg_receive   = $message['text'];

if($message['type']=='text'){
	$keyword = strtolower($msg_receive);
	$status = cekInit($userId);

	if($keyword == 'jadwal dong' || $keyword == 'mau tanya jadwal' || $keyword == 'masuk pesan tiket kereta'){
		init($userId);
		$msg = "Masukkan kota asal anda";

		$balas = array(
			'replyToken' => $replyToken,                                                        
			'messages' => array(
				array(
					'type' => 'text',                   
					'text' => $msg
				)
			)
		);
		$client->replyMessage($balas);
	}

	else if(is_array($status)){
		if($status['jenis'] == 'init'){
			$status_kota = cekKota($keyword);
			if($status_kota == 'tersedia'){
				tambahKotaAsal($userId, $keyword);
				$stasiun = dataStasiun($keyword);
				$msg = "Masukkan stasiun asal\n\n" . $stasiun;
			}
			else {
				$msg = 'Kota yang anda cari tidak tersedia';
			}

			$balas = array(
				'replyToken' => $replyToken,                                                        
				'messages' => array(
					array(
						'type' => 'text',                   
						'text' => $msg
					)
				)
			);
			
			$client->replyMessage($balas);
		}
		
		elseif($status['jenis'] == 'kota asal'){
			$status_stasiun = cekStasiun($keyword, $userId);
			if($status_stasiun == 'tersedia'){
				tambahStasiunAsal($keyword, $userId);
				$msg = "Masukkan kota tujuan";
			}
			else {
				$msg = "Stasiun yang anda cari tidak tersedia";
			}

			$balas = array(
				'replyToken' => $replyToken,
				'messages' => array(
					array(
						'type' => 'text',
						'text' => $msg
					)
				)
			);
			$client->replyMessage($balas);
		}

		elseif($status['jenis'] == 'stasiun asal'){
			$status_kota = cekKota($keyword);
			if($status_kota == 'tersedia'){
				tambahKotaTujuan($keyword, $userId);
				$stasiun = dataStasiun($keyword);
				$msg = "Masukkan stasiun tujuan\n\n" . $stasiun;
			}
			else {
				$msg = "Kota yang anda cari tidak tersedia";
			}

			$balas = array(
				'replyToken' => $replyToken,
				'messages' => array(
					array(
						'type' => 'text',
						'text' => $msg
					)
				)
			);
			$client->replyMessage($balas);
		}

		elseif($status['jenis'] == 'kota tujuan'){
			$status_stasiun = cekStasiun($keyword, $userId);
			if($status_stasiun == 'tersedia'){
				tambahStasiunTujuan($keyword, $userId);
				$msg = "Masukkan jumlah penumpang dewasa (3 thn keatas), maximal 4";
			}
			else {
				$msg = "Stasiun yang anda cari tidak tersedia";
			}

			$balas = array(
				'replyToken' => $replyToken,
				'messages' => array(
					array(
						'type' => 'text',
						'text' => $msg
					)
				)
			);
			$client->replyMessage($balas);
		}

		elseif($status['jenis'] == 'stasiun tujuan'){
			tambahKursiDewasa($keyword, $userId);
			$msg = "Masukkan jumlah penumpang anak (kurang dari 3 thn), maximal 4";
			$balas = array(
				'replyToken' => $replyToken,                                                        
				'messages' => array(
					array(
						'type' => 'text',                   
						'text' => $msg
					)
				)
			);
			$client->replyMessage($balas);
		}

		elseif($status['jenis'] == 'kursi dewasa'){
			tambahKursiAnak($keyword, $userId);
			$msg = "Masukkan tanggal keberangkatan (Contoh: 19-11-2019)";
			$balas = array(
				'replyToken' => $replyToken,                                                        
				'messages' => array(
					array(
						'type' => 'text',                   
						'text' => $msg
					)
				)
			);
			$client->replyMessage($balas);
		}

		elseif($status['jenis'] == 'kursi anak'){
			$msg = tanggalKeberangkatan($keyword, $userId);

			if ($msg['status'] == "gagal") {
				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array(
							'type' => 'text',                   
							'text' => $msg['msg']
						)
					)
				);
			}

			else {
				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'Jadwal kereta api',
							'template' => 
							array (
							  'type' => 'carousel',
							  'columns' => $msg,
							  'imageAspectRatio' => 'rectangle',
							  'imageSize' => 'cover',
							),
						)
					)
				);
			}
			
			$client->replyMessage($balas);
		}
	}

	else {
		
		$ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://loko-preprocessing.herokuapp.com/preprocessing");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, 
            http_build_query(array(
                'kalimat' => $keyword,
            )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        
		$result = json_decode($server_output);
		$result = get_output($result);
		$balas = array(
			'replyToken' => $replyToken,                                                        
			'messages' => array(
				array(
					'type' => 'text',                   
					'text' => $result
				)
			)
		);
		$client->replyMessage($balas);
	}

}

function get_output($data){
	if (count($data) == 1) {
		return $data[0]->jawaban;
	}
	else {
		$nomor = 1;
		$result = "";
		for ($i=0; $i < $data; $i++) { 
			$result .= $nomor . ". ". $data[$i]->pertanyaan . "\n";
		}

		return $result;
	}
}

?>