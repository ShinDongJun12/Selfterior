<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultUserNum = (int)$userNum;

    $postNum = $_POST["postNum"]; // 게시글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultPostNum = (int)$postNum;

    // => 체크 상태여부는 2:좋아요 누른상태, 1:좋아요 안 누른상태 , 0: 한번도 해당게시글의 좋아요 버튼을 누른적없는 상태.
    $postLikeCheckNumStr = $_POST["postLikeCheckNumStr"]; // 좋아요 체크상태 여부 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.) 
    $resultpostLikeCheckNumStr = (int)$postLikeCheckNumStr;

// 기존 코드 수정함.
// 좋아요를 최초로 누를때 post_like_check 값을 1로 넣어 데이터를 생성하기 때문에 resultpostLikeCheckNumStr 값이 1이면 좋아요가 눌려있는 상태이다.


    // 좋아요 누른 상태. (이때 좋아요 눌렀던 데이터를 삭제하도록 처리함.)
    if($resultpostLikeCheckNumStr == 1){

        // 좋아요 누른기록 삭제.
        $sql = "DELETE FROM post_like WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND post_like_category = '게시글'"; 
        $res = mysqli_query($conn, $sql);
        
        if($res) {

            // ★삭제시 체크값을 0으로 넘겨 주어야한다.
            $response = array(); // response라는 배열 생성
            $response["post_like_check"] = 0; 
            
            // ★★★ 여기서는 게시글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
            // 좋아요 클릭 이후의 게시글 좋아요 총 개수 조회하기.
            $sql = "SELECT * FROM post_like WHERE post_num = $resultPostNum AND post_like_category = '게시글'"; 
            $ret = mysqli_query($conn, $sql); 
            $postLikeTotal = mysqli_num_rows($ret);
            
            if($ret){
            
                $response["postLikeTotal"] = $postLikeTotal; 
                $response["success"] = true; 
            
                echo json_encode($response);
                exit();

            }else{

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
    // 좋아요 안 누른 상태인 경우. ($resultpostLikeCheckNumStr == 0)
    else{

        // 해당 게시글과 유저간의 북마크 데이터 새로 만들어준다.
        $sql = "INSERT INTO post_like (post_num, user_num, post_like_check, post_like_regtime, post_like_category) VALUES ($resultPostNum, $resultUserNum, 1, now(), '게시글')";
        $res = mysqli_query($conn, $sql);
        
        if($res) {
            
            // 방금 INSERT하고 생성된 좋아요 고유 번호 값 가져오기.
            // $get_post_like_num = mysqli_insert_id($conn);

            $response = array(); // response라는 배열 생성
            $response["post_like_check"] = 1; // 게시글에대한 유저의 체크상태 값.
        
            // ★★★ 여기서는 게시글에 눌린 전체 좋아요 개수를 가져와야하므로 현재 접속 유저 값은 조건에서 빼고 조회한다. ★★★
            // 좋아요 클릭 이후의 게시글 좋아요 총 개수 조회하기.
            $sql = "SELECT * FROM post_like WHERE post_num = $resultPostNum AND post_like_category = '게시글'"; 
            $ret = mysqli_query($conn, $sql); 
            $postLikeTotal = mysqli_num_rows($ret);
            
            if($ret){
            
                $response["postLikeTotal"] = $postLikeTotal; 
                $response["success"] = true; 
            
                echo json_encode($response);
                exit();

            }
            else{

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