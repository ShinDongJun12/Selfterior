<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $room_num = $_GET["room_num"]; // 채팅방 고유번호
    $user_num = $_GET["user_num"]; // 접속 유저 고유번호.

    $chat_room_data_array = array(); // 리사이클러뷰에 보여줄 한 채팅방에대한 모든 데이터를 담을 배열

    
    // 해당 유저가 판매자 또는 구매자로 들어가있는 모든 채팅방인 경우 채팅방 데이터 조회.
    $sql = "SELECT * FROM chat_room WHERE room_num = $room_num AND (sales_user_num = $user_num OR purchase_user_num = $user_num)";
    $res = mysqli_query($conn, $sql);
    $chat_room = mysqli_fetch_assoc($res);
    $resultNum = mysqli_num_rows($res); // 검색결과 게시물 총 개수.
    
    // 채팅방이 한 개이상 존재하면.
    if($resultNum > 0){
    //if($res){

        // 채팅방 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        //while($chat_room = $res->fetch_assoc()) { 

            // 현재 접속한 유저의 고유번호가 판매자 고유번호랑 같을 경우.
            if($user_num == $chat_room["sales_user_num"])
            {
                // ***** 구매자 고유번호 데이터를 이용해서 해당 유저의 데이터를 조회한다.  *****
                // (유저 닉네임, 유저 프로필 이미지)
                $sql_user = "SELECT * FROM members WHERE user_num = $chat_room[purchase_user_num]";
            }
            //else if($user_num == $chat_room["purchase_user_num"])
            // 현재 접속한 유저의 고유번호가 구매자 고유번호랑 같을 경우.
            else
            {
                // ***** 판매자 고유번호 데이터를 이용해서 해당 유저의 데이터를 조회한다.  *****
                // (유저 닉네임, 유저 프로필 이미지)
                $sql_user = "SELECT * FROM members WHERE user_num = $chat_room[sales_user_num]";
            }
            $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $user_info = mysqli_fetch_assoc($res_user);

            // ***** 채팅방 게시물 고유 번호로 판매자 주소 데이터를 조회한다. *****
            $sql_post = "SELECT sale_address FROM used_transaction_post WHERE post_num = $chat_room[post_num]";
            $res_post = mysqli_query($conn, $sql_post); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $post_info = mysqli_fetch_assoc($res_post);


        
            // ***** 채팅방에 안읽은 메시지 개수 조회.(!!!이부분 1:N 채팅으로 개발하게 된다면 수정해야함.) *****
            $sql_message = "SELECT * FROM chat_message WHERE room_num = $chat_room[room_num] AND user_num != $user_num AND unchecked_count >= 1";
            $res_message = mysqli_query($conn, $sql_message);
            $messaget_info = mysqli_num_rows($res_message); // 검색결과 게시물 총 개수.

            
            $data = [
                'room_num' => $chat_room['room_num'], // 채팅방 고유번호.
                'sales_user_num' => $chat_room['sales_user_num'], // 판매자 고유번호.
                'purchase_user_num' => $chat_room['purchase_user_num'], // 구매자 고유번호.
                'post_num' => $chat_room['post_num'], // 중고거래 게시물 고유번호.
                'message_content' => $chat_room['message_content'], // 채팅방 마지막 채팅 메시지 내용.
                'regtime' => $chat_room['regtime'], // 채팅방 마지막 채팅 메시지 등록날짜.
                'message_type' => $chat_room['message_type'], // 채팅방 마지막 채팅 메시지 타입.
                'user_nickname' => $user_info['user_nickname'], // 채팅 상대 유저 닉네임
                'user_profile_image' => $user_info['profile_image'], // 채팅 상대 유저 프로필 이미지 Uri
                'sale_address' => $post_info['sale_address'], // 게시물 판매자 주소.
                'unchecked_count' => $messaget_info // 채팅방 안읽은 메시지 개수.
            ]; 
            array_push($chat_room_data_array, $data); // 리사이클러뷰에 보여줄 채팅방에대한 모든 정보를 담은 $data를 $chat_room_data_array 푸쉬.
        // }

        mysqli_close($conn); // DB 종료.

        echo json_encode($chat_room_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    }
    // 채팅방이 존재하지 않으면
    else{

        mysqli_close($conn); // DB 종료.

        //     //$result = array("result" => "error"); // $result["success"] = false;

        //     $response = array(); // response라는 배열 생성
        //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        //     echo json_encode($response);

        exit();
    }
?>