<?php
	include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $post_num = $_GET["post_num"]; // 중고거래 게시물 고유번호.

    $post_data_array = array(); // 중고거래 게시물 데이터들을 담을 배열.

    // 게시물 고유번호로 게시물 데이터를 조회한다.
    $sql = "SELECT * FROM used_transaction_post WHERE post_num = $post_num";
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
            
            
            // ***** 게시물 데이터 최종 합치기 *****
            // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
            $data = [
                'post_num' => $post['post_num'], // 게시물 고유번호
                'user_num' => $post['user_num'], // 게시물 작성자(사용자) 고유번호
                'post_title' => $post['post_title'], // 게시물 제목
                'item_category' => $post['item_category'], // 아이템 카테고리
                'item_price' => $post['item_price'], // 아이템 가격
                'sale_address' => $post['sale_address'], // 판매자 주소
                'post_content' => $post['post_content'], // 게시물 내용
                'post_imgPath' => $post['post_imgPath'], // 게시물 이미지 전체 URL
                'post_regtime' => $post['post_regtime'], // 게시물 등록날짜
                'transaction_status' => $post['transaction_status'], // 게시물 거래상태.
                'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                'user_profile_image' => $user_info['profile_image'] // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
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