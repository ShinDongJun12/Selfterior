<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 게시물 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$postNum;

        // 해당 게시물 고유번호로 게시물이 존재하는지 조회.  
        $sql = "SELECT * FROM house_tour_post WHERE post_num = $resultNum"; 
        $ret = mysqli_query($conn, $sql); 
        $exist = mysqli_num_rows($ret); 

        // 존재하면
        if($exist > 0){
        
            // 해당 게시물 조회수 +1씩 증가시킨다.
            $sql = "UPDATE house_tour_post SET post_view_count = post_view_count+1 WHERE post_num = $postNum";
            $ret2 = mysqli_query($conn, $sql); 
            
            if($ret2){
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
                $response["success"] = true; 
    
                //$response = array ('success'=>true,'user_num'=>$response[user_num],'user_email'=>$response[user_email],'user_pass'=>$response[user_pass],'user_nickname'=>$response[user_nickname],'platform_type'=>$response[platform_type],'user_regtime'=>$response[user_regtime],'profile_image'=>$response[profile_image],'user_unique_identifier'=>$response[user_unique_identifier]);
    
                echo json_encode($response);
                //echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            }
            else{
                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
            
                echo json_encode($response);
                exit();
            }

        // 존재하지 않으면
        }else{
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }
?>