<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 유저 고유 번호.
    $userNum = (int)$userNum;

    // 현재 가입된 회원정보 바로 조회. (닉네임은 중복X 이므로)   
    $sql = "SELECT user_nickname FROM members WHERE user_num = $userNum"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 유저가 존재하면.
    if($exist > 0){
    
        $response = array(); // response라는 배열 생성
        // ★조회된 값이 존재하므로 그 값들의 response 배열에 담는다.★
        $response = mysqli_fetch_array($ret); // 조회한 유저 닉네임 저장.
        $response["success"] = true;
        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();

    }
    // 조회 실패.
    else{
        
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>