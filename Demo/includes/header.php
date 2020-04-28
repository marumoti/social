<?php
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Notification.php");


if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_datails_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_datails_query); //ログインしたユーザーのデータをデータベースから全て取得
} else {
    header("Location: register.php");
}
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/bootbox.min.js"></script> <!-- bootboxでのダイアログ生成-->
    <script src="assets/js/demo.js"></script>
    <script src="assets/js/jquery.Jcrop.js"></script> <!-- jqueryで画像の一部分を切りぬき -->
    <script src="assets/js/jcrop_bits.js"></script> <!-- jqueryで画像の一部分を切りぬき -->


    <!-- css -->
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />　
    <!-- jqueryで画像の一部分を切りぬき -->

    <title>ようこそ</title>
</head>

<body>

    <div class="top_bar">
        <div class="logo">
            <a href="index.php">ソーシャル掲示板</a>
        </div>

        <div class="search">

            <form action="search.php" method="GET" name="search_form">
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="探す" autocomplete="off" id="search_text_input">

                <div class="button_holder">
                    <img src="assets/images/icons/magnifying_glass.png">
                </div>

            </form>

            <div class="search_results">
            </div>

            <div class="search_results_footer_empty">
            </div>



        </div>

        <nav>

            <?php

            //未読メッセージ
            $messages = new Message($con, $userLoggedIn);
            $num_messages = $messages->getUnreadNumber();

            //未読の通知
            $notifications = new Notification($con, $userLoggedIn);
            $num_notifications = $notifications->getUnreadNumber();

            // 未読の通知
            $user_obj = new User($con, $userLoggedIn);
            $num_requests = $user_obj->getNumberOfFriendRequests();
            ?>

            <a href="<?php echo $userLoggedIn; ?>">
                <?php echo $user['first_name']; ?>
            </a>
            <a href="#">
                <i class="fa fa-home fa-lg" aria-hidden="true"></i>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
                <i class="fa fa-envelope fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_messages > 0)
                    echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
                ?>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
                <i class="fa fa-bell fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_notifications > 0)
                    echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
                ?>
            </a>
            <a href="requests.php">
                <i class="fa fa-users fa-lg" aria-hidden="true"></i>
                <?php
                if ($num_requests > 0)
                    echo '<span class="notification_badge" id="unread_requests">' . $num_requests . '</span>';
                ?>
            </a>
            <a href="settings.php">
                <i class="fa fa-cog fa-lg" aria-hidden="true"></i>
            </a>
            <a href="includes/handlers/logout.php">
                <i class="fa fa-sign-out fa-lg" aria-hidden="true"></i>
            </a>
        </nav>

        <div class="dropdown_data_window" style="height:0px; border:none;"></div>

    </div>

    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';

        $(document).ready(function() {

            $('.dropdown_data_window').scroll(function() {
                var inner_height = $('.dropdown_data_window').innerHeight(); //Divを含むデータ
                var scroll_top = $('.dropdown_data_window').scrollTop();
                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                    var pageName; //ajaxの読み込み
                    var type = $('#dropdown_data_type').val();


                    if (type == 'notification')
                        pageName = "ajax_load_notifications.php";
                    else if (type == 'message')
                        pageName = "ajax_load_messages.php"


                    var ajaxReq = $.ajax({
                        url: "includes/handlers/" + pageName,
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                        cache: false,

                        success: function(response) {
                            $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //ページの消去
                            $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //ページの消去


                            $('.dropdown_data_window').append(response);
                        }
                    });

                } //End if 

                return false;

            }); //End (window).scroll(function())


        });
    </script>

    <div class="wrapper">