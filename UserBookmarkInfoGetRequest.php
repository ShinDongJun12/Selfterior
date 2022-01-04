<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultUserNum = (int)$userNum;

    $postNum = $_POST["postNum"]; // 게시물 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultPostNum = (int)$postNum;

    $bookmarkCategory = $_POST["bookmarkCategory"]; // 게시물(북마크) 카테고리 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    

        // 사용자 고유번호, 게시물 고유번호, 북마크 카테고리 데이터로 현재 로그인된 사용자가 특정 
        $sql = "SELECT bookmark_check FROM bookmark WHERE user_num = $resultUserNum AND post_num = $resultPostNum AND bookmark_category = '$bookmarkCategory'"; 
        $ret = mysqli_query($conn, $sql); 
        $num_row = mysqli_num_rows($ret); 

        // 사용자가 해당 게시물에 대해 북마크 버튼을 한번이라도 누른적이 있다면.
        if($num_row > 0){
        
            // 색칠 된 북마크 출력
            $response = array(); // response라는 배열 생성
            $response = mysqli_fetch_array($ret); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다. (bookmark_check라는 이름으로 해당 컬럼 값이 배열에 저장됨.)
            $response["success"] = true; 

            echo json_encode($response);
            exit();

        // 한번도 누른적이 없다면.
        }else{

            // 색칠 안 된 북마크 출력 
            $response = array(); // response라는 배열 생성
            // bookmark_check라는 이름으로 값이 아예 존재하지 않을테니 false값만 배열에 넣어서 넘겨주고 클라이언트 단에서 판단 후 북마크 버튼 출력한다.
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }
?>