<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 집구경 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$postNum;

        // 해당 고유번호를 가진 게시물이 존재하는지 조회. 
        $sql = "SELECT * FROM house_tour_post WHERE post_num = $resultNum"; 
        $ret = mysqli_query($conn, $sql); 
        $exist = mysqli_num_rows($ret); 

        if($exist > 0){

            // 게시물 좋아요 테이블에서 해당 게시물 고유번호
            // (주의! 게시물에 대한 좋아요 총 개수 이므로 post_like_check이 2인 데이터만 개수에 포함 시켜야한다.)
            $sql = "SELECT post_like_num FROM post_like WHERE post_num = $resultNum AND post_like_check = 2"; 
            $ret = mysqli_query($conn, $sql); 
            
            // 게시물에대한 좋아요 총 개수 조회 성공
            if($ret){

                $exist = mysqli_num_rows($ret); // 총 개수를 변수에 저장.

                $response = array(); // response라는 배열 생성
                $response["post_like_total_num"] = $exist; // post_like_total_num 키 명으로 총 개수값을 배열에 넣는다.
                $response["success"] = true; 
                echo json_encode($response);
            
                exit();

            }
            // 게시물에대한 좋아요 총 개수 조회 실패
            else{

                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                echo json_encode($response);

                exit();
            }
    
        // 조회 실패
        }else{
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }
?>