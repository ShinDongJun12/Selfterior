<?php 
 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.


    $searchKeyWord = $_POST["searchKeyWord"]; // 검색 키워드.
    $buttonCheckString = $_POST["buttonCheckString"]; // 댓글을 기다리는 질문 버튼 클릭유무 (String)
    $buttonCheckInt = (int)$buttonCheckString;


    // *버튼 클릭유무에 따라 쿼리문을 바꿔준다.
    // 버튼이 눌려 있는경우
    if($buttonCheckInt == 1){
        
        // 질문과답변 게시물 중 검색 키워드가 게시물 제목 or 게시물 내용에 포함돼 있으면서 댓글이 0개인 게시물을 조회한다.
        $sql = "SELECT * FROM (SELECT *, MAX(post_regtime) FROM qna_post WHERE post_title LIKE '%$searchKeyWord%' OR post_content LIKE '%$searchKeyWord%' GROUP BY post_num ORDER BY MAX(post_regtime) DESC)P WHERE post_num NOT IN (SELECT post_num FROM comments WHERE category = '질문과답변')";
    }
    // 버튼이 안눌려 있는경우
    else{
        // 질문과답변 게시물 제목 or 내용에 검색 키워드가 포함되어 있는 집구경 게시물의 데이터를 조회한다. OR post_content LIKE '%$searchKeyWord%'
        $sql = "SELECT * FROM qna_post WHERE post_title LIKE '%$searchKeyWord%' OR post_content LIKE '%$searchKeyWord%'";
    }

    $ret = mysqli_query($conn, $sql); 
    $resultTotalNum = mysqli_num_rows($ret); // 검색 키워드로 조회되는 게시물 총 개수. (※ Int형)

    // 검색된 게시물이 1개이상 존재하면
    if($resultTotalNum > 0){

        $response = array(); // response라는 배열 생성
    
        $response["success"] = true; 
        $response["searchPostTotalNum"] = $resultTotalNum; // 총 개수 세팅.

        echo json_encode($response);
        //echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();

    // 존재하지 않으면
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        $response["searchPostTotalNum"] = $resultTotalNum; // 총 개수 세팅. (※ 검색결과 없는 경우 0 값이 들어갈 것.)
    
        echo json_encode($response);
        exit();
    }
?>