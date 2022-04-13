<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $roomNum = $_POST["roomNum"]; // 채팅방 고유번호.
    $roomNum = (int)$roomNum;
    $userNum = $_POST["userNum"]; // 현재 접속 유저 고유번호.
    $userNum = (int)$userNum;
    
    // 현재 접속 유저가 채팅방에 들어가 있는지 조회한다.
    $sql = "SELECT * FROM chat_room WHERE room_num = $roomNum AND (sales_user_num = $userNum OR purchase_user_num = $userNum)";
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);

    // 채팅 서버로 부터 받은 메시지가 작성된 채팅방에 유저가 포함된 경우.(채팅 메시지를 받아야하는 유저인 경우.)
    if($rs_num > 0)
    {
        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; // success라는 이름을 가진 변수는 기본값이 true였다가 유저가 입력한 닉네임값이 이미 존재하면 아래에서 false값을 가지게 된다.
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        
        exit();
    }
    else
    {
        $response = array(); // response라는 배열 생성
        $response["success"] = false; 
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        
        exit();
    }
?>