<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 회원이 입력한 닉네임값을 받아온다.
    $userNickName = $_POST["userNickName"]; // 사용자가 입력한 닉네임
    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$userNum;

    $sql = "SELECT * FROM members WHERE user_nickname='$userNickName'";
    $result1 = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result1);
    
    //print_r($member);
    
    // 중복된 닉네임이 없으면 (닉네임 사용가능)
    if($rs_num == 0){

        // 닉네임 변경
        $result2 = mysqli_query($conn, "UPDATE members SET user_nickname='$userNickName' WHERE user_num=$resultNum");

        // DB에 성공적으로 넣어졌으면
        if($result2){

            // 변경 후 변경된 회원 정보 모두 조회.
            $sql2 = "SELECT * FROM members WHERE user_num=$resultNum";
            $result3 = mysqli_query($conn, $sql2);

            $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
            $response = mysqli_fetch_array($result3); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            $response["success"] = true; // success라는 이름을 가진 변수는 기본값이 true였다가 유저가 입력한 닉네임값이 이미 존재하면 아래에서 false값을 가지게 된다.
            $response["samecheck"] = "nothing"; // 닉네임 중복이 아닐 때.
            echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
            exit();
        }
        else{
            $response = array(); // response라는 배열 생성
            //$response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            $response["success"] = false; 
            $response["samecheck"] = "same"; // 이상태는 그냥 중복이 아닌데 위에 커리문 실행 안된 오류일 때 다이얼로그창 안 떠야 해서 same으로 처리.
            echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
            exit();
        }
    
    // 중복된 닉네임이 있으면 (닉네임 사용 불가능)
    }else{

        // 사용자 고유번호로 닉네임 조회해서 같은지 판다.
        $sql3 = "SELECT * FROM members WHERE user_num=$resultNum";
        $result4 = mysqli_query($conn, $sql3);

        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($result4); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = false; 

        // 자신의 닉네임을 그대로 입력했을 때.
        if($userNickName == $response['user_nickname']){
            $response["samecheck"] = "same";
        }
        // 그냥 닉네임 중복인 상황
        else{
            $response["samecheck"] = "notsame";
        }

        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    }
?>