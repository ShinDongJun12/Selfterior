<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $login_user_num = $_GET["login_user_num"]; // 현재 접속한 회원 고유번호

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에대한 모든 데이터를 담을 배열

    // 먼저 날짜 빠른순으로 게시물 각각의 데이터를 배열에 담아 둔다.
    // 집구경 게시물 등록날짜 기준 내림차순으로 데이터 조회(가장 최근에 업로드한 게시물이 먼저오도록(맨 위로 오도록)조회.)
    // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
    $sql = "select * from house_tour_post";
    $res = mysqli_query($conn, $sql);
    $resultNum = mysqli_num_rows($res); 
    
    mysqli_close($conn); // DB 종료.

    //echo json_encode($resultNum); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
    echo $resultNum;
?>