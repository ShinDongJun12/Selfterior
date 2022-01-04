<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $loginUserNum = $_POST["loginUserNum"]; // 현재 접속유저 고유번호
    $loginUserNumInt = (int)$loginUserNum;

    $targetUserNum = $_POST["targetUserNum"]; // 고유 식별자
    $targetUserNumInt = (int)$targetUserNum;

    $followCheckNum = $_POST["followCheckNum"]; // 팔로우 체크유무 번호 (0 ro 1)
    $followCheckNumInt = (int)$followCheckNum;

    // 팔로우 체크유무 번호 값에 따라 구분
    // 팔로우 하는경우
    if($followCheckNumInt == 0)
    {
        // 현재 로그인된 유저가 피드에 들어온 유저를 팔로잉 했으므로 follow.
        $sql = "INSERT INTO follow (user_num, target_user_num, follow_datetime) VALUES ($loginUserNumInt, $targetUserNumInt, now())";
    }
    // 팔로우 끊는경우
    else{

        // 현재 로그인된 유저가 피드에 들어온 유저를 팔로잉 했으므로 follow.
        $sql = "DELETE FROM follow WHERE user_num = $loginUserNumInt AND target_user_num = $targetUserNumInt"; 
    }
    $ret = mysqli_query($conn, $sql);

    // 팔로우 처리 성공
    if($ret){
        
        // 팔로우 체크유무 번호 값에 따라 구분
        // 팔로우 하는경우
        if($followCheckNumInt == 0)
        {
        
            // 피드유저 팔로워 수 +1 증가
            $sql = "UPDATE members SET follower_num = follower_num+1 WHERE user_num = $targetUserNumInt";

            // 접속유저 팔로잉 수 +1 증가
            $sql2 = "UPDATE members SET following_num = following_num+1 WHERE user_num = $loginUserNumInt";
        }
        // 팔로우 끊는경우
        else{

            // 피드유저 팔로워 수 -1 증가
            $sql = "UPDATE members SET follower_num = follower_num-1 WHERE user_num = $targetUserNumInt";
        
            // 접속유저 팔로잉 수 -1 증가
            $sql2 = "UPDATE members SET following_num = following_num-1 WHERE user_num = $loginUserNumInt";    
        }
        $ret = mysqli_query($conn, $sql);
        $ret2 = mysqli_query($conn, $sql2);

        // 팔로우 처리 성공
        if($ret && $ret2){
            
            $response = array(); // response라는 배열 생성
            // $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
            $response["success"] = true; 

            echo json_encode($response);
            //echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        
        // 팔로우 처리 실패
        }else{
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
            
            echo json_encode($response);
            exit();
        }
    
    // 팔로우 처리 실패
    }
    else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        echo json_encode($response);
        exit();
    }
?>