<?php
include("includes/header.php");

if (isset($_POST['cancel'])) {
	header("Location: settings.php");
}

if (isset($_POST['close_account'])) {
	$close_query = mysqli_query($con, "UPDATE users SET user_closed='yes' WHERE username='$userLoggedIn'");
	session_destroy();
	header("Location: register.php");
}


?>

<div class="main_column column">

	<h4>アカウントを閉鎖する</h4>

	アカウントを閉鎖してもよろしいですか？<br><br>
	アカウントを閉鎖すると、プロフィールとすべてのアクティビティが他のユーザーから見えなくなります。<br><br>
	ログインするだけで、いつでもアカウントを再開できます。<br><br>

	<form action="close_account.php" method="POST">
		<input type="submit" name="close_account" id="close_account" value="閉鎖する" class="danger settings_submit">
		<input type="submit" name="cancel" id="update_details" value="閉鎖しない" class="info settings_submit">
	</form>

</div>