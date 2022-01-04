<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    // $userNum = $_POST["userNum"]; // 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    // $resultNum = (int)$userNum;
    $search_keyword = $_GET["search_keyword"]; // 검색 키워드
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 유저에대한 모든 데이터를 담을 배열

    // 멤버테이블에서 유저 닉네임에 검색 키워드가 포함되어 있는 유저의 데이터를 조회한다. (order by post_regtime -> 딱히 정렬기준은 필요 X)
    $sql = "SELECT * FROM members WHERE user_nickname LIKE '%$search_keyword%' LIMIT $limit offset $page";
    $res = mysqli_query($conn, $sql);
    $resultNum = mysqli_num_rows($res); // 검색결과 게시물 총 개수.
    
    // 받아온 고유번호의 유저가 존재하면
    if($resultNum > 0){

        // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장.
        while($user = $res->fetch_assoc()) { 
                
            $data = [
                'user_num' => $user['user_num'], // 유저 고유번호
                'user_nickname' => $user['user_nickname'], // 유저 닉네임
                'user_introduction' => $user['user_introduction'], // 유저 소개글
                'user_profile_image' => $user['profile_image'] // 유저 프로필 이미지 Uri
                // 'user_follower_num' => $user['follower_num'], // 유저 팔로워 숫자
                // 'user_following_num' => $user['following_num'] // 유저 팔로잉 숫자
            ]; 
            array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
        }

        mysqli_close($conn); // DB 종료.

        echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    }
    // 유저가 존재하지 않으면. 
    else{

        mysqli_close($conn); // DB 종료.

        //     //$result = array("result" => "error"); // $result["success"] = false;

        //     $response = array(); // response라는 배열 생성
        //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        //     echo json_encode($response);

        exit();
    }
?>