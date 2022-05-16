<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    $userIdentifier = $_POST["userIdentifier"]; // 회원 고유 식별자
    $userNickname = $_POST["userNickName"]; // 닉네임
    $userEmail = $_POST["userEmail"];  // 선택 동의라 있으면 받고 없으면 안받는다.
    $userPlatformType = $_POST["userPlatformType"]; // 플랫폼 타입
    $userToken = $_POST["userToken"]; // 유저 토큰
    $profileImage = $_POST["profileImage"]; // 유저 프로필 이미지
    $userIntroduction = null; // 유저 소개글

    $resultList = array(); // 결과값을 담을 배열.


    // 소셜 로그인 가입자는 고유식별자를 user_unique_identifier에 암호화 처리해서 저장한다.
    $userIdentifier = md5($userIdentifier);

// 기존 코드 -> 왜 카카오만 이렇게 했지??? 어차피 소셜로그인으로 회원가입하면 다 고유번호 받아올테고 그것들 다 암호화해줘야 할텐데...    
    // if($userPlatformType == "kakao"){
    //     // 소셜 로그인 가입자는 고유식별자를 user_unique_identifier에 암호화 처리해서 저장한다.
    //     $userIdentifier = md5($userIdentifier);
    // }

    
    // user_pass 없음 따라서 ''값으로 생성하고 유저가 비밀번호 리셋하면 그때 생김. 어차피 소셜로그인은 고유 식별자로 체크함.
    $result=mysqli_query($conn, "INSERT INTO members(user_email, user_pass, user_unique_identifier, user_nickname, profile_image, user_regtime, platform_type, user_token, recent_broadcast_date, user_introduction) VALUES ('$userEmail','','$userIdentifier','$userNickname','$profileImage',now(),'$userPlatformType','$userToken',now(),'$userIntroduction')");

    // DB에 성공적으로 넣어졌으면
    if($result){

        // echo "DB에는 저장";
        // 현재 가입된 회원정보 바로 조회. (닉네임은 중복X 이므로)
        //$sql = "SELECT * FROM members WHERE user_nickname='".$_POST['userNickname']."'"; 
        $sql = "SELECT * FROM members WHERE user_nickname='$userNickname'"; 
        $ret = mysqli_query($conn, $sql); 
        $exist = mysqli_num_rows($ret); 

        if($exist > 0){

            // 변경 후 변경된 회원 정보 모두 조회.
            $sql2 = "SELECT user_num FROM members WHERE user_nickname='$userNickname'"; 
            $result3 = mysqli_query($conn, $sql2);
            $row = mysqli_fetch_assoc($result3); // 조회값 가져오기.
        
            // 사용자 프로필 이미지 조회 성공
            if($result3) {
                // 조회한 값 저장
                
                $responseUserNum = $row['user_num']; // 사용자 프로필 이미지 저장된 값 $profileImg에 저장.
            
                // 사용자 프로필 이미지 경로를 value로 넘겨준다.
                $result = array("result" => "success", "value" => $responseUserNum); //$result["success"] = true;  value는 클라이언트(안드로이드 자바) 에서 response.body() 출력했을때 보여줄 값인듯
                echo $responseUserNum;

                exit();
        
            } else {
                // 조회 실패
                $result = array("result" => "error"); // $result["success"] = false;
                echo "error";
                exit();
            }
    
        // 조회 실패
        }else{

            $result = array("result" => "error"); // $result["success"] = false;
            echo "error";

            exit();
        }
    }else {

        $result = array("result" => "error"); // $result["success"] = false;
            echo "error";

            exit();
    }

?>