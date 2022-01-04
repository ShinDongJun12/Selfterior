<?php 
 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.


    $searchKeyWord = $_POST["searchKeyWord"]; // 검색 키워드.


    // 멤버테이블에서 유저 닉네임에 검색 키워드가 포함되어 있는 유저의 데이터를 조회한다.
    $sql = "SELECT * FROM members WHERE user_nickname LIKE '%$searchKeyWord%'";
    
    $ret = mysqli_query($conn, $sql); 
    $resultTotalNum = mysqli_num_rows($ret); // 검색 키워드로 조회되는 유저수. (※ Int형)

    // 검색된 유저가 1명 이상 존재하면
    if($resultTotalNum > 0){

        $response = array(); // response라는 배열 생성
    
        $response["success"] = true; 
        $response["searchUserTotalNum"] = $resultTotalNum; // 검색된 유저 총 수 세팅.

        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();

    // 존재하지 않으면
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        $response["searchUserTotalNum"] = $resultTotalNum; // 검색된 유저 총 수 세팅. (※ 검색결과 없는 경우 0 값이 들어갈 것.)
    
        echo json_encode($response);
        exit();
    }
?>