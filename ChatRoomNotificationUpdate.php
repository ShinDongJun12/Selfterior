<?php
    //ini_set('display_errors', true);
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.

    $roomNum = $_POST["chatRoomNum"]; // 채팅방 고유번호.
    $roomNum = (int)$roomNum;

    $userNum = $_POST["chatuserNum"]; // 현재 접속 유저 고유번호.
    $userNum = (int)$userNum;
    

    // 현재 접속 유저가 채팅방에 들어가 있는지 조회한다.
    $sql = "SELECT turn_off_notification FROM chat_room WHERE room_num = $roomNum AND (sales_user_num = $userNum OR purchase_user_num = $userNum)";
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);


    // 채팅 서버로 부터 받은 메시지가 작성된 채팅방에 유저가 포함된 경우.(채팅 메시지를 받아야하는 유저인 경우.)
    if($rs_num > 0)
    {
        $chat_room = mysqli_fetch_assoc($result); // 조회한 데이터를 $chat_message에 모두 저장.
        $room_notification = json_decode($chat_room['turn_off_notification'], true); // array형태로 초기화

        // * array_search() : 주어진 값으로 배열을 검색하여 성공시 해당하는 키를 반환하고 찾지 못하면 false를 반환하는 함수.
        // ※주의 : 0번째 배열에 값이 있는 경우, 위치 0이 반환되어 if 구문에서 false와 같은 것으로 인식될 수 있으므로 주의해야한다.
        //          따라서 비교연산자 '=='가 아닌 '==='를 사용해야한다. 
        if (($index = array_search($userNum, $room_notification)) !== false) {

            unset($room_notification[$index]); // 제거 -> 나의 경우에는 인덱스값
        }
        else
        {
            // 배열에 유저 고유번호를 추가한다. (배열에 값 추가할떄 .add()-> X 에러남)
            $room_notification[] = $userNum;  
        }

        $room_notification = json_encode($room_notification); // jsonArray를 문자열로 변환

        // 채팅방 알림 off 유저 목록 컬럼값을 수정한다. ($room_notification배열을 String값으로 turn_off_notification 컬럽에 저장한다.)
        $sql = "UPDATE chat_room SET turn_off_notification = '$room_notification' WHERE room_num = $roomNum";
        $res = mysqli_query($conn, $sql);
        
        // 수정 성공.
        if($res) 
        {
            $response = array(); // response라는 배열 생성
            $response["success"] = true; // 수정 성공여부

            echo json_encode($response);
            exit();
        }
        // 수정 실패.
        else
        {
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // 수정 성공여부

            echo json_encode($response);
            exit();
        }

    }
    else
    {
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // 수정 성공여부

        echo json_encode($response);
        exit();
    }
?>