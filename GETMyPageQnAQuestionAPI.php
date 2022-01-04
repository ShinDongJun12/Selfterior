<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $login_user_num = $_GET["login_user_num"]; // 현재 접속한 회원 고유번호
    $post_category = $_GET["post_category"]; // 게시물 카테고리
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $comment_category = $_GET["comment_category"]; // 댓글 카테고리
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에대한 모든 데이터를 담을 배열

    // 먼저 날짜 빠른순으로 게시물 각각의 데이터를 배열에 담아 둔다.
    // '현재 접속한 사용자가 작성한' 질문과 답변 게시물 등록날짜 기준 내림차순으로 데이터 조회(가장 최근에 업로드한 게시물이 먼저오도록(맨 위로 오도록)조회.)
    // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
    $sql = "SELECT * FROM qna_post WHERE user_num = $login_user_num ORDER BY post_regtime DESC LIMIT $limit offset $page";
    $res = mysqli_query($conn, $sql);
    $resultNum = mysqli_num_rows($res); 
    
    // 게시물이 1개라도 존재하면 
    if($resultNum > 0){
        // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        while($post = $res->fetch_assoc()) { 
            
            // ***** 사용자의 닉네임과 프로필 이미지 URL 조회 *****
            // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
            $sql_user = "select profile_image, user_nickname from members where user_num = $post[user_num]";
            $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $user_info = mysqli_fetch_assoc($res_user);

            // ***** 게시물 댓글 총 개수 조회 *****
            // 현재 게시물 고유번호와 카테고리값을 통해 현재 게시물의 댓글 총 개수를 조회한다.
            $sql_user2 = "select * from comments where post_num = $post[post_num] and category = '$comment_category'";
            $res_comment = mysqli_query($conn, $sql_user2);
            $comment_total = mysqli_num_rows($res_comment); 

            
            // ***** 게시물 데이터 최종 합치기 *****
            // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
            $data = [
                'post_num' => $post['post_num'], // 게시물 고유번호
                'user_num' => $post['user_num'], // 게시물 작성자(사용자) 고유번호
                'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                'user_profile_image' => $user_info['profile_image'], // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                'post_category' => $post['post_category'], // 게시물 카테고리
                'post_title' => $post['post_title'], // 게시물 제목
                'post_content' => $post['post_content'], // 게시물 내용
                'post_thumbnail_image' => $post['post_imgPath'], // 게시물 썸네일 URL(첫번째 사진)
                'post_regtime' => $post['post_regtime'], // 게시물 등록날짜
                'post_view_count' => $post['post_view_count'], // 게시물 조회수
                'post_comment_count' => $comment_total // 게시물 댓글 총 개수
            ]; 

            array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
        }

        mysqli_close($conn); // DB 종료.

        echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
    }
    // 게시물이 1개라도 존재하면
    else{

        mysqli_close($conn); // DB 종료.

    //     //$result = array("result" => "error"); // $result["success"] = false;

    //     $response = array(); // response라는 배열 생성
    //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    //     echo json_encode($response);

        exit();
    }
?>