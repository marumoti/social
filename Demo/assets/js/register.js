$(document).ready(function () {

    //クリックをしたらサインアップページとログインページを切り替える
    $("#signup").click(function () {
        $("#first").slideUp("slow", function () {
            $("#second").slideDown("slow");
        });
    });

    $("#signin").click(function () {
        $("#second").slideUp("slow", function () {
            $("#first").slideDown("slow");
        });
    });
});