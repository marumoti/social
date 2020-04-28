<?php
class Notification {
	private $user_obj;
	private $con;

	public function __construct($con, $user){
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}

	public function getUnreadNumber() {
		$userLoggedIn = $this->user_obj->getUsername();
		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");
		return mysqli_num_rows($query);
	}

	public function getNotifications($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";

		if($page == 1)
			$start = 0;
		else 
			$start = ($page - 1) * $limit;

		$set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");

		$query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

		if(mysqli_num_rows($query) == 0) {
			echo "現在、通知はありません";
			return;
		}

		$num_iterations = 0; 
		$count = 1; 

		while($row = mysqli_fetch_array($query)) {

			if($num_iterations++ < $start)
				continue;

			if($count > $limit)
				break;
			else 
				$count++;


			$user_from = $row['user_from'];

			$user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user_from'");
			$user_data = mysqli_fetch_array($user_data_query);


			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($row['datetime']); 
			$end_date = new DateTime($date_time_now); 
			$interval = $start_date->diff($end_date);  
			if ($interval->y >= 1) {
				if ($interval == 1)
					$time_message = $interval->y . "年前";
				else
					$time_message = $interval->y . "年前";
			} else if ($interval->m >= 1) {
				if ($interval->d == 0) {
					$days = " 前";
				} elseif ($interval->d == 1) {
					$days = $interval->d . "日前";
				} else {
					$days = $interval->d . "日前";
				}

				if ($interval->n == 1) {
					$time_message = $interval->m . "月" . $days;
				} else {
					$time_message = $interval->m . "月" . $days;
				}
			} else if ($interval->d >= 1) {
				if ($interval->d == 1) {
					$time_message = "昨日";
				} else {
					$time_message = $interval->d . "日前";
				}
			} else if ($interval->h >= 1) {
				if ($interval->h == 1) {
					$time_message = $interval->h . "時間前";
				} else {
					$time_message = $interval->h . "時間前";
				}
			} else if ($interval->i >= 1) {
				if ($interval->i == 1) {
					$time_message = $interval->i . "分前";
				} else {
					$time_message = $interval->i . "分前";
				}
			} else if ($interval->s < 30) {
				if ($interval->i == 1) {
					$time_message = "たった今";
				} else {
					$time_message = $interval->s . "秒前";
				}
			}
			$opened = $row['opened'];
			$style = ($opened == 'no') ? "background-color: #DDEDFF;" : "";

			$return_string .= "<a href='" . $row['link'] . "'> 
									<div class='resultDisplay resultDisplayNotification' style='" . $style . "'>
										<div class='notificationsProfilePic'>
											<img src='" . $user_data['profile_pic'] . "'>
										</div>
										<p class='timestamp_smaller' id='grey'>" . $time_message . "</p>" . $row['message'] . "
									</div>
								</a>";
		}


		//投稿が読み込まれた場合
		if($count > $limit)
			$return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
		else 
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'>通知はこれ以上ありません!</p>";

		return $return_string;
	}

	public function insertNotification($post_id, $user_to, $type) {

		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedInName = $this->user_obj->getFirstAndLastName();

		$date_time = date("Y-m-d H:i:s");

		switch($type) {
			case 'comment':
				$message = $userLoggedInName . " 投稿にコメントされました";
				break;
			case 'like':
				$message = $userLoggedInName . " 投稿にいいね！が押されました";
				break;
			case 'profile_post':
				$message = $userLoggedInName . " プロファイルに投稿されました";
				break;
			case 'comment_non_owner':
				$message = $userLoggedInName . " 投稿した内容にコメントされました";
				break;
			case 'profile_comment':
				$message = $userLoggedInName . " プロファイルの投稿にコメントされました";
				break;
		}

		$link = "post.php?id=" . $post_id;

		$insert_query = mysqli_query($this->con, "INSERT INTO notifications VALUES('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
	}

}

?>