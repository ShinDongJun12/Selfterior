<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $room_num = $_GET["room_num"]; // 채팅방 고유 번호
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $chat_message_data_array = array(); // 리사이클러뷰에 보여줄 채팅 메시지 한개에 대한 모든 데이터를 담을 배열

    // $page 행 부터 $limit만큼 데이터 조회해서 가져오기. ('regtime'로 내림차순 정렬(desc) -> 가장 최근에 작성한 메시지가 먼저 조회. , asc : 오름차순))
    $sql = "SELECT * FROM chat_message WHERE room_num = $room_num ORDER BY regtime desc limit $limit offset $page";
    $res = mysqli_query($conn, $sql);
    
    // 채팅 메시지 갯수만큼 while문 돌면서 데이터 조회하고 저장.
	while($chat_message = $res->fetch_assoc()) { 
        
        // ***** 채팅 메시지 작성자 프로필 이미지 Uri 조회 *****
        // 저장한 값들 중 메시지 작성자 고유번호를 통해 사용자의 프로필 이미지 Uri 데이터를 조회.
		$sql_user = "select profile_image from members where user_num = $chat_message[user_num]";
        $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
        $user_info = mysqli_fetch_assoc($res_user);
        
        // ***** 채팅 메시지 데이터 최종 합치기 *****
        // 채팅 메시지 데이터 + 채팅 메시지 작성자 정보(프로필 이미지)를 $data에 다시 저장.
        $data = [
            'message_num' => $chat_message['message_num'], // 채팅 메시지 고유번호
            'room_num' => $chat_message['room_num'], // 채팅방 고유번호
            'user_num' => $chat_message['user_num'], // 작성자 고유번호
            'message_content' => $chat_message['message_content'], // 메시지 내용
            'regtime' => $chat_message['regtime'], // 메시지 등록시간
            'message_type' => $chat_message['message_type'], // 메시지 타입
            'unchecked_count' => $chat_message['unchecked_count'], // 채팅 메시지 안읽은 사람 수.
            'user_profile_image' => $user_info['profile_image'] // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
        ]; 
        array_push($chat_message_data_array, $data); // 리사이클러뷰에 보여줄 채팅 메시지에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
	}

	mysqli_close($conn); // DB 종료.

    echo json_encode($chat_message_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
?>