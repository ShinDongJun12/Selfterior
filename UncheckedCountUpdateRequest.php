<?php 
    //ini_set('display_errors', true);
    include "dbcon.php";
    mysqli_query($conn,'SET NAMES utf8'); 

    $roomNum = $_POST["roomNum"]; // 채팅방 고유번호.
    $roomNum = (int)$roomNum;

    $userNum = $_POST["userNum"]; // 채팅방에 입장한 유저 고유번호.
    $userNum = (int)$userNum;
    

    // 채팅 메시지 안읽은 사람수 -1씩 감소 시켜준다.
    $sql = "UPDATE chat_message SET unchecked_count = unchecked_count-1 WHERE room_num = $roomNum AND unchecked_count >= 1 AND user_num != $userNum";
    $res = mysqli_query($conn, $sql); 

    if($res)
    {
        // 색칠 된 북마크 출력
        $response = array(); // response라는 배열 생성
        //$response = mysqli_fetch_array($ret); 
        $response["success"] = true; 

        echo json_encode($response);
        exit();
    
    // 조회된 채팅 메시지가 없는경우
    }
    else{

        // 색칠 안 된 북마크 출력 
        $response = array(); // response라는 배열 생성
        // bookmark_check라는 이름으로 값이 아예 존재하지 않을테니 false값만 배열에 넣어서 넘겨주고 클라이언트 단에서 판단 후 북마크 버튼 출력한다.
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>