<?php  
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; //読み込まれる投稿の数

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);
?>