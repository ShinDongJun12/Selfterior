<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNumStr = $_POST["postNumStr"]; // 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $postNumStr = (int)$postNumStr;

    $commentNumStr = $_POST["commentNumStr"]; // 댓글/답글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $commentNumStr = (int)$commentNumStr;

    $userNumStr = $_POST["userNumStr"]; // 현재 접속 유저 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $userNumStr = (int)$userNumStr;

    // ※ 이 코드에서는 (1) 현재 로그인된 사용자가 해당 댓글/답글을 좋아요 누르고 있는지와 (2) 해당 댓글/답글의 총 조아요 개수를 가져오고 있다.

    // 사용자가 해당 댓글/답글에 좋아요를 누른 상태인지 아닌지 조회하여 체크한다. 
    $sql = "SELECT * FROM comment_like WHERE post_num = $postNumStr AND user_num = $userNumStr AND comment_num = $commentNumStr"; 
    $ret = mysqli_query($conn, $sql); 
    $check = mysqli_num_rows($ret);

    // 좋아요 누른상태 이면 
    if($check > 0){
  
        // ★★★ 여기서는 댓글/답글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
        // 댓글/답글의 총 개수 조회
        $sql = "SELECT * FROM comment_like WHERE post_num = $postNumStr AND comment_num = $commentNumStr"; 
        $ret = mysqli_query($conn, $sql); 
        $commentLikeTotal = mysqli_num_rows($ret);

        $response = array(); // response라는 배열 생성
        
        $response["success"] = true; // 좋아요 누른 상태 
        $response["commentLikeTotal"] = $commentLikeTotal; // 해당 댓글/답글 좋아요 총 개수 데이터

        echo json_encode($response);
        exit();
    }
    // 좋아요 안누른 상태이면
    else{

         // ★★★ 여기서는 댓글/답글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
        // 댓글/답글 총 개수 조회
        $sql = "SELECT * FROM comment_like WHERE post_num = $postNumStr AND comment_num = $commentNumStr"; 
        $ret = mysqli_query($conn, $sql); 
        $commentLikeTotal = mysqli_num_rows($ret);

        $response = array(); // response라는 배열 생성
        
        $response["success"] = false; // 좋아요 안 누른 상태
        $response["commentLikeTotal"] = $commentLikeTotal; // 해당 댓글/답글 좋아요 총 개수 데이터
    
        echo json_encode($response);
        exit();
    }
?>