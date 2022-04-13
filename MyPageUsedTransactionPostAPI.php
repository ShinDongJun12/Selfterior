<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $login_user_num = $_GET["login_user_num"]; // 현재 접속한 회원 고유번호
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......
    $bookmark_category = $_GET["bookmark_category"]; // 북마크 카테고리
    $postStatus = $_GET["postStatus"]; // 거래상태 (거래완료 or 거래미완료 -> 예외처리 기준은 거래완료)


    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에대한 모든 데이터를 담을 배열


    // 거래완료 상태인 중고거래 게시물을 조회하는 경우.
    if(strcmp($postStatus, "거래완료") == 0){

        $sql = "SELECT * FROM used_transaction_post WHERE user_num = $login_user_num AND transaction_status = '$postStatus' order by post_regtime desc limit $limit offset $page";
    }
    // 거래중 or 예약중인 중고거랜 게시물을 조회하는 경우.
    else{

        $sql = "SELECT * FROM used_transaction_post WHERE user_num = $login_user_num AND transaction_status NOT IN('거래완료') order by post_regtime desc limit $limit offset $page";
    }

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


            // ***** 북마크 클릭여부 조회 *****
            // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호, 북마크 카테고리 값들로 현재 접속중인 회원의 해당 게시물 북마크 체크여부 데이터를 조회한다. $post_category
            $sql_user2 = "select bookmark_check from bookmark where user_num = $login_user_num and post_num = $post[post_num] and bookmark_category = '$bookmark_category'";
            $res_user2 = mysqli_query($conn, $sql_user2); 
            $num_row = mysqli_num_rows($res_user2); 

            // 사용자가 해당 게시물에 대해 북마크 버튼을 한번이라도 누른적이 있다면.
            if($num_row > 0){
                $post_info = array(); // post_info 배열 생성
                $post_info = mysqli_fetch_assoc($res_user2); // bookmark_check값을 받아온다.
            }
            // 한번도 북마크 버튼을 누른적이 없으면.
            else{

                $post_info = array(); // post_info 배열 생성
                $post_info['bookmark_check'] = 0; // bookmark_check값에 0값을 넣는다.
            }


            // ***** 게시물 관심(북마크) 총 개수 조회 *****
            // 현재 게시물 고유번호와 카테고리값, 북마크 체크 번호가 2를 통해 현재 게시물의 관심(북마크) 총 개수를 조회한다.
            $sql_user2 = "select * from bookmark where post_num = $post[post_num] and bookmark_category = '$bookmark_category' and bookmark_check = 2";
            $res_interest = mysqli_query($conn, $sql_user2);
            $interest_total = mysqli_num_rows($res_interest); 
            
            
            // ***** 게시물 데이터 최종 합치기 *****
            // 중고거래 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
            $data = [
                'post_num' => $post['post_num'], // 게시물 고유번호
                'user_num' => $post['user_num'], // 게시물 작성자(사용자) 고유번호
                'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                'user_profile_image' => $user_info['profile_image'], // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                'post_title' => $post['post_title'], // 게시물 제목
                'item_category' => $post['item_category'], // 아이템 카테고리
                'item_price' => $post['item_price'], // 아이템 가격
                'sale_address' => $post['sale_address'], // 판매자 주소
                'post_content' => $post['post_content'], // 게시물 내용
                'post_imgPath' => $post['post_imgPath'], // 게시물 이미지 전체 URL
                'post_regtime' => $post['post_regtime'], // 게시물 등록날짜
                'post_view_count' => $post['post_view_count'], // 게시물 조회수
                'transaction_status' => $post['transaction_status'], // 거래상태
                'total_interest_count' => $interest_total, // 게시물 관심 총 개수
                'bookmark_check' => $post_info['bookmark_check'] // 게시물 북마크 체크여부 (한번도 북마크 버튼을 누른적이 없다면 null값을 전달한다)
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