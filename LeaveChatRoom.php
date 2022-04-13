<?php
    //ini_set('display_errors', true);
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.

    $roomNum = $_POST["chatRoomNum"]; // 채팅방 고유번호.
    $roomNum = (int)$roomNum;

    $userNum = $_POST["chatuserNum"]; // 현재 접속 유저 고유번호.
    $userNum = (int)$userNum;
    

    // 현재 접속 유저가 채팅방에 들어가 있는지 조회한다.
    $sql = "SELECT sales_user_num, purchase_user_num FROM chat_room WHERE room_num = $roomNum AND (sales_user_num = $userNum OR purchase_user_num = $userNum)";
    $result = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result);

    
    // 채팅방이 존재하면
    if($rs_num > 0)
    {
        $chat_room = mysqli_fetch_assoc($result); // 조회한 데이터를 $chat_message에 모두 저장.


        // 채팅방을 나간 유저가 판매자인 경우
        if($chat_room['sales_user_num'] == $userNum)
        {
            // 채팅방에 아무도 남아있지 않은 경우
            if($chat_room['purchase_user_num'] == 0)
            {
                // // (1). 채팅방 삭제.
                // $sql = "DELETE FROM chat_room WHERE room_num = $roomNum"; 
                // $ret = mysqli_query($conn, $sql); 

                // // 채팅방 삭제 성공.
                // if($ret)
                // {
                    // (2). 채팅방에 속해있는 채팅 이미지 메시지의 모든 이미지 파일 제거.
                    // 특정 채팅방 채팅 메시지 이면서 채팅 메시지 타입이 이미지인 채팅 메시지의 메시지고유번호를 조회한다.
                    $sql = "SELECT message_num FROM chat_message WHERE room_num = $roomNum AND message_type = '이미지'";
                    $res = mysqli_query($conn, $sql);

                    // 조회된 갯수만큼 while문 돌면서 데이터 조회하고 저장.
                    while($chat_message = $res->fetch_assoc()) { 

                        // 채팅 이미지 메시지의 이미지들이 담겨있는 폴더명
                        $messageImageDirName = $chat_message['message_num'].'_chat_images_message';
                
                        $delete_path = "chatMessageImages/$messageImageDirName";

                        // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                        // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                        // 디렉토리 삭제 함수. 
                        function rmdir_ok($dir) {
                            $dirs = dir($dir);
                            while(false !== ($entry = $dirs->read())) {
                                if(($entry != '.') && ($entry != '..')) {
                                    if(is_dir($dir.'/'.$entry)) {
                                        rmdir_ok($dir.'/'.$entry);
                                    } else {
                                        @unlink($dir.'/'.$entry);
                                    }
                                }
                            }
                            $dirs->close();
                            @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                        }
                    
                        rmdir_ok($delete_path); // 폴더 삭제하기.
                        
                    }


                    // (3). 해당 채팅방에 속해있는 모든 채팅 메시지 삭제.
                    $sql = "DELETE FROM chat_message WHERE room_num = $roomNum"; 
                    $ret = mysqli_query($conn, $sql); 

                    // 채팅방 나가기 처리 성공.
                    if($ret)
                    {

                        // (1). 채팅방 삭제.
                        $sql = "DELETE FROM chat_room WHERE room_num = $roomNum"; 
                        $ret = mysqli_query($conn, $sql); 

                        // 채팅방 삭제 성공.
                        if($ret)
                        {
                            
                            $response = array(); // response라는 배열 생성
                            $response["success"] = true; // 수정 성공여부

                            echo json_encode($response);
                            exit();
                        }
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
                // }
                // else
                // {
                //     $response = array(); // response라는 배열 생성
                //     $response["success"] = false; // 수정 성공여부

                //     echo json_encode($response);
                //     exit();
                // }
                

            }
            // 채팅방에 유저가 남아있는 경우
            else
            {
                // 나간 유저 고유번호만 0값으로 수정
                $sql = "UPDATE chat_room SET sales_user_num = 0 WHERE room_num = $roomNum";
                $result = mysqli_query($conn, $sql);


                // 채팅방 나가기 처리 성공.
                if($result)
                {
                    $response = array(); // response라는 배열 생성
                    $response["success"] = true; // 수정 성공여부

                    echo json_encode($response);
                    exit();
                }
            }

        }
        // 채팅방을 나간 유저가 구매자인 경우
        else
        {
            // 채팅방에 아무도 남아있지 않은 경우
            if($chat_room['sales_user_num'] == 0)
            {
                //  // (1). 채팅방 삭제.
                //  $sql = "DELETE FROM chat_room WHERE room_num = $roomNum"; 
                //  $ret = mysqli_query($conn, $sql); 
 
                //  // 채팅방 삭제 성공.
                //  if($ret)
                //  {
                     // (2). 채팅방에 속해있는 채팅 이미지 메시지의 모든 이미지 파일 제거.
                     // 특정 채팅방 채팅 메시지 이면서 채팅 메시지 타입이 이미지인 채팅 메시지의 메시지고유번호를 조회한다.
                     $sql = "SELECT message_num FROM chat_message WHERE room_num = $roomNum AND message_type = '이미지'";
                     $res = mysqli_query($conn, $sql);
 
                     // 조회된 갯수만큼 while문 돌면서 데이터 조회하고 저장.
                     while($chat_message = $res->fetch_assoc()) { 
 
                         // 채팅 이미지 메시지의 이미지들이 담겨있는 폴더명
                         $messageImageDirName = $chat_message['message_num'].'_chat_images_message';
                 
                         $delete_path = "chatMessageImages/$messageImageDirName";
 
                         // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                         // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                         // 디렉토리 삭제 함수. 
                         function rmdir_ok($dir) {
                             $dirs = dir($dir);
                             while(false !== ($entry = $dirs->read())) {
                                 if(($entry != '.') && ($entry != '..')) {
                                     if(is_dir($dir.'/'.$entry)) {
                                         rmdir_ok($dir.'/'.$entry);
                                     } else {
                                         @unlink($dir.'/'.$entry);
                                     }
                                 }
                             }
                             $dirs->close();
                             @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                         }
                     
                         rmdir_ok($delete_path); // 폴더 삭제하기.
                         
                     }
 
 
                     // (3). 해당 채팅방에 속해있는 모든 채팅 메시지 삭제.
                     $sql = "DELETE FROM chat_message WHERE room_num = $roomNum"; 
                     $ret = mysqli_query($conn, $sql); 
 
                     // 채팅방 나가기 처리 성공.
                     if($ret)
                     {
                        // (1). 채팅방 삭제.
                        $sql = "DELETE FROM chat_room WHERE room_num = $roomNum"; 
                        $ret = mysqli_query($conn, $sql); 

                        // 채팅방 삭제 성공.
                        if($ret)
                        {
                            
                            $response = array(); // response라는 배열 생성
                            $response["success"] = true; // 수정 성공여부

                            echo json_encode($response);
                            exit();
                        }
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
                //  }
                //  else
                //  {
                //      $response = array(); // response라는 배열 생성
                //      $response["success"] = false; // 수정 성공여부
 
                //      echo json_encode($response);
                //      exit();
                //  }

            }
            // 채팅방에 유저가 남아있는 경우
            else
            {
                // 나간 유저 고유번호만 0값으로 수정
                $sql = "UPDATE chat_room SET purchase_user_num = 0 WHERE room_num = $roomNum";
                $result = mysqli_query($conn, $sql);


                // 채팅방 나가기 처리 성공.
                if($result)
                {
                    $response = array(); // response라는 배열 생성
                    $response["success"] = true; // 수정 성공여부

                    echo json_encode($response);
                    exit();
                }
            }

        }

    }
    // 채팅방이 존재하지 않으면
    else
    {
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // 수정 성공여부

        echo json_encode($response);
        exit();
    }
?>