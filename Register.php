<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 카카오 소셜 로그인 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_pass에 저장한다.
    $userEmail = $_POST["userEmail"];
    $userPassword = $_POST["userPassword"];
    $userNickname = $_POST["userNickname"];
    $userPlatformType = $_POST["userPlatformType"]; // 플랫폼 타입
    $userToken = $_POST["userToken"]; // 유저 토큰
    $userProfileImg = null; // 프로필 이미지 null값
    // $userRegtime = now()

    // 비밀번호 암호화 처리
    $userPassword = md5($userPassword);

    $result=mysqli_query($conn, "INSERT INTO members(user_email,user_pass,user_nickname,profile_image,user_regtime,platform_type,user_token) VALUES ('$userEmail','$userPassword','$userNickname','$userProfileImg',now(),'$userPlatformType','$userToken')");
    // mysqli_close($conn);

    if($result){

        // 현재 가입된 회원정보 바로 조회. (닉네임은 중복X 이므로)
        $sql = "SELECT * FROM members WHERE user_nickname='".$_POST['userNickname']."'"; 
        $ret = mysqli_query($conn, $sql); 
        $exist = mysqli_num_rows($ret); 

        if($exist > 0){

            $response = array(); // response라는 배열 생성
            $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            $response["success"] = true; 
            echo json_encode($response);
            // exit();
    
        // 조회 실패
        }else{
            // 실패
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            echo json_encode($response);
            // exit(); //추가함
        }

    }else {
        // 실패
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        // exit(); //추가함
    }

?>