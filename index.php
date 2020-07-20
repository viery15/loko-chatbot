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

	if($keyword == 'cek jadwal' || $keyword == 'mau tanya jadwal'){
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
			$kota_awal = cekKota($keyword);
			if($kota_awal != 'kota tidak ditemukan'){
				tambahKotaAsal($userId, $kota_awal);
				$stasiun = dataStasiun($kota_awal);
				$msg = "Masukkan stasiun asal\n\n" . $stasiun;

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array(
							'type' => 'text',                   
							'text' => $msg
						)
					)
				);

			}
			else {
				$msg = "Kota yang anda cari tidak tersedia, ingin memulai pertanyaan baru ?";

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => 'jadwal dong',
								),
							  ),
							),
						  )
					)
				);
			}

			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}
		
		elseif($status['jenis'] == 'kota asal'){
			$stasiun_awal = cekStasiun($keyword, $userId);
			if($stasiun_awal != 'stasiun tidak ditemukan'){
				tambahStasiunAsal($stasiun_awal, $userId);
				$msg = "Masukkan kota tujuan";
				$balas = array(
					'replyToken' => $replyToken,
					'messages' => array(
						array(
							'type' => 'text',
							'text' => $msg
						)
					)
				);
			}
			else {
				$msg = "Stasiun yang anda cari tidak tersedia, ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
							),
						  )
					)
				);
			}

			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}

		elseif($status['jenis'] == 'stasiun asal'){
			$kota_tujuan = cekKota($keyword);
			if($kota_tujuan != 'kota tidak ditemukan'){
				tambahKotaTujuan($kota_tujuan, $userId);
				$stasiun = dataStasiun($kota_tujuan);
				$msg = "Masukkan stasiun tujuan\n\n" . $stasiun;
				$balas = array(
					'replyToken' => $replyToken,
					'messages' => array(
						array(
							'type' => 'text',
							'text' => $msg
						)
					)
				);
			}
			else {
				$msg = "Kota yang anda cari tidak tersedia, ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
							),
						  )
					)
				);

			}

			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}

		elseif($status['jenis'] == 'kota tujuan'){
			$stasiun_tujuan = cekStasiun($keyword, $userId);
			if($stasiun_tujuan != 'stasiun tidak ditemukan'){
				tambahStasiunTujuan($stasiun_tujuan, $userId);
				$msg = "Masukkan jumlah penumpang dewasa (3 thn keatas), maximal 4";

				$balas = array(
					'replyToken' => $replyToken,
					'messages' => array(
						array(
							'type' => 'text',
							'text' => $msg
						)
					)
				);
			}
			else {
				$msg = "Stasiun yang anda cari tidak tersedia, ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
							),
						  )
					)
				);
			}

			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}

		elseif($status['jenis'] == 'stasiun tujuan'){

			if(is_numeric($keyword) && $keyword >= 0){
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
			}

			else {

				$msg = "Jumlah yang anda masukkan salah. ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
							),
						  )
					)
				);

			}

			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}

		elseif($status['jenis'] == 'kursi dewasa'){

			if(is_numeric($keyword) && $keyword >= 0){
				tambahKursiAnak($keyword, $userId);
				$msg = "Masukkan tanggal keberangkatan (yyyy-mm-dd)";
				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array(
							'type' => 'text',                   
							'text' => $msg
						)
					)
				);
			}

			else {
				$msg = "Jumlah yang anda masukkan salah. ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
							),
						  )
					)
				);
			}

			
			$client->replyMessage($balas);
			saveHistory($userId, $profil->displayName, $keyword, $msg);
		}

		elseif($status['jenis'] == 'kursi anak'){

			$validasi_tanggal = validateDate($keyword);

			if ($validasi_tanggal) {
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
			}

			else {
				$msg = "Tanggal yang anda masukkan salah. ingin memulai pertanyaan baru ?";
				$prev_msg = reAsk($userId);

				$balas = array(
					'replyToken' => $replyToken,                                                        
					'messages' => array(
						array (
							'type' => 'template',
							'altText' => 'this is a confirm template',
							'template' => 
							array (
							  'type' => 'confirm',
							  'text' => $msg,
							  'actions' => 
							  array (
								0 => 
								array (
								  'type' => 'message',
								  'label' => 'Ya',
								  'text' => "/exit",
								),
								1 => 
								array (
								  'type' => 'message',
								  'label' => 'Tidak',
								  'text' => $prev_msg,
								),
							  ),
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
				resetPercakapan($userId);
			}
			
		}

		if ($jawaban == "init") {
			init($userId);
			$jawaban = "Masukkan kota asal anda";
		}

		if ($jawaban != "") {
			$balas = array(
				'replyToken' => $replyToken,                                                        
				'messages' => array(
					array(
						'type' => 'text',                   
						'text' => $jawaban
					)
				)
			);
		}

		else {
			resetPercakapan($userId);
			$prev_msg = getHistory($userId);
			$balas = array(
				'replyToken' => $replyToken,                                                        
				'messages' => array(
					array (
						'type' => 'template',
						'altText' => 'this is a confirm template',
						'template' => 
						array (
						  'type' => 'confirm',
						  'text' => "Maaf yang anda masukkan salah, ingin memulai pertanyaan baru ?",
						  'actions' => 
						  array (
							0 => 
							array (
							  'type' => 'message',
							  'label' => 'Ya',
							  'text' => "/exit",
							),
							1 => 
							array (
							  'type' => 'message',
							  'label' => 'Tidak',
							  'text' => $prev_msg,
							),
						  ),
						),
					  )
				)
			);
		}

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
	$sql2 = "DELETE FROM chatbot.jadwal WHERE id_user='$userId'";
	$result = pg_query($connect, $sql);
	$result2 = pg_query($connect, $sql2);
}

function saveHistory($userId, $nama, $input, $output){
	include "/app/db.php";
	$tanggal = date("d-m-Y");
	$sql = "INSERT INTO chatbot.history (user_id, nama, input, tanggal, output) VALUES ('". $userId ."','". $nama ."','". $input ."','". $tanggal ."','". $output ."')";
	$result = pg_query($connect, $sql);
}

function reAsk($userId){
	include "/app/db.php";
	$sql = "SELECT * FROM chatbot.jadwal WHERE id_user='".$userId."' ORDER BY id DESC";
	$result = pg_query($connect, $sql);
	$row = pg_fetch_array($result);
	$jenis = $row['jenis'];

	$sql2 = "DELETE FROM chatbot.jadwal WHERE id_user='$userId' AND jenis='$jenis'";
	$result2 = pg_query($connect, $sql2);


	return $row['value'];
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    
    return $d && $d->format($format) === $date;
}

function getHistory($userId){
	include "/app/db.php";
	$sql = "SELECT * FROM chatbot.history WHERE user_id='".$userId."' ORDER BY id DESC LIMIT 1";
	$result = pg_query($connect, $sql);
	$row = pg_fetch_array($result);

	return $row['input'];
}

?>