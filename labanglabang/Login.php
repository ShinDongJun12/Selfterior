<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 회원 아이디와 비밀번호를 매개변수로 받은 후 
    $userEmail = $_POST["userEmail"];
    // 사용자가 입력한 비밀번호를 암호화해서 DB의 해당 비밀번호와 대조한다.
    $userPassword = md5($userPassword = $_POST['userPassword']);
    // 조회된 토큰값을 암호화해서 DB의 해당 비밀번호와 대조한다.
    $userToken = $_POST['userToken'];


    // 클라이언트로 부터 받아온 이메일과 비밀번호로 일치하는 회원이 존재하는지 조회한다.
    $sql = "SELECT * FROM members WHERE user_email='$userEmail' AND user_pass='$userPassword'";
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);

    // 가입된 계정이 있으면
	if($rs_num > 0){

        // 해당 유저의 토큰 값을 조회한다.
        $sql = "SELECT * FROM members WHERE user_email='$userEmail' AND user_pass='$userPassword'";
        $result = mysqli_query($conn, $sql);
        $user_info = mysqli_fetch_assoc($result);

        // 조회한 토큰값과 클라이언트로 부터 받아온 토큰값이 일치하면 (유저가 기존에 로그인 했던 디바이스와 같은 디바이스에서 접속한 경우이다.)
        // strcasesmp() -> 대소문자 구분없이 비교
        if(strcmp($userToken, $user_info['user_token']) == 0){

            $response = array(); // response라는 배열 생성
            // $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. 
            $response["user_num"] = $user_info['user_num']; // response배열에서 user_num라는 이름을 가진 변수의 값을 조회된 user_num값을 문자열로 초기화 해준다.
            $response["platform_type"] = $user_info['platform_type']; // response배열에서 platform_type라는 이름을 가진 변수의 값을 조회된 platform_type 문자열로 초기화 해준다.
            $response["user_nickname"] = $user_info['user_nickname']; // response배열에서 user_nickname라는 이름을 가진 변수의 값을 조회된 user_nickname 문자열로 초기화 해준다.
            $response["loginCheck"] = "tokenMatches"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 tokenMatches문자열로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }
        // 다른 디바이스에서 로그인한 경우.
        else{

            $response = array(); // response라는 배열 생성
            // $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. 
            $response["user_num"] = $user_info['user_num']; // response배열에서 user_num라는 이름을 가진 변수의 값을 조회된 user_num값을 문자열로 초기화 해준다.
            $response["platform_type"] = $user_info['platform_type']; // response배열에서 platform_type라는 이름을 가진 변수의 값을 조회된 platform_type 문자열로 초기화 해준다.
            $response["user_nickname"] = $user_info['user_nickname']; // response배열에서 user_nickname라는 이름을 가진 변수의 값을 조회된 user_nickname 문자열로 초기화 해준다.
            $response["loginCheck"] = "tokenDiscrepancy"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 tokenDiscrepancy문자열로 초기화 해준다.
        
            // ★ 결과값 계속 널값떠서 돌아버리겠다. JSON으로 엔코딩할때 한글로 바꿔주는 설정 해보자
            //echo json_encode($response, JSON_UNESCAPED_UNICODE);
            echo json_encode($response);
            exit();
        }

    // 가입된 계정이 없으면
    }else{
        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response["loginCheck"] = "noAccount"; // response배열에서 loginCheck라는 이름을 가진 변수의 디폴트값을 noAccount문자열로 초기화 해준다.
        //$response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.

        echo json_encode($response);
        exit();
    }
?>