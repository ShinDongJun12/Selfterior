<?php

    //ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $roomNum = $_POST["roomNum"]; // 채팅방 고유번호.
    $roomNum = (int)$roomNum;

    $userNum = $_POST["userNum"]; // 유저 고유번호.
    $userNum = (int)$userNum; 

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.

    $chatMessageType = $_POST["chatMessageType"]; // 채팅 메시지 타입.


    // Text 채팅 메시지 DB에 저장.
    // (※ 메시지 안읽은 사용자 수는 1대1채팅이므로 데이터 생성시 자기자신을 뺀 1로 세팅하지만 1대N채팅인 경우 자신을 제외한 채팅방 참여자 수로 저장해야한다.)
    // message_content값을 'mmm'으로 하고 나머지 정보들은 그대로 DB에 저장한다. (message_num가 생긴다.).
    $sql = "INSERT INTO chat_message (room_num, user_num, message_content, regtime, message_type, unchecked_count)";
    $sql.= " VALUES ($roomNum, $userNum, 'mmm', now(), '$chatMessageType', 1)";
    $res = mysqli_query($conn, $sql);

    // ★채팅 메시지 저장 직후 PK 값을 획득한다. (방금 생성된 채팅 메시지 고유번호)
    $get_chat_message_num = mysqli_insert_id($conn);

    // 메시지를 작성한 채팅방 마지막 메시지데이터를 위의 메시지 값으로 수정한다. (채팅 이미지 메시지의 경우 message_content 값을 '사진을 보냈습니다.'으로 저장한다.)
    $sql2 = "UPDATE chat_room SET message_content='사진을 보냈습니다.', regtime=now(), message_type='$chatMessageType' WHERE room_num = $roomNum";
    $res2 = mysqli_query($conn, $sql2);
    

    // DB에 정상적으로 저장되었으면.
    if($res && $res2) {
        
    
        $get_chat_message_num2 = (string)$get_chat_message_num; // 받아온 게시물 고유번호(int) -> (string)으로 변환 후 다시 저장.
        
        // 이미지 채팅 메시지별 이미지 디렉토리 생성.
        $dirBaseName = "chat_images_message";
        $messageDirName = $get_chat_message_num2.'_'.$dirBaseName; // ex) 23_chat_images_message
        $messageUploadDir = "chatMessageImages"; // 채팅 메시지 이미지 저장할 폴더명
        $messageDirPath = "$messageUploadDir/$messageDirName"; // 게시물이 추가될 때 해당 게시물의 이미지들을 저장할 폴더경로.
        
        // umask : 권한을 설정할 때 수동적으로 권한을 주지 않고 파일이나 디렉토리가 생성됨가 동시에 지정된 권한이 주어주도록 함
        // 리눅스 umask 설정값이 0022로 잡혀 있어서 0777로 디렉토리를 생성해도 0755값으로 나온 것이다.
        // 이를 해결하기 위해 umask값의 옵션을 변경해서 생성하니 해결 되었다.
        $oldumask = umask(0);

        // $messageDirPath 경로에 $messageDirName 폴더가 존재하지 않으면. (is_dir가 조금더 빠르다고 한다.)
        if (!is_dir($messageDirPath)) {
            
            mkdir($messageDirPath, 0777, true); // 777권한으로 폴더 생성.

            // 서버에 저장된 사진의 URL 리스트
            $urlList = array();
            // 첨부된 사진이 1장이상 있을 때
            if($cntImage > 0) {
            
                $server = 'http://49.247.148.192/'; // 서버주소.
                $uploadDir = $messageDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_chat_images_message

                // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 urlList에 담는다.
                for($i=0; $i<$cntImage; $i++) { 
                    $tmp_name = $_FILES['image'.$i]["tmp_name"];
                    $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
                    $type = $_FILES['image'.$i]["type"]; // application/octet-stream
                    $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
                    $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
                    $name = $get_chat_message_num2.'_'.$i.'.'.$type; //ex) 채팅메시지고유번호_1.jpg
                    $path = "$messageUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) chatMessageImages/23_chat_images_message/채팅메시지고유번호.jpg
                    move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                    $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 url들의 List
                    
                    // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  urlList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
                }
            }
            
            // 서버에 저장된 사진의 url 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 urlList에 담는다.)
            $urlList = json_encode($urlList); // jsonArray를 문자열로 변환
            
            // DB에 게시물의 message_content, regtime 값을 추가적으로 저장한다.
            $sql2 = "UPDATE chat_message SET message_content = '$urlList', regtime = now() WHERE message_num = $get_chat_message_num";
            $res2 = mysqli_query($conn, $sql2);
            
            if($res2) {

                // 새로 추가된 채팅 이미지 메시지의 모든 정보를 조회하여 클라이언트에 응답으로 넘겨준다.

                $chat_message_data_array = array(); // 리사이클러뷰에 보여줄 한 채팅 이미지 메시지에 대한 모든 데이터를 담을 배열

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
                
                array_push($chat_message_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.

                mysqli_close($conn); // DB 종료.

                echo json_encode($chat_message_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

            } 
            else 
            {
                // 포스트 저장 실패
                // $result = array("result" => "error"); // $result["success"] = false;
                // echo "error";
            }

            umask($oldumask);
        } 
        // 폴더가 존재하면 
        else {
            
            // 바로 해당 폴더에 이미지 파일들 저장.
            // 서버에 저장된 사진의 URL 리스트
            $urlList2 = array();
            // 첨부된 사진이 1장이상 있을 때
            if($cntImage > 0) {
            
                $server = 'http://49.247.148.192/'; // 서버주소.
                $uploadDir = $messageDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_chat_images_message

                // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 urlList2에 담는다.
                for($i=0; $i<$cntImage; $i++) { 
                    $tmp_name = $_FILES['image'.$i]["tmp_name"];
                    $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
                    $type = $_FILES['image'.$i]["type"]; // application/octet-stream
                    $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
                    $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
                    $name = $get_chat_message_num2.'_'.$i.'.'.$type; //ex) 채팅메시지고유번호_1.jpg
                    $path = "$messageUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) chatMessageImages/23_chat_images_message/채팅메시지고유번호.jpg
                    move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                    $urlList2[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 url들의 List
                    
                    // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  urlList2에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
                }
            }
            
            // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
            $urlList2 = json_encode($urlList2); // jsonArray를 문자열로 변환

            // DB에 게시물의 message_content, regtime 값을 추가적으로 저장한다.
            $sql3 = "UPDATE chat_message SET message_content = '$urlList2', regtime = now() WHERE message_num = $get_chat_message_num";
            $res3 = mysqli_query($conn, $sql3);

            if($res3) {
                
                 // 새로 추가된 채팅 이미지 메시지의 모든 정보를 조회하여 클라이언트에 응답으로 넘겨준다.

                 $chat_message_data_array = array(); // 리사이클러뷰에 보여줄 한 채팅 이미지 메시지에 대한 모든 데이터를 담을 배열

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
                 
                 array_push($chat_message_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
 
                 mysqli_close($conn); // DB 종료.
 
                 echo json_encode($chat_message_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

            } else {
                // 포스트 저장 실패
                // $result = array("result" => "error"); // $result["success"] = false;
                // echo "error";
            }

        }

    }
    // DB에 정상적으로 저장되지 않았으면.
    else{
        // 포스트 저장 실패
        // $result = array("result" => "error"); // $result["success"] = false;
        // echo "error";
    }
    
    mysqli_close($conn); // DB종료.
?>