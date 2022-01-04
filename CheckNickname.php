<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 회원이 입력한 닉네임값을 받아온다.
    $userNickName = $_POST["userNickName"];
    
    // 입력받은 닉네임 길이가 2~10자 사이면 중복검사를 위해 DB조회 실시. (mb_strlen() : 문자열의 길이를 숫차로 출력하는 함수.)
    if(mb_strlen($userNickName, "UTF-8") >= 2 && mb_strlen($userNickName, "UTF-8") <= 10){

        $sql = "SELECT * FROM members WHERE user_nickname='$userNickName'";
        // echo  $sql;
        $result = mysqli_query($conn, $sql);
        $rs_num = mysqli_num_rows($result);
    
        //print_r($member);
    
        // 중복된 닉네임이 없으면 (닉네임 사용가능)
        if($rs_num==0){
            $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
            $response["success"] = true; // success라는 이름을 가진 변수는 기본값이 true였다가 유저가 입력한 닉네임값이 이미 존재하면 아래에서 false값을 가지게 된다.
            echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
            exit();
    
        // 중복된 닉네임이 있으면 (닉네임 사용 불가능)
        }else{
            $response = array(); // response라는 배열 생성
            $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            $response["success"] = false; 
            echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
            exit();
        }

    }else{
        // 위의 조건에 맞지 않으면 중복검사를 위한 DB조회 없이 바로 success 값을 false로 변경
        $response = array();
        $response["success"] = false;  
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    }
?>