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

    $resultList = array(); // 결과값을 담을 배열.

    // 유저 토큰 암호화 처리
    // $userToken = md5($userToken);

    // $userPass = null; // 소셜로그인으로 가입시 비밀번호 null
    // $userRegtime = now()
    
    if($userPlatformType == "kakao"){
        // 소셜 로그인 가입자는 고유식별자를 user_unique_identifier에 암호화 처리해서 저장한다.
        $userIdentifier = md5($userIdentifier);
    }

    // // 사용자 프로필 이미지 서버에 업로드
    // $server = 'http://49.247.148.192/'; // 서버주소.
    // $uploadDir = 'userProfileImages'; // 서버에서 사용자 프로필 이미지를 저장할 디렉토리 이름.

    // // 사용자가 변경한 프로필 이미지를 서버에 업로드 한 후 업로드된 이미지 경로를 프로필 이미지 컬럼에 저장한다.
    // // 사용자가 변경한 이미지에 대한 정보를 각가의 변수에 저장한다.
    // $tmp_name = $_FILES['profileImage']["tmp_name"];
    // $oldName = $_FILES['profileImage']["name"]; //ex) example.jpg (houseTourImage1.jpg)
    // $type = $_FILES['profileImage']["type"]; // application/octet-stream
    // // ***********************************************************************************************************************************
    // $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    // $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    // $name = $userNum.'_'.'profileImage'.'.'.$type; //ex) 유저고유번호_profileImage.jpg
    // $path = "$uploadDir/$name"; // 서버에 이미지를 저장할 경로.
    // move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    // $profile_image = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지의 uri값을 최종적으로 $profile_image 변수에 저장한다.
    // // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  profile_image 담아서 그것을 DB의 사용자 프로필 이미지 컬럼에 저장한다.


    // user_pass 없음
    $result=mysqli_query($conn, "INSERT INTO members(user_email,user_pass,user_unique_identifier,user_nickname,profile_image,user_regtime,platform_type,user_token) VALUES ('$userEmail','','$userIdentifier','$userNickname','$profileImage',now(),'$userPlatformType','$userToken')");

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

            // // echo "조회는 성공";
            // $response = array(); // response라는 배열 생성
            // $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            // $response["success"] = true; 

            //$response = array ('success'=>true,'user_num'=>$response[user_num],'user_email'=>$response[user_email],'user_pass'=>$response[user_pass],'user_nickname'=>$response[user_nickname],'platform_type'=>$response[platform_type],'user_regtime'=>$response[user_regtime],'profile_image'=>$response[profile_image],'user_unique_identifier'=>$response[user_unique_identifier]);

            //echo json_encode($response);
            //echo json_encode($response, JSON_UNESCAPED_UNICODE);
            //exit();
    
        // 조회 실패
        }else{

            $result = array("result" => "error"); // $result["success"] = false;
            echo "error";

            exit();
            // $response = array(); // response라는 배열 생성
            // $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            // echo json_encode($response);
            // exit();
        }
    }else {
        // echo "그냥실패";
        // 실패
        // $response = array(); // response라는 배열 생성
        // $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        // echo json_encode($response);
        // exit();
        $result = array("result" => "error"); // $result["success"] = false;
            echo "error";

            exit();
    }

?>