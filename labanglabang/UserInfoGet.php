<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    $userNum = $_POST["userNum"]; // 유저 고유번호.
    $resultNum = (int)$userNum;

    // (1). 현재 가입된 회원정보 바로 조회.
    $sql = "SELECT * FROM members WHERE user_num = $resultNum"; 
    $ret = mysqli_query($conn, $sql); 
    $result = mysqli_num_rows($ret); 

    if($result > 0){
    
        $response = array(); // response라는 배열 생성
        $response = mysqli_fetch_array($ret); // members테이블에서 가져올 수 있는 회원정보는 다 가져옴. (유저 소개글, 팔로워 수, 팔로잉 수)

        // (2). 유저가 작성한 게시글 갯수조회.
        $sql = "SELECT * FROM post WHERE user_num = $resultNum"; 
        $ret = mysqli_query($conn, $sql); 

        // 해당 회원이 작성한 게시글 총 개수 조회 성공.
        if($ret){

            $result = mysqli_num_rows($ret); 
            $response["post_count"] = $result; // 회원이 작성한 게시글 총 개수 배열에 저장.
            
            // (3). 회원이 북마크한 게시글 총 개수 조회.
            $sql = "SELECT bookmark_num FROM bookmark WHERE user_num = $resultNum AND bookmark_check = 1 AND bookmark_category = '게시글'"; 
            $ret = mysqli_query($conn, $sql);  

            // 해당 회원이 북마크한 게시물 총 개수 조회 성공.
            if($ret){

                $result = mysqli_num_rows($ret);
                $response["post_bookmark_count"] = $result; // 회원이 북마크한 게시물 총 개수 배열에 저장.

// //VOD 게시물 추가 후 수정할 것 ------------------------------------------------------------------------
// // 유저가 작성한 VOD 게시물 갯수, 유저가 북마크한 VOD게시글 총 개수, 유저가 구독한 스트리머 총 개수, 유저 애청자 총 개수 추가해야함
//                 //VOD 게시물 총 개수 조회.
//                 $sql = "SELECT * FROM vod_post WHERE user_num = $resultNum"; 
//                 $ret = mysqli_query($conn, $sql);  

//                 // 조회 성공.
//                 if($ret){

//                     $result = mysqli_num_rows($ret);
//                     $response["used_transaction_count"] = $result; // 중고거래 게시물 총 개수 배열에 저장.

                    // ★현재 여기가 최종 결과★
                    $response["success"] = true; 
                    echo json_encode($response);
                    //echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    exit();

                // }
                // // 조회 실패.
                // else{
                    
                //     $response = array(); // response라는 배열 생성
                //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                
                //     echo json_encode($response);
                //     exit();
                // }

// 총 애청자 수도 나중에 추가할 것

            }
            // 조회 실패.
            else{
                
                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
            
                echo json_encode($response);
                exit();
            }
            
        }
        // 조회 실패.
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