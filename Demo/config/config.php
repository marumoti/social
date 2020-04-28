<?php
ob_start();
session_start();

$timezone = date_default_timezone_set("Asia/Tokyo");

$con = mysqli_connect("localhost", "root", "", "social");
if (mysqli_connect_errno()) {
    echo "接続に失敗しました" . mysqli_connect_errno();
}
