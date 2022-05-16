<?php 
    /** - include 는 파일이 없어도 경고만 나올 뿐 PHP는 계속 동작한다. 파일 하나가 여러번 호출되면 호출되는 횟수만큼 파일을 포함시킨다.
     *  - include_once 는 파일이 없어도 경고만 나올 뿐 PHP는 계속 동작한다. 파일 하나가 여러번 호출되어도 처음 한번만 파일을 불러온다.
     *  - require 는 파일이 없으면 오류가 나며 PHP의 실행이 완전히 멈춘다. 파일 하나가 여러번 호출되면 호출되는 횟수만큼 파일을 포함시킨다.
     *  - require_once는 파일이 없으면 오류가 나며 PHP의 실행이 완전히 멈춘다. 파일 하나가 여러번 호출되어도 처음 한번만 파일을 불러온다.
     */
    //ini_set('display_errors', true);
    include "dbcon.php"; 
    require "JWT.php"; // JWT 클래스가 선언되어있는 php파일을 포함한다.

    mysqli_query($conn,'SET NAMES utf8'); 

    $userEmail = $_POST["userEmail"]; // 회원 이메일
    $response = array(); // response라는 배열 생성

    // 현재 가입된 회원정보 바로 조회. (플랫폼 타입이 'email'이고, 받아온 이메일 주소와 일치하는 계정의 회원 정보 조회(유저 고유번호,닉네임))   
    $sql = "SELECT * FROM members WHERE user_email='$userEmail' AND platform_type ='email'"; 
    $ret = mysqli_query($conn, $sql); 
    $response = mysqli_fetch_assoc($ret); // ★ 이렇게 해줘야 response를 통해 조회된 특정 값을 사용할 수 있다.
    $exist = mysqli_num_rows($ret); 

    // 존재하는 경우
    if($exist > 0){

        $jwt = new JWT();

        $user_num = base64_encode($response['user_num']); // .이 들어가도 JWT가 분리되지 않기 위한 base64 인코딩

        // 유저 고유번호 정보를 가진 jwt 생성.
        $token = $jwt->hashing(array(
            'exp' => time() + 120, // 만료기간.(임의로 2분설정)  (보통 재설정 페이지 하루정도 유효함 86400 : 24시간)     (360 * 30) -> 3시간
            'iat' => time(), // 생성일.  -> * time(): 1970년 1월1일 0시0분0초부터 지금까지 지나온 초를 정수형태로 리턴해주는 함수. ex) 1356066385
            'id' => 10,
            'user_num' => $user_num
        ));

        // * var_dump(): 말그대로 var의 정보를 dump 해주는 함수. -> ()안에 있는 변수형, 즉 변수가 int인지, float인지, array등등 인지 출력해준다.
        // var_dump($token); 

        //$response = array(); // response라는 배열 생성
        //$response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (닉네임 값 저장)
        $response["token"] = $token; // 생성한 토큰을 넣어준다.
        $response["success"] = true; 

        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    
    // 조회 실패
    }else{
        //$response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        echo json_encode($response);
        exit();
    }

?>