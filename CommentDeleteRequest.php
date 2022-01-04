<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $commentNum = $_POST["commentNumStr"]; // 댓글/대댓글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $commentNum = (int)$commentNum;

    $parentNum = $_POST["parentNumStr"]; // 댓글/대댓글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $parentNum = (int)$parentNum;
    
    $comment_imgPath; // ??

        // 해당 고유번호를 가진 댓글/대댓글 존재하는지 조회. 
        $sql = "SELECT * FROM comments WHERE comment_num = $commentNum"; 
        $ret = mysqli_query($conn, $sql);
        // $exist = mysqli_num_rows($ret); 
        $comment_info = mysqli_fetch_assoc($ret); 

        // // 댓글 또는 답글이 존재하는경우.
        // if($exist > 0){

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
                        $response["commentContent"] = "'삭제된 댓글 입니다.'"; // 변경해줄 댓글 내용.

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

                    // 댓글 이미지가 Null값이면 따로 폴더 지우는 것 없이 DB에서 삭제.
                    if(empty($comment_info['comment_imgPath'])){

                        // 해당 댓글/대댓글 고유번호를 가진 댓글/대댓글 삭제.
                        $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
                        $ret1 = mysqli_query($conn, $sql); 

                        // 해당 댓글/대댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
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
                        else
                        {
                            $response = array(); // response라는 배열 생성

                            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                            echo json_encode($response);
    
                            exit();
                        }

                    }
                    // 댓글 이미지가 존재하면 해당 이미지폴더 삭제.
                    else{

                        // 댓글 이미지들이 담겨있는 폴더명
                        $commentImageDirName = $commentNum.'_comment_images';
                        $delete_path = "commentImages/$commentImageDirName";

                        // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                        // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                        // 디렉토리 삭제 함수. 
                        function rmdir_ok($dir) {
                            $dirs = dir($dir);
                            while(false !== ($entry = $dirs->read())) {
                                if(($entry != '.') && ($entry != '..')) {
                                    if(is_dir($dir.'/'.$entry)) {
                                        rmdir_ok($dir.'/'.$entry);
                                    } else {
                                        @unlink($dir.'/'.$entry);
                                    }
                                }
                            }
                            $dirs->close();
                            @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                        }
                    
                        rmdir_ok($delete_path); // 폴더 삭제하기.


                        // 해당 댓글/대댓글 고유번호를 가진 댓글/대댓글 삭제.
                        $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
                        $ret1 = mysqli_query($conn, $sql); 

                        // 해당 댓글/대댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
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
                        else
                        {
                            $response = array(); // response라는 배열 생성

                            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                            echo json_encode($response);
    
                            exit();
                        }
                    
                    }
                }
            }
        // 답글인 경우.
            else{

                // 부모 고유번호를 통해 댓글에 답글이 삭제하려는 답글하나 뿐인지 조회한다.
                $sql = "SELECT * FROM comments WHERE parent = $parentNum"; 
                $ret = mysqli_query($conn, $sql);
                $exist = mysqli_num_rows($ret); 

        // ※ 1(댓글) + 1(답글) = 2 이미므로 $exist값이 2일때 삭제하려는 답글이 마지막 답글인 것을 알 수 있다.
                if($exist == 2){

                    // 답글 이미지가 Null값이면 바로 답글을 삭제한다.
                    if(empty($comment_info['comment_imgPath'])){

                        // (1) 일단 답글 먼저 삭제한다. (해당 답글 고유번호를 가진 답글 삭제.)
                        $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
                        $ret1 = mysqli_query($conn, $sql); 

                        // 해당 댓글/대댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
                        $sql2 = "DELETE FROM comment_like WHERE comment_num = $commentNum"; 
                        $ret2 = mysqli_query($conn, $sql2); 
                        
                        // 해당 고유번호를 가진 댓글/대댓글에 대한 댓글/대댓글 데이터,좋아요 모든 데이터 삭제 성공.
                        if($ret1 && $ret2){

                            // 상단의 $comment_info는 답글 정보를 담고 있으므로 여기서 다시 삭제할 댓글의 이미지 데이터를 조회한다.
                            $sql = "SELECT * FROM comments WHERE comment_num = $parentNum AND parent = $parentNum AND comment_delete=2"; 
                            $ret = mysqli_query($conn, $sql);
                            $delete_comment_info = mysqli_fetch_assoc($ret); 

                            // 댓글 이미지가 Null값이면 바로 댓글을 삭제한다.
                            if(empty($delete_comment_info['comment_imgPath'])){
                            
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
                                    $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
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
                        // ★ 이미지가 있는 답글 삭제 시. (답글 삭제시 폴더명 바꿔줄 것)    
                            else
                            {
                                // 답글 이미지들이 담겨있는 폴더명 
                                // (※폴더명도 $commentNum은 답글이므로 $parentNum해주어야 올바르게 삭제하려는 댓글의 이미지 폴더를 삭제할 수 있다.) 
                                $commentImageDirName = $parentNum.'_comment_reply_images';
                                $delete_path = "commentImages/$commentImageDirName";

                                // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                                // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                                // 디렉토리 삭제 함수. 
                                function rmdir_ok($dir) {
                                    $dirs = dir($dir);
                                    while(false !== ($entry = $dirs->read())) {
                                        if(($entry != '.') && ($entry != '..')) {
                                            if(is_dir($dir.'/'.$entry)) {
                                                rmdir_ok($dir.'/'.$entry);
                                            } else {
                                                @unlink($dir.'/'.$entry);
                                            }
                                        }
                                    }
                                    $dirs->close();
                                    @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                                }
                            
                                rmdir_ok($delete_path); // 폴더 삭제하기.


                                // 폴더를 삭제했으님 댓글을 삭제한다.
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
                                    $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
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
                        }
                        else{

                            $response = array(); // response라는 배열 생성

                            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                            echo json_encode($response);
    
                            exit();
                        }
                    }
                    // 답글 이미지가 존재하면 해당 이미지폴더 삭제.
                    else{

                        // 답글 이미지들이 담겨있는 폴더명
                        $postImageDirName = $commentNum.'_comment_reply_images';
                        $delete_path = "commentImages/$postImageDirName";

                        // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                        // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                        // 디렉토리 삭제 함수. 
                        function rmdir_ok($dir) {
                            $dirs = dir($dir);
                            while(false !== ($entry = $dirs->read())) {
                                if(($entry != '.') && ($entry != '..')) {
                                    if(is_dir($dir.'/'.$entry)) {
                                        rmdir_ok($dir.'/'.$entry);
                                    } else {
                                        @unlink($dir.'/'.$entry);
                                    }
                                }
                            }
                            $dirs->close();
                            @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                        }
                    
                        rmdir_ok($delete_path); // 폴더 삭제하기.

                        // 답글 이미지 폴더 삭제 후 답글 먼저 삭제한다.
                        // (1) 일단 답글 먼저 삭제한다. (해당 답글 고유번호를 가진 답글 삭제.)
                        $sql = "DELETE FROM comments WHERE comment_num = $commentNum"; 
                        $ret1 = mysqli_query($conn, $sql); 

                        // 해당 댓글/대댓글 고유번호로 등록된 '좋아요 데이터'를 모두 삭제한다.
                        $sql2 = "DELETE FROM comment_like WHERE comment_num = $commentNum"; 
                        $ret2 = mysqli_query($conn, $sql2); 
                        
                        // 해당 고유번호를 가진 댓글/대댓글에 대한 댓글/대댓글 데이터,좋아요 모든 데이터 삭제 성공.
                        if($ret1 && $ret2){

                            // 상단의 $comment_info는 답글 정보를 담고 있으므로 여기서 다시 삭제할 댓글의 이미지 데이터를 조회한다.
                            $sql = "SELECT * FROM comments WHERE comment_num = $parentNum AND parent = $parentNum AND comment_delete=2"; 
                            $ret = mysqli_query($conn, $sql);
                            $delete_comment_info = mysqli_fetch_assoc($ret); 

                            // 댓글 이미지가 Null값이면 바로 댓글을 삭제한다.
                            if(empty($delete_comment_info['comment_imgPath'])){
                            
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
                                    $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
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
                            else
                            {
                                // 답글 이미지들이 담겨있는 폴더명 
                                // (※폴더명도 $commentNum은 답글이므로 $parentNum해주어야 올바르게 삭제하려는 댓글의 이미지 폴더를 삭제할 수 있다.) 
                                $postImageDirName = $parentNum.'_comment_reply_images';
                                $delete_path = "commentImages/$postImageDirName";

                                // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                                // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                                // 디렉토리 삭제 함수. 
                                function rmdir_ok($dir) {
                                    $dirs = dir($dir);
                                    while(false !== ($entry = $dirs->read())) {
                                        if(($entry != '.') && ($entry != '..')) {
                                            if(is_dir($dir.'/'.$entry)) {
                                                rmdir_ok($dir.'/'.$entry);
                                            } else {
                                                @unlink($dir.'/'.$entry);
                                            }
                                        }
                                    }
                                    $dirs->close();
                                    @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                                }
                            
                                rmdir_ok($delete_path); // 폴더 삭제하기.


                                // 폴더를 삭제했으님 댓글을 삭제한다.
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
                                    $response["check"] = "삭제"; // 삭제된 댓글에 더 이상 답글이 남아있지 않아 삭제된 댓글 또한 삭제 처리함. 
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
                        }
                        else{

                            $response = array(); // response라는 배열 생성

                            $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                            echo json_encode($response);
    
                            exit();
                        }

                    }   
                } 
                // 삭제된 댓글의 마지막 답글이 아니므로 그냥 답글만 삭제한다.
                else{

                     // 답글 이미지가 Null값이면 따로 폴더 지우는 것 없이 바로 답글 삭제
                     if(empty($comment_info['comment_imgPath'])){

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
                    // 답글 이미지가 존재하면 해당 이미지폴더 삭제.
                    else{

                        // 답글 이미지들이 담겨있는 폴더명
                        $postImageDirName = $commentNum.'_comment_reply_images';
                        $delete_path = "commentImages/$postImageDirName";

                        // 디렉토리 안에 파일이나 디렉토리가 존재한다면 삭제할 수 없다. 
                        // 그러므로 재귀호출을 이용하여 하위 디렉토리를 일괄 삭제한 다음 해당 디렉토리를 삭제해야 한다.
                        // 디렉토리 삭제 함수. 
                        function rmdir_ok($dir) {
                            $dirs = dir($dir);
                            while(false !== ($entry = $dirs->read())) {
                                if(($entry != '.') && ($entry != '..')) {
                                    if(is_dir($dir.'/'.$entry)) {
                                        rmdir_ok($dir.'/'.$entry);
                                    } else {
                                        @unlink($dir.'/'.$entry);
                                    }
                                }
                            }
                            $dirs->close();
                            @rmdir($dir); // 마지막에 해당 경로 폴더까지 삭제.
                        }
                    
                        rmdir_ok($delete_path); // 폴더 삭제하기.

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
            } 
    
        // // 조회 실패
        // }else{
        //     $response = array(); // response라는 배열 생성
        //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
        //     echo json_encode($response);
        //     exit();
        // }
?>