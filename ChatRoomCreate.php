<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $salesUserNum = $_POST["salesUserNum"]; // 판매자 고유번호.
    $purchaseUserNum = $_POST["purchaseUserNum"]; // 구매자 고유번호.
    $postNum = $_POST["postNum"]; // 중고거래 게시물 고유번호.

    $chat_room_data_array = array(); // 채팅방에 대한 모든 데이터를 담을 배열

    
    // 채팅방 DB에 저장.
    $sql = "INSERT INTO chat_room (sales_user_num, purchase_user_num, post_num)";
    $sql.= " VALUES ($salesUserNum, $purchaseUserNum, $postNum)";
    $res = mysqli_query($conn, $sql);
    
    // 채팅방 생성 성공.
    if($res){

        // 채팅방 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        // while($post = $res->fetch_assoc()) { 

            // ★채팅방 저장 직후 PK 값을 획득한다. (방금 생성된 채팅방 고유번호)
            $get_chat_room_num = mysqli_insert_id($conn);
        
            // 방금 저장된 채팅 메시지 데이터 조회.
            $sql = "SELECT * FROM chat_room WHERE post_num = $get_chat_room_num";
            $res = mysqli_query($conn, $sql);
            $chat_room = mysqli_fetch_assoc($res); // 조회한 데이터를 $chat_message에 모두 저장.

            // ***** chat_room테이블의 판매자 고유번호(sales_user_num)데이터를 이용해 해당 유저의 프로필 이미지 URL과 닉네임 데이터를 조회한다. *****
            $sql_user = "SELECT profile_image, user_nickname FROM members WHERE user_num = $chat_room[sales_user_num]";
            $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $user_info = mysqli_fetch_assoc($res_user);

            // ***** chat_room테이블의 채팅방 고유번호(room_num)데이터를 이용해 해당 채팅방의 가장 마지막 메시지의 '채팅 메시지 내용'과'채팅 메시지 등록날짜' 데이터를 조회한다. *****
            $sql_message = "SELECT message_content, regtime FROM chat_message WHERE room_num = $chat_room[room_num] ORDER BY regtime DESC LIMIT 1";
            // $sql_message = "SELECT MAX(regtime) FROM chat_message WHERE room_num = $chat_room[room_num]";
            // $sql_message = "SELECT * FROM (SELECT * FROM chat_message ORDER BY regtime DESC) WHERE room_num = $chat_room[room_num] AND ROWNUM = 1";
            $res_message = mysqli_query($conn, $sql_message); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $chat_message_info = mysqli_fetch_assoc($res_message);

            
            // ***** 채팅방 데이터 최종 합치기 *****
            // 채팅방 데이터 + 판매자 정보(닉네임,프로필 이미지) + 채팅방 메시지중 가장 마지막 메시지 데이터(채팅 메시지 내용, 등록날짜)를 $data에 다시 저장.
            $data = [
                'room_num' => $chat_room['room_num'], // 채팅방 고유번호.
                'sales_user_num' => $chat_room['sales_user_num'], // 판매자 고유번호.
                'purchase_user_num' => $chat_room['purchase_user_num'], // 구매자 고유번호.
                'post_num' => $chat_room['post_num'], // 중고거래 게시물 고유번호.
                'user_nickname' => $user_info['user_nickname'], // 채팅상대 유저 닉네임
                'user_profile_image' => $user_info['profile_image'], // 채팅상대 유저 프로필 이미지.
                'message_content' => $chat_message_info['message_content'], // 채팅방 제일 마지막 메시지 내용.
                'regtime' => $chat_message_info['regtime'] //  채팅방 제일 마지막 메시지 등록날짜.
            ]; 
            array_push($chat_room_data_array, $data); // 리사이클러뷰에 보여줄 채팅방에 대한 모든 정보를 담은 $data를 $chat_room_data_array배열에 푸쉬.
        // }

        mysqli_close($conn); // DB 종료.

        echo json_encode($chat_room_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    }
    // 채팅방 저장 실패.
    else{

        mysqli_close($conn); // DB 종료.

        //     //$result = array("result" => "error"); // $result["success"] = false;

        //     $response = array(); // response라는 배열 생성
        //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        //     echo json_encode($response);

        exit();
    }
?>