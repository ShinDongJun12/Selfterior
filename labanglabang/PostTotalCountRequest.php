<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $categoryFilter = $_POST["categoryFilter"]; // 게시글 카테고리
    $userNum = $_POST["userNum"]; // 유저 고유번호
    $userNum = (int)$userNum;

    
    // 전체 게시글 불러올 경우. 
    if($categoryFilter === "전체 게시글")
    {
        $sql = "SELECT * FROM post WHERE user_num = $userNum";
    }
    // 특정 게시글 불러올 경우.
    else
    {
        $sql = "SELECT * FROM post WHERE user_num = $userNum AND post_category = '$categoryFilter'";
    }

    $ret = mysqli_query($conn, $sql); 
    $postTotalCount = mysqli_num_rows($ret); // 게시글 총 개수 가져오기. 

    // 쿼리문 조회 성공
    if($ret){

        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true;
        $response["postTotalCount"] = $postTotalCount; 

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