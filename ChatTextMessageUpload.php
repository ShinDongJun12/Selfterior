<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $roomNum = $_GET["roomNum"]; // 채팅방 고유번호.
    $userNum = $_GET["userNum"]; // 채팅 메시지 작성자 고유번호.
    $chatMessage = $_GET["chatMessage"]; // 채팅 메시지 내용.
    $chatMessageType = $_GET["chatMessageType"]; // 체팅 메시지 타입.

    $chat_message_data_array = array(); // 채팅 메시지에 대한 모든 데이터를 담을 배열

    
    // Text 채팅 메시지 DB에 저장.
    // (※ 메시지 안읽은 사용자 수는 1대1채팅이므로 데이터 생성시 자기자신을 뺀 1로 세팅하지만 1대N채팅인 경우 자신을 제외한 채팅방 참여자 수로 저장해야한다.)
    $sql = "INSERT INTO chat_message (room_num, user_num, message_content, regtime, message_type, unchecked_count)";
    $sql.= " VALUES ($roomNum, $userNum, '$chatMessage', now(), '$chatMessageType', 1)";
    $res = mysqli_query($conn, $sql);

    // ★채팅 메시지 저장 직후 PK 값을 획득한다. (방금 생성된 채팅 메시지 고유번호)
    $get_chat_message_num = mysqli_insert_id($conn);
    
    // 메시지를 작성한 채팅방 마지막 메시지데이터를 위의 메시지 값으로 수정한다.
    $sql2 = "UPDATE chat_room SET message_content='$chatMessage', regtime=now(), message_type='$chatMessageType'  WHERE room_num = $roomNum";
    $res2 = mysqli_query($conn, $sql2);
    
    // 채팅 메시지 저장 및 채팅방 최신 메시지 데이터 수정 성공.
    if($res && $res2){

        // 채팅 메시지 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        // while($post = $res->fetch_assoc()) { 

            // // ★채팅 메시지 저장 직후 PK 값을 획득한다. (방금 생성된 채팅 메시지 고유번호)
            // $get_chat_message_num = mysqli_insert_id($conn);
        
            // 방금 저장된 채팅 메시지 데이터 조회.
            $sql = "SELECT * FROM chat_message WHERE message_num = $get_chat_message_num";
            $res = mysqli_query($conn, $sql);
            $chat_message = mysqli_fetch_assoc($res); // 조회한 데이터를 $chat_message에 모두 저장.

            // ***** chat_message테이블의 채팅 메시지 작성자 고유번호(user_num)데이터를 이용해 해당 유저의 프로필 이미지 URL 데이터를 조회한다. *****
            $sql_user = "SELECT profile_image FROM members WHERE user_num = $chat_message[user_num]";
            $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $user_info = mysqli_fetch_assoc($res_user);

            // ***** 채팅 메시지 데이터 최종 합치기 *****
            // 채팅 메시지 데이터 + 채팅 메시지 작성자 정보(프로필 이미지)를 $data에 다시 저장.
            $data = [
                // 왼쪽은 Response에서 받는 변수명, 오른쪽은 DB에 저장된 컬럼(변수)명과 일치해야한다.
                'message_num' => $chat_message['message_num'], // 채팅 메시지 고유번호.
                'room_num' => $chat_message['room_num'], // 채팅 메시지가 입력된 채팅방 고유번호.
                'user_num' => $chat_message['user_num'], // 채팅 메시지 작성자 고유번호.
                'message_content' => $chat_message['message_content'], // 채팅 메시지 내용.
                'regtime' => $chat_message['regtime'], // 채팅 메시지 등록날짜.
                'message_type' => $chat_message['message_type'], // 채팅 메시지 타입. 
                'unchecked_count' => $chat_message['unchecked_count'], // 채팅 메시지 안읽은 사람 수.
                'user_profile_image' => $user_info['profile_image'] // 채팅 메시지 작성자 프로필 이미지 Uri (추가한 유저정보)
            ]; 
            array_push($chat_message_data_array, $data); // 리사이클러뷰에 보여줄 채팅 메시지에 대한 모든 정보를 담은 $data를 $chat_message_data_array배열에 푸쉬.
        // }

        mysqli_close($conn); // DB 종료.

        echo json_encode($chat_message_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    }
    // 채팅 메시지 저장 실패.
    else{

        mysqli_close($conn); // DB 종료.

        //     //$result = array("result" => "error"); // $result["success"] = false;

        //     $response = array(); // response라는 배열 생성
        //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        //     echo json_encode($response);

        exit();
    }
?>