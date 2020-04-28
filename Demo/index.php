<?php
include("includes/header.php");

if (isset($_POST['post'])) {

    $uploadOk = 1;
    $imageName = $_FILES['fileToUpload']['name'];
    $errorMessage = "";

    if ($imageName != "") {
        $targetDir = "assets/images/posts/";
        $imageName = $targetDir . uniqid() . basename($imageName);
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

        if ($_FILES['fileToUpload']['size'] > 10000000) {
            $errorMessage = "申し訳ありません、ファイルが大きすぎます";
            $uploadOk = 0;
        }

        if (strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
            $errorMessage = "jpeg、jpg、pngファイルのみが許可されています";
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                //画像をアップロードしない
            } else {
                //画像をアップロードする
                $uploadOk = 0;
            }
        }
    }

    if ($uploadOk) {
        $post = new Post($con, $userLoggedIn);
        $post->submitPost($_POST['post_text'], 'none', $imageName);
        header("Location: index.php");
    } else {
        echo "<div style='text-align:center;' class='alert alert-danger'>
				$errorMessage
			</div>";
    }
}
?>

<div class="user_details column">
    <a href="<?php echo $userLoggedIn; ?>"> <img src="<?php echo $user['profile_pic']; ?>"></a>

    <div class="user_details_left_right">
        <a href="<?php echo $userLoggedIn; ?>">
            <?php
            echo $user['first_name'] . " " . $user['last_name'];

            ?>
        </a>
        <br>
        <?php echo "投稿数: " . $user['num_posts'] . "<br>";
        echo "お気に入り: " . $user['num_likes'];
        ?>
    </div>
</div>

<div class="main_column column">
    <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <textarea name="post_text" id="post_text" placeholder="今何をしていますか？"></textarea>
        <input type="submit" name="post" id="post_button" value="投稿する">
    </form>


    <div class="posts_area"></div>
    <img id="loading" src="assets/images/icons/loading.gif">

</div>

<script>
    $(function() {

        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
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
                url: "includes/handlers/ajax_load_posts.php",
                type: "POST",
                data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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