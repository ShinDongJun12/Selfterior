<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    // 각각의 회원정보를 받는다. => 어떤 소셜 로그인으로 가입된 계정인지 확인하기 위해 소셜로그인시 고유 식별자를 user_unique_identifier에 저장한다.
    $userNum = $_POST["userNum"]; // 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$userNum;

    // 현재 가입된 회원정보 바로 조회. (닉네임은 중복X 이므로)   
    $sql = "SELECT * FROM members WHERE user_num = $resultNum"; 
    // $sql = "SELECT *, h.count(*), b.count(*), p.count(*) FROM members AS m JOIN house_tour_post AS h ON h.user_num = m.user_num JOIN bookmark AS b ON b.user_num = m.user_num JOIN post_like AS p ON p.user_num = m.user_num WHERE user_num = $resultNum"; 
    // $sql = "SELECT *, h.count(*), b.count(*), p.count(*) FROM members AS m JOIN house_tour_post AS h ON h.user_num = m.user_num JOIN bookmark AS b ON b.user_num = m.user_num JOIN post_like AS p ON p.user_num = m.user_num WHERE user_num = $resultNum"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    if($exist > 0){
    
        $response = array(); // response라는 배열 생성
        // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
        $response = mysqli_fetch_array($ret); // members테이블에서 가져올 수 있는 회원정보는 다 가져옴. (유저 소개글, 팔로워 수, 팔로잉 수)

    // 회원이 작성한 집구경 게시물 겟수
        $sql = "SELECT * FROM house_tour_post WHERE user_num = $resultNum"; 
        $ret = mysqli_query($conn, $sql); 

        // 해당 회원이 작성한 집구경 게시물 총 개수 조회 성공.
        if($ret){

            $exist2 = mysqli_num_rows($ret); 
            $response["house_tour_post_count"] = $exist2; // 회원이 작성한 집구경 게시물 총 개수 배열에 저장.
            
        // 회원이 북마크한 게시물 총 개수 조회.
            $sql = "SELECT bookmark_num FROM bookmark WHERE user_num = $resultNum AND bookmark_check = 2"; 
            $ret = mysqli_query($conn, $sql);  

            // 해당 회원이 북마크한 게시물 총 개수 조회 성공.
            if($ret){

                $exist2 = mysqli_num_rows($ret);
                $response["bookmark_count"] = $exist2; // 회원이 북마크한 게시물 총 개수 배열에 저장.


            // 회원이 좋아요한 게시물 총 개수 조회.
                $sql = "SELECT post_like_num FROM post_like WHERE user_num = $resultNum AND post_like_check = 2"; 
                $ret = mysqli_query($conn, $sql);  

                // 해당 회원이 좋아요한 게시물 총 개수 조회 성공.
                if($ret){

                    $exist2 = mysqli_num_rows($ret);
                    $response["post_like_count"] = $exist2; // 회원이 좋아요한 게시물 총 개수 배열에 저장.

                // 회원 나의 질문 총 개수 조회.
                    $sql = "SELECT * FROM qna_post WHERE user_num = $resultNum"; 
                    $ret = mysqli_query($conn, $sql);  

                // 회원 나의 답변 총 개수 조회.
                    $sql2 = "SELECT post_num FROM (SELECT DISTINCT post_num, MAX(regtime) FROM comments WHERE user_num = $resultNum AND category = '질문과답변' GROUP BY post_num ORDER BY MAX(regtime) DESC)P";
                    $ret2 = mysqli_query($conn, $sql2); 

                    // 조회 성공.
                    if($ret && $ret2){

                        $exist2 = mysqli_num_rows($ret);
                        $exist3 = mysqli_num_rows($ret2);

                        $total = $exist2 + $exist3; // 최종 개수

                        $response["qna_count"] = $total; // 회원의 질문,답변 총 개수 배열에 저장.


                        // 중고거래 게시물 총 개수 조회.
                        $sql = "SELECT * FROM used_transaction_post WHERE user_num = $resultNum"; 
                        $ret = mysqli_query($conn, $sql);  

                        // 조회 성공.
                        if($ret){

                            $exist2 = mysqli_num_rows($ret);
                            $response["used_transaction_count"] = $exist2; // 중고거래 게시물 총 개수 배열에 저장.

                            // ★현재 여기가 최종 결과★
                            $response["success"] = true; 
                            //$response = array ('success'=>true,'user_num'=>$response[user_num],'user_email'=>$response[user_email],'user_pass'=>$response[user_pass],'user_nickname'=>$response[user_nickname],'platform_type'=>$response[platform_type],'user_regtime'=>$response[user_regtime],'profile_image'=>$response[profile_image],'user_unique_identifier'=>$response[user_unique_identifier]);
                            echo json_encode($response);
                            //echo json_encode($response, JSON_UNESCAPED_UNICODE);
                            exit();

                        }
                        // 조회 실패.
                        else{
                            
                            $response = array(); // response라는 배열 생성
                            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                        
                            echo json_encode($response);
                            exit();
                        }

                    }
                    // 조회 실패.
                    else{
                        
                        $response = array(); // response라는 배열 생성
                        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                    
                        echo json_encode($response);
                        exit();
                    }

                }
                // 조회 실패.
                else{
                    
                    $response = array(); // response라는 배열 생성
                    $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                
                    echo json_encode($response);
                    exit();
                }
                
            }
            // 조회 실패.
            else{
                
                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
            
                echo json_encode($response);
                exit();
            }
            
        }
        // 조회 실패.
        else{
            
            $response = array(); // response라는 배열 생성
            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
            echo json_encode($response);
            exit();
        }

    // 조회 실패
    }else{
        $response = array(); // response라는 배열 생성
        $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
        echo json_encode($response);
        exit();
    }
?>