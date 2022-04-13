<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    $postNum = $_POST["postNum"]; // 중고거래 게시물 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $postNum = (int)$postNum;
    $salesUserNum = $_POST["salesUserNum"]; // 판매자 고유번호
    $salesUserNum = (int)$salesUserNum;
    $purchaseUserNum = $_POST["purchaseUserNum"]; // 구매자 고유번호
    $purchaseUserNum = (int)$purchaseUserNum;

    // 받아온 값들로 일치하는 채팅방 조회.   
    $sql = "SELECT * FROM chat_room WHERE post_num = $postNum AND sales_user_num = $salesUserNum AND purchase_user_num = $purchaseUserNum"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 조건에 일치하는 채팅방이 존재하면
    if($exist > 0){
    
        $response = array(); // response라는 배열 생성
        // ★조회된 값이 존재하므로 그 값들의 response 배열에 담는다.★
        $response = mysqli_fetch_array($ret); // 조회된 채팅방 정보 모두 저장.

        $response["success"] = true; // 조회 성공여부값 저장
        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();

    // 조회 실패
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>