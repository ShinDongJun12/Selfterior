<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $platformCheck = $_POST["platformCheck"]; // 소셜로그인 플랫폼 
    // 사용자의 고유식별자를 암호화해서 DB의 해당 비밀번호와 대조한다.
    $userIdentifier = md5($userIdentifier = $_POST['userIdentifier']);

    $sql = "SELECT * FROM members WHERE user_unique_identifier='$userIdentifier' AND platform_type='$platformCheck'";
    // echo  $sql;
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);

    //print_r($member);

    // 가입된 계정이 없으면
	if($rs_num==0){
        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        echo json_encode($response);
        exit();

    // 가입된 계정이 있으면
    }else{
        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; 
        echo json_encode($response);
        exit();
    }
?>