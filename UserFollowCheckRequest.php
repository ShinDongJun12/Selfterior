<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $loginUserNum = $_POST["loginUserNum"]; // 현재 접속유저 고유번호
    $loginUserNumInt = (int)$loginUserNum;

    $targetUserNum = $_POST["targetUserNum"]; // 고유 식별자
    $targetUserNumInt = (int)$targetUserNum;

    // 현재 로그인된 유저가 피드에 들어온 유저를 팔로잉 하고있는지 조회한다.
    $sql = "SELECT * FROM follow WHERE user_num = $loginUserNumInt AND target_user_num = $targetUserNumInt"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 팔로잉 하고있는 경우
    if($exist > 0){
        
        $response = array(); // response라는 배열 생성
        // $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; 

        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    
    // 팔로잉 하고있지 않는 경우
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        echo json_encode($response);
        exit();
    }
?>