<?php 
    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 게시글 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$postNum;

        // 해당 고유번호를 가진 게시글이 존재하는지 조회. 
        $sql = "SELECT * FROM post WHERE post_num = $resultNum"; 
        $ret = mysqli_query($conn, $sql); 
        $exist = mysqli_num_rows($ret); 

        if($exist > 0){

            // 해당 게시글 고유번호를 가진 게시글 삭제.
            $sql = "DELETE FROM post WHERE post_num = $resultNum"; 
            $ret = mysqli_query($conn, $sql); 

            // 북마크 카테고리가 '게시글'이고 해당 게시글 고유번호로 등록된 북마크 데이터를 모두 삭제한다.
            $sql2 = "DELETE FROM bookmark WHERE post_num = $resultNum AND bookmark_category = '게시글'"; 
            $ret2 = mysqli_query($conn, $sql2); 

            // 해당 게시글 고유번호로 등록된 좋아요 데이터를 모두 삭제한다.
            $sql3 = "DELETE FROM post_like WHERE post_num = $resultNum AND post_like_category = '게시글'"; 
            $ret3 = mysqli_query($conn, $sql3); 


// 게시글에 달린 댓글/답글도 모두 삭제할 것.
            
            // 해당 고유번호를 가진 게시글에 대한 게시글 데이터,북마크,좋아요 모든 데이터 삭제 성공.
            if($ret && $ret2 && $ret3){

                // 게시글 이미지들이 담겨있는 폴더명
                $postImageDirName = $postNum.'_post_images';
        
                $delete_path = "postImages/$postImageDirName";

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


                // // 북마크 카테고리가 '집구경게시글'이고 해당 게시글 고유번호로 등록된 북마크 데이터를 모두 삭제한다.
                // $sql2 = "DELETE FROM bookmark WHERE post_num = $resultNum AND bookmark_category = '집구경게시글'"; 
                // $ret2 = mysqli_query($conn, $sql2); 

                // // 해당 게시글 고유번호로 등록된 좋아요 데이터를 모두 삭제한다.
                // $sql3 = "DELETE FROM post_like WHERE post_num = $resultNum"; 
                // $ret3 = mysqli_query($conn, $sql3); 
                
                // // 게시글 북마크와 좋아요 모두 삭제 성공.
                // if($ret2 && $ret3){

                $response = array(); // response라는 배열 생성
                // $response = mysqli_fetch_array($ret2); // 조회된 값이 존재하므로 그 값들의 response 배열에 담는다.
                $response["success"] = true; 
                echo json_encode($response);
            
                exit();
                // }
                // // 게시글 북마크 삭제 실패
                // else{
                //     $response = array(); // response라는 배열 생성
                //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
                
                //     echo json_encode($response);
                //     exit();
                // }

            }
            // DB에서 게시글 삭제 실패
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