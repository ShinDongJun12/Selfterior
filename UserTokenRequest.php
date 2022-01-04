<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    $userNum = $_POST["userNum"]; // 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$userNum;

    // 유저 고유번호로 토큰값을 조회한다.
    $sql = "SELECT user_token FROM members WHERE user_num = $resultNum"; 
    $ret = mysqli_query($conn, $sql); 
    $user_info = mysqli_fetch_assoc($ret);
    
    // 조회 성공
    if($ret){
    
        $response = array(); // response라는 배열 생성
        $response["success"] = true; // 조회 성공여부    
        $response["userToken"] = $user_info['user_token']; // 유저 토큰값   

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