<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 중고거래 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$postNum;

    $dealStatus = $_POST["dealStatus"]; // 거래상태.

  
    // 해당 고유번호를 가진 게시물이 존재하는지 조회. 
    $sql = "UPDATE used_transaction_post SET transaction_status = '$dealStatus' WHERE post_num = $resultNum"; 
    $ret = mysqli_query($conn, $sql); 

    // 변경 성공.
    if($ret)
    {

        // 변경된 게시물의 데이터에서 거래상태 값을 조회한다.     
        $sql = "SELECT * FROM used_transaction_post WHERE post_num = $resultNum";
        $ret = mysqli_query($conn, $sql);
 
        $response = array(); // response라는 배열 생성 
        //$response = mysqli_fetch_assoc($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (변경된 후의 거래상태값을 클라이언트단에서 받아 세팅해주기위함.)
        $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (변경된 후의 거래상태값을 클라이언트단에서 받아 세팅해주기위함.)
        $response["success"] = true; 
        echo json_encode($response);
    
        exit();
    }
    // 변경 실패.
    else{

        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        echo json_encode($response);

        exit();
    }
?>