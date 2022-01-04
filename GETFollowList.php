<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $criteri_user_num = $_GET["criteri_user_num"]; // 기준이 되는 유저 고유번호
    $follow_select_num = $_GET["follow_select_num"]; // 팔로워 or 팔로잉 선택 체크 번호
    $limit = $_GET["limit"]; // 가져올 갯수
    $page = $_GET["page"]; // 시작 값
    $page = ($page-1)*$limit; // 0 , 10 , 20 , 30 ......

    $post_data_array = array(); // 리사이클러뷰에 보여줄 한 유저에대한 모든 데이터를 담을 배열

// ※ 팔로워 or 팔로잉 선택 체크 번호값에 따라 쿼리문 분류 ※
    
// 팔로워 목록을 조회하는 경우.
    if($follow_select_num == 0){

        // target_user_num가 기준 유저고유 번호인 경우의 user_num를 조회하며, 정렬기준은최근에 팔로우 한 시간으로한다.
        $sql = "SELECT user_num FROM follow WHERE target_user_num = $criteri_user_num ORDER BY follow_datetime DESC LIMIT $limit offset $page";

        $res = mysqli_query($conn, $sql);
        $resultNum = mysqli_num_rows($res); // 검색결과 게시물 총 개수.
        
        // 기준이되는 유저 고유번호로 필로워 유저가 존재하면
        if($resultNum > 0){

            // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장.
            while($user = $res->fetch_assoc()) { 

                // ***** 현재 접속한 유저 팔로워들의 고유 번호로 유저 데이터를 조회한다. *****
                // (유저 닉네임, 유저 소개글, 유저 프로필 이미지)
                $sql_user = "SELECT * FROM members WHERE user_num = $user[user_num]";
                $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                $user_info = mysqli_fetch_assoc($res_user);

                $data = [
                    'user_num' => $user['user_num'], // 유저 고유번호
                    'user_nickname' => $user_info['user_nickname'], // 유저 닉네임
                    'user_introduction' => $user_info['user_introduction'], // 유저 소개글
                    'user_profile_image' => $user_info['profile_image'] // 유저 프로필 이미지 Uri
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

    }
// 팔로잉 목록을 조회하는 경우.
    else{

        // user_num가 기준 유저고유 번호인 경우의 target_user_num를 조회하며, 정렬기준은최근에 팔로우 한 시간으로한다.
        $sql = "SELECT target_user_num FROM follow WHERE user_num = $criteri_user_num ORDER BY follow_datetime DESC LIMIT $limit offset $page";

        $res = mysqli_query($conn, $sql);
        $resultNum = mysqli_num_rows($res); // 검색결과 게시물 총 개수.
        
        // 기준이되는 유저 고유번호로 필로워 유저가 존재하면
        if($resultNum > 0){

            // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장.
            while($user = $res->fetch_assoc()) { 

                // ***** 현재 접속한 유저 팔로워들의 고유 번호로 유저 데이터를 조회한다. *****
                // (유저 닉네임, 유저 소개글, 유저 프로필 이미지)
                $sql_user = "SELECT * FROM members WHERE user_num = $user[target_user_num]";
                $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                $user_info = mysqli_fetch_assoc($res_user);

                $data = [
                    'user_num' => $user['target_user_num'], // 유저 고유번호
                    'user_nickname' => $user_info['user_nickname'], // 유저 닉네임
                    'user_introduction' => $user_info['user_introduction'], // 유저 소개글
                    'user_profile_image' => $user_info['profile_image'] // 유저 프로필 이미지 Uri
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
    }
?>