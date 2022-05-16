<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 게시물 고유번호
    $postNum = (int)$postNum; // 이미지 개수.

    $postCategory = $_POST["postCategory"]; // 게시물 카테고리

    // 현재 게시물 댓글 총 개수 조회. 
    $sql = "SELECT * FROM comments WHERE comment_delete NOT IN(2) AND post_num = $postNum AND category = '$postCategory'"; 
    $ret = mysqli_query($conn, $sql); 
    $totalComments = mysqli_num_rows($ret); // 게시물 댓글 총 개수 가져오기. 

    // 쿼리문 조회 성공
    if($ret){

        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true;
        $response["totalComments"] = $totalComments; 

        echo json_encode($response);
        
        exit();
    
    // 조회 실패
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        echo json_encode($response);
        exit();
    }
?>