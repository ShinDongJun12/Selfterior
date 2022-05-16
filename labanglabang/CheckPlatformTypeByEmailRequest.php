<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 회원이 입력한 이메일 값을 받아온다.
    $userEmail = $_POST["userEmail"];

    $sql = "SELECT * FROM members WHERE user_email='$userEmail'";
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);

    
    // 가입된 이메일이 있으면 (이메일 사용 불가능)
    if($rs_num > 0){

        $response = array(); // response라는 배열 생성

        // ---------------------------------------------------------------------------------------
        // while($row = mysqli_fetch_assoc($result))
        // {
        //     array_push($response, array(
        //         'platform_type' => $row['platform_type']
        //     ));
        // }
        // ex) -> {"0":{"platform_type":"kakao"},"1":{"platform_type":"email"},"success":false}
        // ---------------------------------------------------------------------------------------

        while($row = mysqli_fetch_assoc($result))
        {

            array_push($response, $row['platform_type']);
        }
        // ex) -> {"0":"kakao","1":"email","count":2,"success":false}
        
        $response["count"] = $rs_num; // 존재하는 계정 갯수
        $response["success"] = false; 
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    
    // 가입된 이메일이 없으면 (이메일 사용가능)
    }
    else{

        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response["success"] = true; // success라는 이름을 가진 변수는 기본값이 true였다가 유저가 입력한 닉네임값이 이미 존재하면 아래에서 false값을 가지게 된다.
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    }
?>