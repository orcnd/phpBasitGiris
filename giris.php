<?php 
// ayarlar  dizisi 
@session_start(); //session baslamali 

$ayarlar=(object)array(
	'kullanicilar'=>array(
		(object)array('kad'=>'ahmet','ad'=>'ahmet mehmet', 'sifre'=>'123456'),
		(object)array('kad'=>'ayse','ad'=>'ayse fatma','sifre'=>'123456'),
	),
	'sifrelemeTuzu'=>'sa1ksad'
);

$giris=false; //giris kontrolu yapacak degisken

function girisHashOlustur($ad,$sifre,$zaman) { //sifreyi sessiona tasimadan kullanici bilgilerini dogrulamak icin zamana ve kisiye ozel hash olusturuluyor
	global $ayarlar;
	return md5($ad . $sifre . $zaman . $ayarlar->sifrelemeTuzu);
}

function kullaniciGetir($kad) { //birden fazla kullanildigi icin fonksiyon yapmakta fayda var
	global $ayarlar;
	foreach ($ayarlar->kullanicilar as $kullanici) { // kullanicilari sirayla kontrol edelim
		if ($kullanici->kad==$kad) return $kullanici;
	}
	return false;
}

if (isset($_SESSION['girisAd']) && isset($_SESSION['girisHash']) && isset($_SESSION['girisZaman'])) {
	$kullaniciTemp=kullaniciGetir($_SESSION['girisAd']);
	if ($kullaniciTemp!=false) {
		$hash=girisHashOlustur($_SESSION['girisAd'],$kullaniciTemp->sifre,$_SESSION['girisZaman']);
		if ($_SESSION['girisHash']==$hash) {
			$giris=$kullaniciTemp;
			unset($giris->sifre); //arayuze sifreyi gondermeye gerek yok 
			unset($kullaniciTemp); //degiskeni bosaltalim cakisma olmasin
		}
	}
}

if ($giris==false && isset($_POST['ad']) && isset($_POST['sifre'])) {
	$kullaniciTemp=kullaniciGetir($_POST['ad']);
	if ($kullaniciTemp!=false) {
		if ($kullaniciTemp->sifre==$_POST['sifre']) {
			$zaman=time();
			$_SESSION['girisAd']=$kullaniciTemp->kad;
			$_SESSION['girisHash']=girisHashOlustur($kullaniciTemp->kad,$kullaniciTemp->sifre,$zaman);
			$_SESSION['girisZaman']=$zaman;

			$giris=$kullaniciTemp;
			unset($giris->sifre); //arayuze sifreyi gondermeye gerek yok 
			unset($kullaniciTemp); //degiskeni bosaltalim cakisma olmasin
		}else{
			$girisFormHata='Hatali bilgi';
		}
	}else{
		$girisFormHata='Hatali bilgi';
	}	
}

if (isset($_GET['cikis'])) { // cikis tamani
	$_SESSION['girisAd']=''; //sessionlar temizleniyor
	$_SESSION['girisHash']='';
	$_SESSION['girisZaman']='';
	$giris=false;
	header('location: ?'); //get den cikis bilgisi temizlemek icin yonlendiriliyor
}

if ($giris==false) { // giris formunu gosterelim
	if (isset($girisFormHata)) echo '<p style="color:red">' . $girisFormHata . '</p>'; //form hatasi varsa gosteriliyor

	//giris formu
?>
<form method="post">
	<input type="text" name="ad" placeholder="Kullanici adi"><br>
	<input type="password" name="sifre" placeholder="Sifre"><br>
	<input type="submit" value="giris">
</form>
<?php 
	exit; //sayfanin geri kalani yuklenmesin
}


//giris yapilmis arayuz
?>
Hosgeldin, <?php echo $giris->ad; ?> (<?php echo $giris->kad; ?>)<hr>
<a href="?cikis">Cikis</a>
