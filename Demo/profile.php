<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if (isset($_GET['profile_username'])) {
	$username = $_GET['profile_username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
	$user_array = mysqli_fetch_array($user_details_query);

	$num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if (isset($_POST['remove_friend'])) {
	$user = new User($con, $userLoggedIn);
	$user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
	$user = new User($con, $userLoggedIn);
	$user->sendRequest($username);
}
if (isset($_POST['respond_request'])) {
	header("Location: requests.php");
}

if (isset($_POST['post_message'])) {
	if (isset($_POST['message_body'])) {
		$body = mysqli_real_escape_string($con, $_POST['message_body']);
		$date = date("Y-m-d H:i:s");
		$message_obj->sendMessage($username, $body, $date);
	}

	$link = '#profileTabs a[href="#messages_div"]';
	echo "<script> 
			$(function() {
				$('" . $link . "').tab('show');
			});
		  </script>";
}
?>

<style type="text/css">
	.wrapper {
		margin-left: 0px;
		padding-left: 0px;
	}
</style>

<div class="profile_left">
	<img src="<?php echo $user_array['profile_pic']; ?>">

	<div class="profile_info">
		<p><?php echo "投稿数 " . $user_array['num_posts']; ?></p>
		<p><?php echo "いいね数 " . $user_array['num_likes']; ?></p>
		<p><?php echo "友達の数 " . $num_friends ?></p>
	</div>

	<form action="<?php echo $username; ?>" method="POST">
		<?php
		$profile_user_obj = new User($con, $username);
		if ($profile_user_obj->isClosed()) {
			header("Location: user_closed.php");
		}

		$logged_in_user_obj = new User($con, $userLoggedIn);

		if ($userLoggedIn != $username) {

			if ($logged_in_user_obj->isFriend($username)) {
				echo '<input type="submit" name="remove_friend" class="danger" value="友達を削除"><br>'; //既に友達だったらこのボタン表示
			} else if ($logged_in_user_obj->didReceiveRequest($username)) {
				echo '<input type="submit" name="respond_request" class="warning" value="リクエストに応答する"><br>';
			} else if ($logged_in_user_obj->didSendRequest($username)) {
				echo '<input type="submit" name="" class="default" value="リクエストを送りました"><br>';
			} else
				echo '<input type="submit" name="add_friend" class="success" value="友達に追加"><br>'; //友達ではなかったらこのボタンを表示

		}

		?>
	</form>
	<input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="投稿">

	<?php
	if ($userLoggedIn != $username) {
		echo '<div class="profile_info_bottom">';
		echo $logged_in_user_obj->getMutualFriends($username) . " 共通の友人";
		echo '</div>';
	}
	?>
</div>



<div class="profile_main_column column">

	<ul class="nav nav-tabs" role="tablist" id="profileTabs">
		<li role="presentation" class="active"><a class="nav-link" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">投稿一覧</a></li>
		<li role="presentation"><a class="nav-link" href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">チャット</a></li>
	</ul>

	<div class="tab-content">

		<div role="tabpanel" class="tab-pane active" id="newsfeed_div">
			<div class="posts_area"></div>
			<img id="loading" src="assets/images/icons/loading.gif">
		</div>


		<div role="tabpanel" class="tab-pane" id="messages_div">
			<?php


			echo "<h4> <a href='" . $username . "'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";

			echo "<div class='loaded_messages' id='scroll_messages'>";
			echo $message_obj->getMessages($username);
			echo "</div>";
			?>



			<div class="message_post">
				<form action="" method="POST">
					<textarea name='message_body' id='message_textarea' placeholder='メッセージを書いてください'></textarea>
					<input type='submit' name='post_message' class='info' id='message_submit' value='送る'>
				</form>

			</div>

			<script>
				$('a[data-toggle="tab"]').on('shown.bs.tab', function() {
					var div = document.getElementById("scroll_messages");
					div.scrollTop = div.scrollHeight;
				});
			</script>
		</div>


	</div>


</div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<h4 class="modal-title" id="postModalLabel">コメントしてください</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

			</div>

			<div class="modal-body">
				<p>このコメントはプロファイルページに投稿されます</p>

				<form class="profile_post" action="" method="POST">
					<div class="form-group">
						<textarea class="form-control" name="post_body"></textarea>
						<input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
						<input type="hidden" name="user_to" value="<?php echo $username; ?>">
					</div>
				</form>
			</div>


			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">閉じる
				</button>
				<button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">投稿する</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(function() {

		var userLoggedIn = '<?php echo $userLoggedIn; ?>';
		var profileUsername = '<?php echo $username; ?>';
		var inProgress = false;


		loadPosts(); //最初の投稿を読み込む

		$(window).scroll(function() {
			var bottomElement = $(".status_post").last();
			var noMorePosts = $('.posts_area').find('.noMorePosts').val();

			if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
				loadPosts();
			}
		});

		function loadPosts() {
			if (inProgress) {
				return;
			}

			inProgress = true;
			$('#loading').show();

			var page = $('.posts_area').find('.nextPage').val() || 1;

			$.ajax({
				url: "includes/handlers/ajax_load_profile_posts.php",
				type: "POST",
				data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" +profileUsername,
				cache: false,

				success: function(response) {
					$('.posts_area').find('.nextPage').remove();
					$('.posts_area').find('.noMorePosts').remove();
					$('.posts_area').find('.noMorePostsText').remove();

					$('#loading').hide();
					$(".posts_area").append(response);

					inProgress = false;
				}
			});
		}

		//要素が表示されているか確認
		function isElementInView(el) {
			var rect = el.getBoundingClientRect();

			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
				rect.right <= (window.innerWidth || document.documentElement.clientWidth)
			);
		}
	});
</script>

</div>
</body>

</html>