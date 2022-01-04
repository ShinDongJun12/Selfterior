<?php
    // ini_set('display_errors', true);

	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $login_user_num = $_GET["login_user_num"]; // 현재 접속한 회원 고유번호
    //$post_category = $_GET["post_category"]; // 게시물 카테고리 (= 질문과답변)
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $comment_category = $_GET["comment_category"]; // 댓글 카테고리 (= 질문과답변)
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에대한 모든 데이터를 담을 배열


//  (1) 질문과 답변 게시물 중에서 사용자의 댓글 or 답글이 달린 게시물 고유번호를 조회한다.
    // (※ (1-1) 한 게시물에 답변을 여러번 작성한 경우 게시물 중복을 없애고 조회한다. => 중복 제거 DISTINCT , (1-2) 답변(댓글or답글) 등록시간 기준 내림차순으로 정렬한다. , (1-3) 그렇게 조회한 post_num 데이터를 10개씩 불러온다.)
    //  'P' : 서브쿼리를 사용하려면 서브쿼리의 별칭을 붙여줘야 한다고 한다.
    $sql = "SELECT post_num FROM (SELECT DISTINCT post_num, MAX(regtime) FROM comments WHERE user_num = $login_user_num AND category = '$comment_category' GROUP BY post_num ORDER BY MAX(regtime) DESC)P LIMIT $limit offset $page";
    $res = mysqli_query($conn, $sql);
    $resultNum = mysqli_num_rows($res); 
    
    // 게시물이 1개라도 존재하면  SELECT post_num FROM comments WHERE user_num = 52 AND category = '질문과답변' ORDER BY regtime DESC      SELECT DISTINCT post_num FROM (SELECT post_num FROM comments WHERE user_num = 52 AND category = '질문과답변' ORDER BY regtime DESC)P LIMIT 10 offset 1
    if($resultNum > 0){
        
        // 조회된 게시물 고유번호 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        while($post = $res->fetch_assoc()) { 

            // (2) (1)번에서 조회된 게시물에대 대한 모든 데이터를 조회한다. (※ ORDER BY post_regtime DESC 정렬 하지 X -> 댓글작성순으로 불러와야 하기때문)
            $sql2 = "SELECT * FROM qna_post WHERE post_num = $post[post_num] ";
            $res2 = mysqli_query($conn, $sql2);
        
            // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장.
            while($post_info = $res2->fetch_assoc()) { 
                
                // ***** 사용자의 닉네임과 프로필 이미지 URL 조회 *****
                // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
                $sql_user = "select profile_image, user_nickname from members where user_num = $post_info[user_num]";
                $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                $user_info = mysqli_fetch_assoc($res_user);

                // ***** 게시물 댓글 총 개수 조회 *****
                // 현재 게시물 고유번호와 카테고리값을 통해 현재 게시물의 댓글 총 개수를 조회한다.
                $sql_user2 = "select * from comments where post_num = $post_info[post_num] and category = '$comment_category'";
                $res_comment = mysqli_query($conn, $sql_user2);
                $comment_total = mysqli_num_rows($res_comment); 

                
                // ***** 게시물 데이터 최종 합치기 *****
                // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
                $data = [
                    'post_num' => $post_info['post_num'], // 게시물 고유번호
                    'user_num' => $post_info['user_num'], // 게시물 작성자(사용자) 고유번호
                    'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                    'user_profile_image' => $user_info['profile_image'], // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                    'post_category' => $post_info['post_category'], // 게시물 카테고리
                    'post_title' => $post_info['post_title'], // 게시물 제목
                    'post_content' => $post_info['post_content'], // 게시물 내용
                    'post_thumbnail_image' => $post_info['post_imgPath'], // 게시물 썸네일 URL(첫번째 사진)
                    'post_regtime' => $post_info['post_regtime'], // 게시물 등록날짜
                    'post_view_count' => $post_info['post_view_count'], // 게시물 조회수
                    'post_comment_count' => $comment_total // 게시물 댓글 총 개수
                ]; 

                array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
            }

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



// 본인이 본인 게시물에 답변단 경우 제외 쿼리(서브쿼리 사용) =========================================================================================================================================================================================================================

    // // (1) 질문과 답변 게시물 중에서 사용자의 댓글 or 답글이 달린 게시물 고유번호를 조회한다.(select 컬럼B from 테이블B)
    // //     (※ (1-1) 본인이 본인 게시물에 답변단 것은 조회하지 않는다. , (1-2) 한 게시물에 답변을 여러번 한경우 게시물 중복을 없애고 조회한다. => 중복 제거 DISTINCT)
    // // 중복제거 뺴고 되는거 일단$sql = "SELECT post_num FROM comments WHERE user_num = $login_user_num AND category = '$comment_category' AND post_num NOT IN (SELECT post_num FROM qna_post WHERE user_num = $login_user_num) ORDER BY regtime DESC LIMIT $limit offset $page";
    // $sql = "SELECT post_num FROM comments WHERE user_num = $login_user_num AND category = '$comment_category' AND post_num NOT IN (SELECT post_num FROM qna_post WHERE user_num = $login_user_num) ORDER BY regtime DESC LIMIT $limit offset $page";
    // $res = mysqli_query($conn, $sql);
    // $resultNum = mysqli_num_rows($res); 
// ================================================================================================================================================================================================================================================================================
?>