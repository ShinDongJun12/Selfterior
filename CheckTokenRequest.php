<?php
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 유저 고유번호.
    $userNum = (int)$userNum;

    $token = $_POST['token']; // 현재 조회된 토큰 받아와서 암호화.

    $sql = "SELECT user_token FROM members WHERE user_num = $userNum";
    $result = mysqli_query($conn, $sql);
    $user_info = mysqli_fetch_assoc($result);

    // 저장된 토큰 조회성공
	if($result){

        // 조회한 토큰값과 클라이언트로 부터 받아온 토큰값이 일치하면 (유저가 기존에 로그인 했던 디바이스와 같은 디바이스에서 접속한 경우이다.)
        // strcasesmp() -> 대소문자 구분없이 비교
        if(strcmp($token, $user_info['user_token']) == 0){

            $response = array(); // response라는 배열 생성
            $response["loginCheck"] = "tokenMatches"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 tokenMatches문자열로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }
        // 다른 디바이스에서 로그인한 경우.
        else{

            $response = array(); // response라는 배열 생성
            $response["loginCheck"] = "tokenDiscrepancy"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 tokenDiscrepancy문자열로 초기화 해준다.
            //$response["success"] = true; // -> SELECT * FROM members로 DB조회하였기 때문에 response 배열안에 user_num등 값이 다 들어간다.
        
            echo json_encode($response);
            exit();
        }

    // 조회실패
    }else{

        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response["loginCheck"] = "noAccount"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 noAccount문자열로 초기화 해준다.
        //$response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.

        echo json_encode($response);
        exit();
    }
?>