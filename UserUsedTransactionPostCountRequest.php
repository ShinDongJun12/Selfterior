<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $targetUserNum = $_POST["targetUserNum"]; // 피드 유저 고유번호
    $targetUserNumInt = (int)$targetUserNum;


    // 피드 유저 고유번호로 작성된 중고거래 게시물 중 거래상태가 '거래완료'인 게시물을 조외한 총 개수를 조회한다.
    $sql = "SELECT * FROM used_transaction_post WHERE user_num = $targetUserNumInt AND transaction_status NOT IN('거래완료')"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret);  // 게시물 총 개수

    // 조회 성공
    if($ret){
        
        $response = array(); // response라는 배열 생성
        // $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; 
        $response["postTotalCount"] = $exist;

        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    
    // 조회 실패
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        echo json_encode($response);
        exit();
    }
?>