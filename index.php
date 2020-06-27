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
	$status_temp = cekTemp($userId);

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
		saveHistory($userId, $profil->displayName, $keyword, $msg);
	}

	else if($keyword == "/exit"){
		resetPercakapan($userId);
		$msg = "Terimakasih kak " . $profil->displayName . " ada yang bisa loko bantu lagi ?";
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
		saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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
			saveHistory($userId, $profil->displayName, $keyword, $msg);
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

	elseif (is_array($status_temp)) {
		$jawaban = "";
		for ($i=0; $i < count($status_temp); $i++) { 
			if ($status_temp[$i]['number'] == $keyword) {
				$jawaban = $status_temp[$i]['jawaban'];
				
			}
			
		}
		if($jawaban == "") {
			$jawaban = "Maaf yang anda masukkan salah, silahkan ulangin atau ketik /exit untuk memulai pertanyaan baru";
		}
		else {
			resetPercakapan($userId);
		}

		$balas = array(
			'replyToken' => $replyToken,                                                        
			'messages' => array(
				array(
					'type' => 'text',                   
					'text' => $jawaban
				)
			)
		);
		$client->replyMessage($balas);
		saveHistory($userId, $profil->displayName, $keyword, $jawaban);
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
		$result = get_output($result, $userId);
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
		saveHistory($userId, $profil->displayName, $keyword, $result);
	}

}

function get_output($data, $userId){
	if (count($data) == 1) {
		return $data[0]->jawaban;
	}
	else {
		$nomor = 1;
		$result = "Mungkin maksud anda" . "\n\n";
		for ($i=0; $i < count($data); $i++) { 
			$result .= $nomor . ". ". $data[$i]->pertanyaan . "\n";
			save_temp($nomor, $userId, $data[$i]->jawaban);
			$nomor++;
		}
		$result .= "\n" . "Masukkan nomer dari pertanyaan yang anda maksud";

		return $result;
	}
}

function save_temp($number, $id_user, $jawaban){
	include "/app/db.php";
	$sql = "INSERT INTO chatbot.temp (id_user, number, jawaban) VALUES ('". $id_user ."','". $number ."','". $jawaban ."')";
	$result = pg_query($connect, $sql);
}

function cekTemp($userId){
	include "/app/db.php";
	$sql = "SELECT * FROM chatbot.temp WHERE id_user='".$userId."'";
	$result = pg_query($connect, $sql);
	$row = pg_fetch_all($result);
	return $row;
}

function getJawaban($id){
	include "/app/db.php";
	$sql = "SELECT * FROM chatbot.faq WHERE id='".$id."'";
	$result = pg_query($connect, $sql);
	$row = pg_fetch_array($result);
	$jawaban = $row['jawaban'];

	return $jawaban;
}

function resetPercakapan($userId){
	include "/app/db.php";
	$sql = "DELETE FROM chatbot.temp WHERE id_user='$userId'";
	$result = pg_query($connect, $sql);
}

function saveHistory($userId, $nama, $input, $output){
	include "/app/db.php";
	$tanggal = date("d-m-Y");
	$sql = "INSERT INTO chatbot.history (user_id, nama, input, tanggal, output) VALUES ('". $userId ."','". $nama ."','". $input ."','". $tanggal ."','". $output ."')";
	$result = pg_query($connect, $sql);
}

?>