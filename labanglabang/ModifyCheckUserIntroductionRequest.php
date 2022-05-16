<?php
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    // 회원이 입력한 닉네임값을 받아온다.
    $userIntroduction = $_POST["userIntroduction"]; // 사용자가 입력한 유저 소개글
    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$userNum;


    // DB에 유저 소개글 수정한 내용 저장.
    $sql = "UPDATE members SET user_introduction ='$userIntroduction' WHERE user_num = $resultNum";
    $res = mysqli_query($conn, $sql);

    // 소개글이 변경 성공
    if($res) {

        // 변경 후 변경된 회원 정보 모두 조회.
        $sql = "SELECT * FROM members WHERE user_num=$resultNum";
        $result = mysqli_query($conn, $sql);

        $response = array(); // 조회 값이 없기때문에 그냥 response 배열을 만들어준다.
        $response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = true; // success라는 이름을 가진 변수는 기본값이 true였다가 유저가 입력한 닉네임값이 이미 존재하면 아래에서 false값을 가지게 된다.
        
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    }
    // 소개글 변경 실패
    else{

        $response = array(); // response라는 배열 생성
        //$response = mysqli_fetch_array($result); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response["success"] = false; 
        $response["samecheck"] = "same"; // 이상태는 그냥 중복이 아닌데 위에 커리문 실행 안된 오류일 때 다이얼로그창 안 떠야 해서 same으로 처리.
        echo json_encode($response); // 해당 리스폰스를 반환하므로 안드로이드에서 이 결과값을 받아갈 수 있다.
        exit();
    }
?>