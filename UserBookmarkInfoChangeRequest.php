<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultUserNum = (int)$userNum;

    $postNum = $_POST["postNum"]; // 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultPostNum = (int)$postNum;

    $bookmarkCategory = $_POST["bookmarkCategory"]; // 게시물(북마크) 카테고리 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)

    // => 체크 상태여부는 2:북마크 해둔상태, 1:북마크 해제상태 , 0: 한번도 해당게시물의 부마크 버튼을 누른적없는 상태.
    $bookmarkCheckNum = $_POST["bookmarkCheckNum"]; // 북마크 체크상태 여부 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.) 
    $resultbookmarkCheckNum = (int)$bookmarkCheckNum;

    // 북마크 체크 해제 상태이면
    if($resultbookmarkCheckNum == 1){

        // 북마크 체크상태로 변경.
        $sql = "UPDATE bookmark SET bookmark_check = 2, bookmark_regtime = now() WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND bookmark_category = '$bookmarkCategory'";
        $res = mysqli_query($conn, $sql);
        
        if($res) {

            // 사용자 고유번호, 게시물 고유번호, 북마크 카테고리 데이터로 현재 로그인된 사용자가 특정 
            $sql = "SELECT bookmark_check FROM bookmark WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND bookmark_category = '$bookmarkCategory'"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (bookmark_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
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
    // 북마크 체크 상태이면
    else if($resultbookmarkCheckNum == 2){

        // 북마크 체크상태로 변경.
        $sql = "UPDATE bookmark SET bookmark_check = 1, bookmark_regtime = now() WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND bookmark_category = '$bookmarkCategory'";
        $res = mysqli_query($conn, $sql);
        
        if($res) {

            // 사용자 고유번호, 게시물 고유번호, 북마크 카테고리 데이터로 현재 로그인된 사용자가 특정 게시물을 북마크 하였는지 조회한다.
            $sql = "SELECT bookmark_check FROM bookmark WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND bookmark_category = '$bookmarkCategory'"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (bookmark_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
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
    // 해당게시물에 북마크 한번도 클릭한적 없음. ($resultbookmarkCheckNum == 0)
    else{

        // 해당 게시물과 유저간의 북마크 데이터 새로 만들어준다.
        $sql = "INSERT INTO bookmark (post_num, user_num, bookmark_check, bookmark_category, bookmark_regtime) VALUES ($resultPostNum, $resultUserNum, 2, '$bookmarkCategory', now())";
        $res = mysqli_query($conn, $sql);
        
        if($res) {
            
            // 방금 INSERT하고 생성된 북마크 고유번호 값 가져오기.
            $get_bookmark_num = mysqli_insert_id($conn);
             
            $sql = "SELECT bookmark_check FROM bookmark WHERE bookmark_num = $get_bookmark_num"; 
            $ret = mysqli_query($conn, $sql); 
            
            if($ret){
            
                $response = array(); // response라는 배열 생성
                $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (bookmark_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
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