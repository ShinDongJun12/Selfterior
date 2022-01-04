<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $userNum = (int)$userNum;

    $postNum = $_POST["postNum"]; // 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $postNum = (int)$postNum;

    $commentNum = $_POST["commentNum"]; // 댓글/답글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $commentNum = (int)$commentNum;

    // 사용자가 해당 댓글/답글에 좋아요를 누른 상태인지 아닌지 조회하여 체크한다.
    $sql = "SELECT * FROM comment_like WHERE post_num = $postNum AND user_num = $userNum AND comment_num = $commentNum"; 
    $ret = mysqli_query($conn, $sql); 
    $check = mysqli_num_rows($ret);

    // 좋아요 누른상태 이면 -> 좋아요 삭제 한다.
    if($check > 0){

        // 해당 좋아요 기록 삭제.
        $sql = "DELETE FROM comment_like WHERE post_num = $postNum AND user_num = $userNum AND comment_num = $commentNum"; 
        $ret = mysqli_query($conn, $sql); 
        
        if($ret) {

            // ★★★ 여기서는 댓글/답글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
            // 삭제 이후의 좋아요 총 개수 조회하기.
            $sql = "SELECT * FROM comment_like WHERE post_num = $postNum AND comment_num = $commentNum"; 
            $ret = mysqli_query($conn, $sql); 
            $commentLikeTotal = mysqli_num_rows($ret);
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response["success"] = true; 
                $response["commentLikeTotal"] = $commentLikeTotal; 

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
    // 좋아요 안누른 상태이면 -> 좋아요 생성.
    else{

        // 해당 게시물과 유저간의 북마크 데이터 새로 만들어준다.
        $sql = "INSERT INTO comment_like (post_num, user_num, comment_num) VALUES ($postNum, $userNum, $commentNum)";
        $ret = mysqli_query($conn, $sql);
        
        if($ret) {

            // ★★★ 여기서는 댓글/답글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
            // 추가 이후의 좋아요 총 개수 조회하기.
            $sql = "SELECT * FROM comment_like WHERE post_num = $postNum AND comment_num = $commentNum"; 
            $ret = mysqli_query($conn, $sql); 
            $commentLikeTotal = mysqli_num_rows($ret);
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response["success"] = true; 
                $response["commentLikeTotal"] = $commentLikeTotal; 

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