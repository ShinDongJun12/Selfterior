<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 고유 식별자
    $resultNum = (int)$userNum;

    $userToken = $_POST["userToken"]; // 변경할 유저 토큰.

    // 유저 고유번호로 해당유저의 토큰값을 변경해준다.
    $sql = "UPDATE members SET user_token = '$userToken' WHERE user_num = $resultNum"; 
    $ret = mysqli_query($conn, $sql); 
    
    // 토큰 변경 성공
    if($ret){
    
        $response = array(); // response라는 배열 생성
        $response["success"] = true; // 변경 성공 여부    

        echo json_encode($response);
        exit();

    // 토큰 변경 실패
    }
    else{

        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>