<?php

include("includes/header.php");

$profile_id = $user['username'];
$imgSrc = "";
$result_path = "";
$msg = "";

/***********************************************************
	0 - Temp jpgの削除
 ***********************************************************/
if (!isset($_POST['x']) && !isset($_FILES['image']['name'])) {
	$temppath = 'assets/images/profile_pics/' . $profile_id . '_temp.jpeg';
	if (file_exists($temppath)) {
		@unlink($temppath);
	}
}


if (isset($_FILES['image']['name'])) {
	/***********************************************************
	1 - 元の画像をサーバーにアップロード
	 ***********************************************************/
	//名前 | サイズ | 一時保管場所		    
	$ImageName = $_FILES['image']['name'];
	$ImageSize = $_FILES['image']['size'];
	$ImageTempName = $_FILES['image']['tmp_name'];
	//ファイル拡張を取得
	$ImageType = @explode('/', $_FILES['image']['type']);
	$type = $ImageType[1]; //file type	
	//アップロードディレクトリの設定 
	$uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/social/Demo/assets/images/profile_pics';
	//ファイル名を設定
	$file_temp_name = $profile_id . '_original.' . md5(time()) . 'n' . $type; //一時ファイルのパス
	$fullpath = $uploaddir . "/" . $file_temp_name; // 一時ファイルのパス
	$file_name = $profile_id . '_temp.jpeg'; //$profile_id.'_temp.'.$type; // 最終的にサイズ変更された画像
	$fullpath_2 = $uploaddir . "/" . $file_name; //最終的にサイズ変更された画像
	//ファイルを正しい場所に移動します
	$move = move_uploaded_file($ImageTempName, $fullpath);
	chmod($fullpath, 0777);
	//有効なアップグレードを確認する
	if (!$move) {
		die('ファイルがアップロードされませんでした');
	} else {
		$imgSrc = "assets/images/profile_pics/" . $file_name; // トリミング領域に表示する画像
		$msg = "ファイルがアップロードされました!";  	//ページへのメッセージ
		$src = $file_name;	 		//トリミングフォームからサイズ変更に投稿するファイル名		
	}

	/***********************************************************
	2  - トリミング領域に合わせて画像のサイズを変更します
	 ***********************************************************/
	//アップロードされた画像サイズを取得する
	clearstatcache();
	$original_size = getimagesize($fullpath);
	$original_width = $original_size[0];
	$original_height = $original_size[1];
	// 新しいサイズを指定
	$main_width = 500; // 画像の幅を設定する
	$main_height = $original_height / ($original_width / $main_width);	// 高さを比率で設定									
	if ($_FILES["image"]["type"] == "image/gif") {
		$src2 = imagecreatefromgif($fullpath);
	} elseif ($_FILES["image"]["type"] == "image/jpeg" || $_FILES["image"]["type"] == "image/pjpeg") {
		$src2 = imagecreatefromjpeg($fullpath);
	} elseif ($_FILES["image"]["type"] == "image/png") {
		$src2 = imagecreatefrompng($fullpath);
	} else {
		$msg .= "ファイルのアップロード中にエラーが発生しました。.jpg、.gif、または.pngファイルをアップロードしてください。<br />";
	}
	//新しい画像のサイズ変更を作成
	$main = imagecreatetruecolor($main_width, $main_height);
	imagecopyresampled($main, $src2, 0, 0, 0, 0, $main_width, $main_height, $original_width, $original_height);
	//新しいバージョンをアップロード
	$main_temp = $fullpath_2;
	imagejpeg($main, $main_temp, 90);
	chmod($main_temp, 0777);
	//メモリを開放
	imagedestroy($src2);
	imagedestroy($main);
	//imagedestroy($fullpath);
	@unlink($fullpath); //元の画像を削除				

} //画像の追加

/***********************************************************
	3- 画像のトリミングとJpgへの変換
 ***********************************************************/
if (isset($_POST['x'])) {

	//投稿されたファイルの種類
	$type = $_POST['type'];
	//画像のsrc
	$src = 'assets/images/profile_pics/' . $_POST['src'];
	$finalname = $profile_id . md5(time());

	if ($type == 'jpg' || $type == 'jpeg' || $type == 'JPG' || $type == 'JPEG') {

		//画像のサイズ 150x150
		$targ_w = $targ_h = 150;
		//出力品質
		$jpeg_quality = 90;
		//トリミングされた画像をコピー
		$img_r = imagecreatefromjpeg($src);
		$dst_r = imagecreatetruecolor($targ_w, $targ_h);
		imagecopyresampled(
			$dst_r,
			$img_r,
			0,
			0,
			$_POST['x'],
			$_POST['y'],
			$targ_w,
			$targ_h,
			$_POST['w'],
			$_POST['h']
		);
		//トリミングされた新しい画像を保存
		imagejpeg($dst_r, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
	} else if ($type == 'png' || $type == 'PNG') {

		//画像のサイズ 150x150
		$targ_w = $targ_h = 150;
		//出力品質
		$jpeg_quality = 90;
		//トリミングされた画像をコピー
		$img_r = imagecreatefrompng($src);
		$dst_r = imagecreatetruecolor($targ_w, $targ_h);
		imagecopyresampled(
			$dst_r,
			$img_r,
			0,
			0,
			$_POST['x'],
			$_POST['y'],
			$targ_w,
			$targ_h,
			$_POST['w'],
			$_POST['h']
		);
		//トリミングされた新しい画像を保存
		imagejpeg($dst_r, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
	} else if ($type == 'gif' || $type == 'GIF') {

		//画像のサイズ 150x150
		$targ_w = $targ_h = 150;
		//出力品質
		$jpeg_quality = 90;
		//トリミングされた画像をコピー
		$img_r = imagecreatefromgif($src);
		$dst_r = imagecreatetruecolor($targ_w, $targ_h);
		imagecopyresampled(
			$dst_r,
			$img_r,
			0,
			0,
			$_POST['x'],
			$_POST['y'],
			$targ_w,
			$targ_h,
			$_POST['w'],
			$_POST['h']
		);
		//トリミングされた新しい画像を保存
		imagejpeg($dst_r, "assets/images/profile_pics/" . $finalname . "n.jpeg", 90);
	}
	//メモリの開放
	imagedestroy($img_r); // メモリの開放
	imagedestroy($dst_r); //メモリの開放
	@unlink($src); // 元の画像を削除				

	//トリミングした画像を元に戻す
	$result_path = "assets/images/profile_pics/" . $finalname . "n.jpeg";

	//データベースに画像を挿入
	$insert_pic_query = mysqli_query($con, "UPDATE users SET profile_pic='$result_path' WHERE username='$userLoggedIn'");
	header("Location: " . $userLoggedIn);
}
?>
<div id="Overlay" style=" width:100%; height:100%; border:0px #990000 solid; position:absolute; top:0px; left:0px; z-index:2000; display:none;"></div>
<div class="main_column column">


	<div id="formExample">

		<p><b> <?= $msg ?> </b></p>

		<form action="upload.php" method="post" enctype="multipart/form-data">
			ファイルをアップロード<br /><br />
			<input type="file" id="image" name="image" style="width:200px; height:30px; " /><br /><br />
			<input type="submit" value="アップロード" style="width:110px; height:25px;" />
		</form><br /><br />

	</div>


	<?php
	if ($imgSrc) { //画像がアップロードされている場合、トリミング領域の表示
	?>
		<script>
			$('#Overlay').show();
			$('#formExample').hide();
		</script>
		<div id="CroppingContainer" style="width:800px; max-height:600px; background-color:#FFF; margin-left: -200px; position:relative; overflow:hidden; border:2px #666 solid; z-index:2001; padding-bottom:0px;">

			<div id="CroppingArea" style="width:500px; max-height:400px; position:relative; overflow:hidden; margin:40px 0px 40px 40px; border:2px #666 solid; float:left;">
				<img src="<?= $imgSrc ?>" border="0" id="jcrop_target" style="border:0px #990000 solid; position:relative; margin:0px 0px 0px 0px; padding:0px; " />
			</div>

			<div id="InfoArea" style="width:180px; height:150px; position:relative; overflow:hidden; margin:40px 0px 0px 40px; border:0px #666 solid; float:left;">
				<p style="margin:0px; padding:0px; color:#444; font-size:18px;">
					<b>写真の切り抜き</b><br /><br />
					<span style="font-size:14px;">
						画像をトリミングしサイズを変更できます <br />
						サイズが決定したらsaveボタンを押してください

					</span>
				</p>
			</div>

			<br />

			<div id="CropImageForm" style="width:100px; height:30px; float:left; margin:10px 0px 0px 40px;">
				<form action="upload.php" method="post" onsubmit="return checkCoords();">
					<input type="hidden" id="x" name="x" />
					<input type="hidden" id="y" name="y" />
					<input type="hidden" id="w" name="w" />
					<input type="hidden" id="h" name="h" />
					<input type="hidden" value="jpeg" name="type" /> <?php
																		?>
					<input type="hidden" value="<?= $src ?>" name="src" />
					<input type="submit" value="Save" style="width:100px; height:30px;" />
				</form>
			</div>

			<div id="CropImageForm2" style="width:100px; height:30px; float:left; margin:10px 0px 0px 40px;">
				<form action="upload.php" method="post" onsubmit="return cancelCrop();">
					<input type="submit" value="キャンセル" style="width:100px; height:30px;" />
				</form>
			</div>

		</div>
	<?php
	} ?>
</div>





<?php if ($result_path) {
?>

	<img src="<?= $result_path ?>" style="position:relative; margin:10px auto; width:150px; height:150px;" />

<?php } ?>


<br /><br />