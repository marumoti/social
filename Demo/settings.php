<?php 
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>

<div class="main_column column">

	<h4>アカウント設定</h4>
	<?php
	echo "<img src='" . $user['profile_pic'] ."' class='small_profile_pic'>";
	?>
	<br>
	<a href="upload.php">プロファイル写真の変更</a> <br><br><br>

	<?php
	$user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username='$userLoggedIn'");
	$row = mysqli_fetch_array($user_data_query);

	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];
	?>

	<form action="settings.php" method="POST">
		苗字: <input type="text" name="first_name" value="<?php echo $first_name; ?>" id="settings_input"><br>
		名前: <input type="text" name="last_name" value="<?php echo $last_name; ?>" id="settings_input"><br>
		メールアドレス: <input type="text" name="email" value="<?php echo $email; ?>" id="settings_input"><br>

		<?php echo $message; ?>

		<input type="submit" name="update_details" id="save_details" value="詳細を変更する" class="info settings_submit"><br>
	</form>

	<h4>パスワードを変更</h4>
	<form action="settings.php" method="POST">
		古いパスワード: <input type="password" name="old_password" id="settings_input"><br>
		新しいパスワード: <input type="password" name="new_password_1" id="settings_input"><br>
		新しいパスワード確認用: <input type="password" name="new_password_2" id="settings_input"><br>

		<?php echo $password_message; ?>

		<input type="submit" name="update_password" id="save_details" value="変更する" class="info settings_submit"><br>
	</form>

	<h4>アカウントの閉鎖</h4>
	<form action="settings.php" method="POST">
		<input type="submit" name="close_account" id="close_account" value="閉鎖する" class="danger settings_submit">
	</form>


</div>