<!-- <!DOCTYPE html> -->
<?php
require 'config/config.php';
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';

?>


<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/register_style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="assets/js/register.js"></script>
    <title>ようこそ</title>
</head>

<body>
    <?php

    if (isset($_POST["register_button"])) {
        echo '
    <script>

    $(document).ready(function(){
        $("#first").hide();
        $("#second").show();
    });

    </script>
    ';
    }

    ?>

    <div class="wrapper">
        <div class="login_box">
            <div class="login_header">
                <h1>ソーシャル掲示板</h1>
                以下からログイン又はサインアップ
            </div>

            <div id="first">
                <form action="register.php" method="POST">
                    <input type="email" name="log_email" placeholder="メールアドレス" value="<?php if (isset($_SESSION['log_email'])) {
                                                                                            echo $_SESSION['log_email'];
                                                                                        } ?>" required>
                    <br>
                    <input type="password" name="log_password" placeholder="パスワード">
                    <br>
                    <?php if (in_array("メールアドレスかパスワードが間違えています<br>", $error_array))
                        echo "メールアドレスかパスワードが間違えています<br>";
                    ?>
                    <input type="submit" name="login_button" value="ログインする">
                    <br>
                    <a href="#" id="signup" class="signup">アカウントが未登録の方はこちらをクリック</a>
                </form>
            </div>

            <div id="second">
                <form action="register.php" method="POST">

                    <input type="text" name="reg_fname" placeholder="苗字" value="<?php if (isset($_SESSION['reg_fname'])) {
                                                                                    echo $_SESSION['reg_fname'];
                                                                                } ?>" required>
                    <br>
                    <?php if (in_array("苗字は2文字から25文字以内で入力してください<br>", $error_array))
                        echo "苗字は2文字から25文字以内で入力してください<br>"; ?>

                    <input type="text" name="reg_lname" placeholder="名前" value="<?php if (isset($_SESSION['reg_lname'])) {
                                                                                    echo $_SESSION['reg_lname'];
                                                                                } ?>" required>
                    <br>
                    <?php if (in_array("名前は2文字から25文字以内で入力してください<br>", $error_array))
                        echo "名前は2文字から25文字以内で入力してください<br>"; ?>

                    <input type="email" name="reg_email" placeholder="メールアドレス" value="<?php if (isset($_SESSION['reg_email'])) {
                                                                                            echo $_SESSION['reg_email'];
                                                                                        } ?>" required>
                    <br>
                    <input type="email" name="reg_email2" placeholder="メールアドレス確認用" value="<?php if (isset($_SESSION['reg_email2'])) {
                                                                                                echo $_SESSION['reg_email2'];
                                                                                            } ?>" required>
                    <br>
                    <?php if (in_array("既にメールアドレスは登録されています<br>", $error_array))
                        echo "既にメールアドレスは登録されています<br>";
                    else if (in_array("無効な形式です<br>", $error_array))
                        echo "無効な形式です<br>";
                    else if (in_array("メールアドレスが一致しません<br>", $error_array))
                        echo "メールアドレスが一致しません<br>"; ?>

                    <input type="password" name="reg_password" placeholder="パスワード" required>
                    <br>
                    <input type="password" name="reg_password2" placeholder="確認用パスワード" required>
                    <br>
                    <?php if (in_array("パスワードが一致しません<br>", $error_array))
                        echo "パスワードが一致しません<br>";
                    else if (in_array("パスワードは半角英数字しか使用できません<br>", $error_array))
                        echo "パスワードは半角英数字しか使用できません<br>";
                    else if (in_array("パスワードは5~30文字以内で入力してください<br>", $error_array))
                        echo "パスワードは5~30文字以内で入力してください<br>"; ?>

                    <input type="submit" name="register_button" value="登録する">
                    <br>

                    <?php if (in_array("<span style='color: #14C800;'>登録が完了しました!</span><br>", $error_array))
                        echo "<span style='color: #14C800;'>登録が完了しました!</span><br>";
                    ?>
                    <a href="#" id="signin" class="signin">アカウントが登録済みの方はこちらをクリック</a>

                </form>
            </div>
        </div>
    </div>
</body>

</html>