<?php 
    include "dbcon.php";
    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 현재 접속유저 고유번호.
    $userNum = (int)$userNum;
    
    // 현재 접속한 유저가 읽지않은 채팅 메시지를 모두 조회하는데 그 채팅 메시지의 room_num가 유저가 속해있는 room_num(채팅방 고유번호)에 포함되어 있어야 한다. 이 조건에 해당하는 모든 채팅 메시지를 조회하여 그 개수를 클라이언트에 넘겨준다.
    $sql = "SELECT * FROM (SELECT * FROM chat_message WHERE user_num != $userNum AND unchecked_count >= 1)P WHERE room_num IN (SELECT room_num FROM chat_room WHERE sales_user_num = $userNum OR purchase_user_num = $userNum)";
    $res = mysqli_query($conn, $sql); 
    $resultNum = mysqli_num_rows($res);

    // 조회 성공
    if($res)
    {
        // 색칠 된 북마크 출력
        $response = array(); // response라는 배열 생성
        $response["uncheckedCount"] = $resultNum; // 조회된 채팅 메시지 총 개수.
        $response["success"] = true; 

        echo json_encode($response);
        exit();
    
    // 조회 실패.
    }
    else{

        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>