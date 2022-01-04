<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $post_num = $_GET["post_num"]; // 게시물 고유번호
    $post_category = $_GET["post_category"]; // 게시물 카테고리
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $comment_data_array = array(); // 리사이클러뷰에 보여줄 한 댓글/대댓글에 대한 모든 데이터를 담을 배열

    // (1) parent 정렬 (2) 등록일 기준 정렬 
    // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
    // (ORDER BY 다중정렬  parent desc, regtime desc => 'parent'을 기준으로 내림차순 정렬 후, 'parent'가 같은 값에 한해서 'regtime'로 내림차순 정렬 하겠다.)
    $sql = "SELECT * FROM comments WHERE post_num = $post_num AND category = '$post_category' ORDER BY parent desc, regtime desc limit $limit offset $page"; // asc
    $res = mysqli_query($conn, $sql);
    
    // 댓글.대댓글 갯수만큼 while문 돌면서 데이터 조회하고 저장.
	while($comment = $res->fetch_assoc()) { 
        
        // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
        // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
		$sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
        $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
        $user_info = mysqli_fetch_assoc($res_user);
        
        // ***** 댓글/대댓글 데이터 최종 합치기 *****
        // 댓글/대댓글 데이터 + 댓글/대댓글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
        $data = [
            'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
            'post_num' => $comment['post_num'], // 게시물 고유번호
            'user_num' => $comment['user_num'], // 작성자 고유번호
            'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
            'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
            'content' => $comment['content'], // 내용
            'category' => $comment['category'], // 카테고리
            'parent' => $comment['parent'], // 부모 번호
            'regtime' => $comment['regtime'], // 작성날짜
            'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
            'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
        ]; 
        array_push($comment_data_array, $data); // 리사이클러뷰에 보여줄 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
	}

	mysqli_close($conn); // DB 종료.

    echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
?>