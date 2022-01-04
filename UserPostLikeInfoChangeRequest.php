<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultUserNum = (int)$userNum;

    $postNum = $_POST["postNum"]; // 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultPostNum = (int)$postNum;

    // => 체크 상태여부는 2:좋아요 누른상태, 1:좋아요 안 누른상태 , 0: 한번도 해당게시물의 좋아요 버튼을 누른적없는 상태.
    $postLikeCheckNumStr = $_POST["postLikeCheckNumStr"]; // 좋아요 체크상태 여부 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.) 
    $resultpostLikeCheckNumStr = (int)$postLikeCheckNumStr;

    // 좋아요 누르지 않은 상태이면
    if($resultpostLikeCheckNumStr == 1){

        // 좋아요 누른상태로 변경
        $sql = "UPDATE post_like SET post_like_check = 2, post_like_regtime = now() WHERE user_num = $resultUserNum AND post_num = $resultPostNum";
        $res = mysqli_query($conn, $sql);
        
        if($res) {

            // 사용자 고유번호, 게시물 고유번호 데이터로 현재 로그인된 사용자가 특정 게시물을 좋아요 했는지 조회한다.
            $sql = "SELECT post_like_check FROM post_like WHERE user_num = $resultUserNum AND post_num = $resultPostNum"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (post_like_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
                $response["success"] = true; 

                echo json_encode($response);
                exit();

            }else{

                $response = array();
                $response["success"] = false; 
            
                echo json_encode($response);
                exit();
            }

        } 
        else {

            $response = array(); 
            $response["success"] = false;
        
            echo json_encode($response);
            exit();
        }

    }
    // 좋아요 누른 상태이면
    else if($resultpostLikeCheckNumStr == 2){

        // 좋아요 안 누른 상태로 변경
        $sql = "UPDATE post_like SET post_like_check = 1, post_like_regtime = now() WHERE user_num = $resultUserNum AND post_num = $resultPostNum";
        $res = mysqli_query($conn, $sql);
        
        if($res) {

            // 사용자 고유번호, 게시물 고유번호 데이터로 현재 로그인된 사용자가 특정 게시물을 좋아요 한 상태인지 조회한다.
            $sql = "SELECT post_like_check FROM post_like WHERE user_num = $resultUserNum AND post_num = $resultPostNum"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (post_like_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
                $response["success"] = true; 

                echo json_encode($response);
                exit();

            }else{

                $response = array();
                $response["success"] = false; 
            
                echo json_encode($response);
                exit();
            }

        } 
        else {

            $response = array(); 
            $response["success"] = false;
        
            echo json_encode($response);
            exit();
        }

    }
    // 해당 게시물에 좋아요 한 번도 클릭한적 없음. ($resultpostLikeCheckNumStr == 0)
    else{

        // 해당 게시물과 유저간의 북마크 데이터 새로 만들어준다.
        $sql = "INSERT INTO post_like (post_num, user_num, post_like_check, post_like_regtime) VALUES ($resultPostNum, $resultUserNum, 2, now())";
        $res = mysqli_query($conn, $sql);
        
        if($res) {
            
            // 방금 INSERT하고 생성된 좋아요 고유 번호 값 가져오기.
            $get_post_like_num = mysqli_insert_id($conn);
             
            $sql = "SELECT post_like_check FROM post_like WHERE post_like_num = $get_post_like_num"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (post_like_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
                $response["success"] = true; 

                echo json_encode($response);
                exit();

            }else{

                $response = array();
                $response["success"] = false; 
            
                echo json_encode($response);
                exit();
            }

        } 
        else {

            $response = array(); 
            $response["success"] = false;
        
            echo json_encode($response);
            exit();
        }


    }
?>