<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userEmail = $_POST["userEmail"]; // 회원 이메일

    // 현재 가입된 회원정보 바로 조회. (닉네임은 중복X 이므로)   
    $sql = "SELECT * FROM members WHERE user_email = '$userEmail'"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 존재하는 경우
    if($exist > 0){
        
        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; 

        //$response = array ('success'=>true,'user_num'=>$response[user_num],'user_email'=>$response[user_email],'user_pass'=>$response[user_pass],'user_nickname'=>$response[user_nickname],'platform_type'=>$response[platform_type],'user_regtime'=>$response[user_regtime],'profile_image'=>$response[profile_image],'user_unique_identifier'=>$response[user_unique_identifier]);

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