<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그대로 '가나다라'로 출력이 가능하게 해주는 것.

    $category_filter = $_GET["category_filter"]; // 카테고리 필터
    $login_user_num = $_GET["login_user_num"]; // 현재 접속한 회원 고유번호
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $category = $_GET["category"]; // 카테고리(게시글, 댓글 모두적용)
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에대한 모든 데이터를 담을 배열

// 아무것도 북마크 없을 때 예외처리할 것!!!!!!!!!!!!
    // 전체 게시글 불러올경우. 
    if($category_filter === "전체 게시글")
    {
        // 먼저 날짜 빠른순으로 게시물 각각의 데이터를 배열에 담아 둔다.
        // 게시글 등록날짜 기준 내림차순으로 데이터 조회(가장 최근에 업로드한 게시물이 먼저오도록(맨 위로 오도록)조회.)
        // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
        $sql = "select post_num, bookmark_check from bookmark where user_num = $login_user_num and bookmark_category = '$category' and bookmark_check = 1 order by bookmark_regtime desc limit $limit offset $page";
    }
    // 특정 게시글 불러올경우.
    else{
    
        // 현재 접속한 유저가 북마크한 게시글 중 북마크 카테고리가 '게시글'이고 북마크체크 값이 1인 post에 해당하면서 post카테고리가 특정 카테고리인인 post를 모두 조회한다.
        // $sql = "SELECT * FROM (SELECT *, MAX(bookmark_regtime) FROM bookmark WHERE user_num = $login_user_num AND bookmark_category = '$category' AND bookmark_check = 1 ORDER BY MAX(bookmark_regtime) DESC)P WHERE post_num NOT IN (SELECT post_num FROM post WHERE post_category = '$category_filter') LIMIT $limit offset $page";
        // $sql = "SELECT * FROM (SELECT *, MAX(post_regtime) FROM post WHERE post_category = '$category_filter' ORDER BY MAX(post_regtime) DESC)P WHERE post_num NOT IN (SELECT * FROM bookmark WHERE user_num = $login_user_num AND bookmark_category = '$category' AND bookmark_check = 1) LIMIT $limit offset $page";
        $sql = "select post_num, bookmark_check from bookmark where user_num = $login_user_num and bookmark_category = '$category' and bookmark_check = 1 order by bookmark_regtime desc limit $limit offset $page";
    }

    $res = mysqli_query($conn, $sql);
    
    
	while($book_mark_post = $res->fetch_assoc()) {
        
        // 저장한 값들 중 게시물 고유번호를 통해 게시물 데이터를 조회.
		$sql_post = "select * from post where post_num = $book_mark_post[post_num]";
        $res_post = mysqli_query($conn, $sql_post); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
        $post_info = mysqli_fetch_assoc($res_post);

        // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
		$sql_user = "select profile_image, user_nickname from members where user_num = $post_info[user_num]";
        $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
        $user_info = mysqli_fetch_assoc($res_user);


        // ***** 즐겨찾기 클릭여부 조회 *****
        // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호, 북마크 카테고리 값들로 현재 접속중인 회원의 해당 게시물 북마크 체크여부 데이터를 조회한다. $post_category
        $sql_user2 = "select bookmark_check from bookmark where user_num = $login_user_num and post_num = $book_mark_post[post_num] and bookmark_category = '$category'";
        $res_user2 = mysqli_query($conn, $sql_user2); 
        $num_row = mysqli_num_rows($res_user2); 

        // 사용자가 해당 게시물에 대해 즐겨찾기 버튼을 한번이라도 누른적이 있다면.
        if($num_row > 0){
            $bookmark_info = array(); // post_info 배열 생성
            $bookmark_info = mysqli_fetch_assoc($res_user2); // bookmark_check값을 받아온다.
        }
        // 한번도 북마크 버튼을 누른적이 없으면.
        else{   

            $bookmark_info = array(); // post_info 배열 생성
            $bookmark_info['bookmark_check'] = 0; // bookmark_check값에 0값을 넣는다.
        }


        // ***** 좋아요 클릭여부 조회 *****
        // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호 값들로 현재 접속중인 회원의 해당 게시물 좋아요 체크여부 데이터를 조회한다.
        $sql_user2 = "select post_like_check from post_like where user_num = $login_user_num and post_num = $book_mark_post[post_num] and post_like_category = '$category'";
        $res_user2 = mysqli_query($conn, $sql_user2);
        $num_row = mysqli_num_rows($res_user2); 

        // 사용자가 해당 게시물에 대해 좋아요 버튼을 한번이라도 누른적이 있다면.
        if($num_row > 0){
            $post_like_info = array(); // post_like_info 배열 생성
            $post_like_info = mysqli_fetch_assoc($res_user2); // post_like_check값을 받아온다.
        }
        // 한번도 좋아요 버튼을 누른적이 없으면.
        else{

            $post_like_info = array(); // post_info 배열 생성
            $post_like_info['post_like_check'] = 0; // post_like_check값에 0값을 넣는다.
        }


        // ***** 게시글 댓글 총 개수 조회 *****
        // 현재 게시글 고유번호와 카테고리값을 통해 현재 게시물의 댓글 총 개수를 조회한다.
        $sql_post = "select * from comments where post_num = $book_mark_post[post_num] and category = '$category'";
        $res_comment = mysqli_query($conn, $sql_post);
        $comment_total = mysqli_num_rows($res_comment); 


        // ***** 게시글 좋아요 총 개수 조회 *****
        // 현재 게시글 고유번호와 카테고리값을 통해 현재 게시물의 좋아요 총 개수를 조회한다.
        $sql_post = "select * from post_like where post_num = $book_mark_post[post_num] and post_like_category = '$category'";
        $res_post_like = mysqli_query($conn, $sql_post);
        $post_like_count = mysqli_num_rows($res_post_like); 

        
        // ***** 게시글 데이터 최종 합치기 *****
        // 게시글 데이터 + 게시글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
        $data = [
            'post_num' => $post_info['post_num'], // 게시글 고유번호
            'user_num' => $post_info['user_num'], // 게시글 작성자(사용자) 고유번호
            'user_nickname' => $user_info['user_nickname'], // 게시글 작성자(사용자) 닉네임 (추가한 유저정보)
            'user_profile_image' => $user_info['profile_image'], // 게시글 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
            'post_title' => $post_info['post_title'], // 게시글 제목
            'post_content' => $post_info['post_content'], // 게시글 내용
            'post_category' => $post_info['post_category'], // 게시물 카테고리
            'post_images' => $post_info['post_img_path'], // 게시글 이미지 URL들
            'post_regtime' => $post_info['post_regtime'], // 게시글 등록날짜
            'post_view_count' => $post_info['post_view_count'], // 게시글 조회수
// 이거는 위에서 post_num로 따로 조회해야할듯?            
            'bookmark_check' => $bookmark_info['bookmark_check'], // 게시글 북마크 체크여부 (한번도 북마크 버튼을 누른적이 없다면 null값을 전달한다)
            'post_like_check' => $post_like_info['post_like_check'], // 게시글 좋아요 체크여부 (한번도 좋아요 버튼을 누른적이 없다면 null값을 전달한다)
            'post_comment_count' => $comment_total, // 게시글 댓글 총 개수
            'post_like_count' => $post_like_count // 게시글 좋아요 총 개수
        ]; 
        array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시글에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
	}

	mysqli_close($conn); // DB 종료.

    echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
?>