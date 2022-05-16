<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $commentNum = $_POST["commentNumStr"]; // 댓글/대댓글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $commentNum = (int)$commentNum;

    $parentNum = $_POST["parentNumStr"]; // 댓글/대댓글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $parentNum = (int)$parentNum;
    

    // 해당 고유번호를 가진 댓글/대댓글 존재하는지 조회. 
    $sql = "SELECT * FROM comments WHERE comment_num = $commentNum"; 
    $ret = mysqli_query($conn, $sql);
    $comment_info = mysqli_fetch_assoc($ret); 

    // 댓글인 경우.
    if($commentNum == $parentNum){

        // 답글이 1개라도 있는지 조회.
        $sql = "SELECT * FROM comments WHERE parent = $parentNum"; 
        $ret = mysqli_query($conn, $sql);
        $exist = mysqli_num_rows($ret); 

        // ※ 이미 댓글은 하나 존재하므로 답글이 한개라도 존재하는 경우는 $exist가 2 이상인 경우이다.
        if($exist > 1){

            // 삭제하려는 댓글에 대한 답글이 한개이상 존재하므로 (1) 삭제유무'2'로 변경하고, (2) 일단 이 경우 클라이언트에게 댓글 내용을 '삭제된 댓글입니다.'로 전달한다.
            $sql = "UPDATE comments SET comment_delete=2 WHERE comment_num = $commentNum"; 
            $ret1 = mysqli_query($conn, $sql); 

            // 변경 성공.
            if($ret1){

                $response = array(); // response라는 배열 생성
                $response["success"] = true;
                $response["check"] = "답글존재"; // 댓글 삭제시 답글이 하나이상 존재하는 경우 구분.

                echo json_encode($response);
            
                exit();
            }
            // 변경 실패.
            else{

                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                echo json_encode($response);

                exit();
            }

        }
        // 답글 존재(X) -> 그대로 삭제 (※ 원래는 그냥 삭제유무 컬럼만 2로 변경하는게 맞지만 나는 그냥 삭제한다. 따로 관리자 페이지만드는 것은 기획에 없기때문에. ※)
        else{

            // 해당 댓글 고유번호를 가진 댓글 삭제.
            $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
            $ret1 = mysqli_query($conn, $sql); 

            // 해당 댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
            $sql2 = "DELETE FROM comment_like WHERE comment_num = $commentNum"; 
            $ret2 = mysqli_query($conn, $sql2); 
            
            // 해당 고유번호를 가진 댓글에 대한 댓글 데이터,좋아요 모든 데이터 삭제 성공.
            if($ret1 && $ret2){

                $response = array(); // response라는 배열 생성
                
                $response["success"] = true;
                $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
                echo json_encode($response);
            
                exit();
            }
            else
            {
                $response = array(); // response라는 배열 생성

                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                echo json_encode($response);

                exit();
            }
        }
    }
// 답글인 경우.
    else{

        // 부모 고유번호를 통해 댓글에 답글이 삭제하려는 답글하나 뿐인지 조회한다.
        $sql = "SELECT * FROM comments WHERE parent = $parentNum"; 
        $ret = mysqli_query($conn, $sql);
        $exist = mysqli_num_rows($ret); 

        // 삭제하는 답글이 마지막 답글인 경우. (※ 1(댓글) + 1(답글) = 2 이미므로 $exist값이 2일때 삭제하려는 답글이 마지막 답글인 것을 알 수 있다.)
        if($exist == 2){

            // (1) 일단 답글 먼저 삭제한다. (해당 답글 고유번호를 가진 답글 삭제.)
            $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
            $ret1 = mysqli_query($conn, $sql); 

            // 해당 답글 고유번호로 등록된 '좋아요 데이터'를 삭제한다.
            $sql2 = "DELETE FROM comment_like WHERE comment_num = $commentNum"; 
            $ret2 = mysqli_query($conn, $sql2); 
            
            // 해당 고유번호를 가진 댓글/대댓글에 대한 댓글/대댓글 데이터,좋아요 모든 데이터 삭제 성공.
            if($ret1 && $ret2){

        // 마지막 답글을 삭제하는 상황이므로 답글 삭제 후에 삭제된 댓글도 함께 삭제해준다.
                
                // (2) 댓글이 이미 삭제된 댓글(삭제유무 값이 2)이고, 그 댓글의 마지막 답글이 삭제 되었으므로 
                //     답글 삭제와 동시에 바로 삭제된 댓글도 삭제해 준다.
                $sql = "DELETE FROM comments WHERE comment_num = $parentNum AND parent = $parentNum AND comment_delete=2"; 
                $ret1 = mysqli_query($conn, $sql); 

                // 해당 댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다. (댓글이므로 commentNum == parentNum기 때문)
                $sql2 = "DELETE FROM comment_like WHERE comment_num = $parentNum"; 
                $ret2 = mysqli_query($conn, $sql2); 
                    
                // 해당 고유번호를 가진 댓글에 대한 댓글 데이터,좋아요 모든 데이터 삭제 성공.
                if($ret1 && $ret2){
                
                    $response = array(); // response라는 배열 생성
            
                    $response["success"] = true; 
                    $response["check"] = "마지막답글삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
                    echo json_encode($response);
                
                    exit();
                }
                else{

                    $response = array(); // response라는 배열 생성

                    $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                    echo json_encode($response);

                    exit();

                }
            }
            else{

                $response = array(); // response라는 배열 생성

                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                echo json_encode($response);

                exit();
            }
    
        } 
        // 삭제된 댓글의 마지막 답글이 아니므로 그냥 답글만 삭제한다.
        else{

            // 답글 삭제
            $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
            $ret1 = mysqli_query($conn, $sql); 

            // 해당 답글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
            $sql2 = "DELETE FROM comment_like WHERE comment_num = $commentNum"; 
            $ret2 = mysqli_query($conn, $sql2); 
            
            // 해당 고유번호를 가진 댓글/대댓글에 대한 댓글/대댓글 데이터,좋아요 모든 데이터 삭제 성공.
            if($ret1 && $ret2){

                $response = array(); // response라는 배열 생성
                
                $response["success"] = true; 
                $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
                echo json_encode($response);
            
                exit();
            }
            // DB에서 게시물 삭제 실패
            else{

                $response = array(); // response라는 배열 생성
                $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                echo json_encode($response);

                exit();
            }
        }
    } 
    // // 조회 실패
    // }else{
    //     $response = array(); // response라는 배열 생성
    //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
    
    //     echo json_encode($response);
    //     exit();
    // }
?>