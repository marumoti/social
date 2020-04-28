<?php
class Post
{
    private $user_obj;
    private $con;

    public function __construct($con, $user) //初期化
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to, $imageName)
    {
        $body = strip_tags($body); //HTMLタグを取り除く
        $body = mysqli_real_escape_string($this->con, $body);

        $body = str_replace('\r\n', '\n', $body);
        $body = nl2br($body); //改行を<br>タグとして挿入する

        $check_empty = preg_replace('/\s+/', '', $body); //スペースを取り除く

        if ($check_empty != "") {

            //Dateタイムの取得
            $date_added = date("Y-m-d H:i:s");

            //usernameの取得
            $added_by = $this->user_obj->getUsername();

            //ユーザーが自分のプロファイルを覗いているとき、noneを入れる
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('','$body','$added_by','$user_to','$date_added','no','no','0','$imageName')");
            $returned_id = mysqli_insert_id($this->con);

            //通知を挿入
            if ($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "like");
            }

            //ユーザーの投稿数の更新
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");
        }
    }
    public function loadPostFriends($data, $limit)
    {

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //チェックされた結果の数
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];
                $imagePath = $row['image'];
 
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                //アカウントが閉鎖されているか確認
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    continue;
                }

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {  //友達と自分の投稿のみ表示

                    if ($num_iterations++ < $start)
                        continue;

                    //10件の投稿が読み込まれたら中止
                    if ($count > $limit) {
                        break;
                    } else {
                        $count++;
                    }

                    if ($userLoggedIn == $added_by)
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name,profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

?>
                    <script>
                        function toggle<?php echo $id; ?>() {

                            var target = $(event.target);
                            if (!target.is("a")) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if (element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>
                <?php

                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time);
                    $end_date =  new DateTime($date_time_now);
                    $interval = $start_date->diff($end_date);
                    $time_message = "";
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

                    if($imagePath != "") {
						$imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
									</div>";
					}
					else {
						$imageDiv = "";
					}

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                    <div class='post_profile_pic'>
                        <img src='$profile_pic' width='50'>
                    </div>

                    <div class='posted_by' style='color:#ACACAC;'>
                        <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                        $delete_button
                    </div>
                    <div id='post_body'>
                        $body
                        <br>
                        $imageDiv
                        <br>
                        <br>
                    </div>

                    <div class='newsfeedPostOptions'>
                                    コメント($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
					</div>
                </div>
                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                </div>
                <hr>";
                }
                ?>
                <script>
                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("この投稿を削除してもよろしいですか？", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();

                            });
                        });


                    });
                </script>
            <?php
            }

            if ($count > $limit)
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                       <input type='hidden' class='noMorePosts' value='false'>";
            else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'class='noMorePostsText'>表示する投稿はこれ以上ありません</p>";
        }

        echo $str;
    }

    public function loadProfilePosts($data, $limit)
    {

        $page = $data['page'];
        $profileUser = $data['profileUsername'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser')  ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //チェックされた結果の数
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                if ($num_iterations++ < $start)
                    continue;

                //10件の投稿が読み込まれたら中止
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                if ($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name,profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

            ?>
                <script>
                    function toggle<?php echo $id; ?>() {

                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }
                </script>
                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time);
                $end_date =  new DateTime($date_time_now);
                $interval = $start_date->diff($end_date);
                if ($interval->y >= 1) {
                    if ($interval->y == 1)
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

                    if ($interval->m == 1) {
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
                    $time_message = "たった今";
                } else {
                    $time_message = $interval->s . "秒前";
                }
                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                    <div class='post_profile_pic'>
                        <img src='$profile_pic' width='50'>
                    </div>

                    <div class='posted_by' style='color:#ACACAC;'>
                        <a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                        $delete_button
                    </div>
                    <div id='post_body'>
                        $body
                        <br>
                        <br>
                        <br>
                    </div>

                    <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
					</div>
                </div>
                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                </div>
                <hr>";

                ?>
                <script>
                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("この投稿を削除してもよろしいですか？", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();

                            });
                        });


                    });
                </script>
            <?php
            }

            if ($count > $limit)
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                       <input type='hidden' class='noMorePosts' value='false'>";
            else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;' class='noMorePostsText'>表示する投稿はこれ以上ありません</p>";
        }

        echo $str;
    }

    public function getSinglePost($post_id)
    {

        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if (mysqli_num_rows($data_query) > 0) {


            $row = mysqli_fetch_array($data_query);
            $id = $row['id'];
            $body = $row['body'];
            $added_by = $row['added_by'];
            $date_time = $row['date_added'];

            if ($row['user_to'] == "none") {
                $user_to = "";
            } else {
                $user_to_obj = new User($this->con, $row['user_to']);
                $user_to_name = $user_to_obj->getFirstAndLastName();
                $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
            }

            //アカウントが閉鎖されているか確認
            $added_by_obj = new User($this->con, $added_by);
            if ($added_by_obj->isClosed()) {
                return;
            }

            $user_logged_obj = new User($this->con, $userLoggedIn);
            if ($user_logged_obj->isFriend($added_by)) {


                if ($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";


                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];


            ?>
                <script>
                    function toggle<?php echo $id; ?>(e) {

                        if (!e) e = window.event;

                        var target = $(e.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }
                </script>
                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);


                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); 
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

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";


                ?>
                <script>
                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("この投稿を削除してもよろしいですか？", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {
                                    result: result
                                });

                                if (result)
                                    location.reload();

                            });
                        });


                    });
                </script>
<?php
            } else {
                echo "<p>このユーザーと友達ではないため、この投稿を表示できません。</p>";
                return;
            }
        } else {
            echo "<p>投稿が見つかりません</p>";
            return;
        }

        echo $str;
    }
}
