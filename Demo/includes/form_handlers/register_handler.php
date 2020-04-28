<?php

//エラーへの変数の宣言
$fname = ""; //苗字
$lname = ""; //名前
$em = ""; //email
$em2 = ""; //email2
$password = ""; //password
$password2 = ""; //password2
$date = ""; // サインアップの日付
$error_array = array(); // エラーメッセージの保持

if (isset($_POST['register_button'])) {

    //登録フォームの値

    $fname = strip_tags($_POST['reg_fname']); //HTMLタグを取り除く
    $fname = str_replace(' ', '', $fname); //スペースと空白を取り除く
    $fname = ucfirst(strtolower($fname)); //strolowerで小文字にし、ucfirstで先頭を大文字にする
    $_SESSION['reg_fname'] = $fname; //名前をセッション変数に格納

    $lname = strip_tags($_POST['reg_lname']); //HTMLタグを取り除く
    $lname = str_replace(' ', '', $lname); //スペースと空白を取り除く
    $lname = ucfirst(strtolower($lname)); //strolowerで小文字にし、ucfirstで先頭を大文字にする
    $_SESSION['reg_lname'] = $lname; //名前をセッション変数に格納


    $em = strip_tags($_POST['reg_email']); //HTMLタグを取り除く
    $em = str_replace(' ', '', $em); //スペースと空白を取り除く
    $em = ucfirst(strtolower($em)); //strolowerで小文字にし、ucfirstで先頭を大文字にする
    $_SESSION['reg_email'] = $em; //メールアドレスをセッション変数に格納


    $em2 = strip_tags($_POST['reg_email2']); //HTMLタグを取り除く
    $em2 = str_replace(' ', '', $em2); //スペースと空白を取り除く
    $em2 = ucfirst(strtolower($em2)); //strolowerで小文字にし、ucfirstで先頭を大文字にする
    $_SESSION['reg_email2'] = $em2; //メールアドレスをセッション変数に格納


    $password = strip_tags($_POST['reg_password']); //HTMLタグを取り除く
    $password = str_replace(' ', '', $password); //スペースと空白を取り除く
    $password = ucfirst(strtolower($password)); //strolowerで小文字にし、ucfirstで先頭を大文字にする

    $password = strip_tags($_POST['reg_password']); //HTMLタグを取り除く
    $password2 = strip_tags($_POST['reg_password2']); //HTMLタグを取り除く

    $date = date("Y-m-d"); //現在の日時を取得

    if ($em == $em2) {
        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
            $em = filter_var($em, FILTER_VALIDATE_EMAIL);

            //メールアドレスの重複が無いかチェック
            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em' ");

            //一致した行数を返す
            $num_rows = mysqli_num_rows($e_check);

            if ($num_rows > 0) {
                array_push($error_array, "既にメールアドレスは登録されています<br>");
            }
        } else {
            array_push($error_array, "無効な形式です<br>");
        }
    } else {
        array_push($error_array, "メールアドレスが一致しません<br>");
    }

    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "苗字は2文字から25文字以内で入力してください<br>");
    }

    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "名前は2文字から25文字以内で入力してください<br>");
    }

    if ($password != $password2) {
        array_push($error_array, "パスワードが一致しません<br>");
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($error_array, "パスワードは半角英数字しか使用できません<br>");
        }
    }

    if (strlen($password) > 30 || strlen($password) < 5) {
        array_push($error_array, "パスワードは5~30文字以内で入力してください<br>");
    }

    if (empty($error_array)) {
        $password = md5($password); //パスワードをハッシュ化
        //ユーザーの作成
        $username = strtolower($fname . "_" . $lname);
        $check_user_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");

        $i = 0;
        while (mysqli_num_rows($check_user_query) != 0) {
            $i++;
            $username = $username . "_" . $i;
            $check_user_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
        }

        //プロファイルの画像
        $rand = rand(1, 2);
        if ($rand == 1)
            $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        else if ($rand == 2)
            $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";

        $query = mysqli_query($con, "INSERT INTO users VALUE('','$fname','$lname','$username','$em','$password','$date','$profile_pic','0','0','no',',')");

        array_push($error_array, "<span style='color: #14C800;'>登録が完了しました!</span><br>");

        //登録完了後のセッションの値をクリア
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}
