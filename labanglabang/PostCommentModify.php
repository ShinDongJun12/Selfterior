<?php

    // ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $commentNum = $_POST["commentNum"]; // 댓글/답글 고유번호.
    $commentNumResult = (int)$commentNum; // int형으로 변환

    $commentContent = $_POST["commentContent"]; // 댓글/대댓글 내용.
    $postCategory = $_POST["postCategory"]; // 게시판 카테고리.

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.

    // 클라이언트로 보낼 응답 배열
    $comment_data_array = array(); // 작성한 댓글/대댓글의 정보 및 작성자 정보(닉네임, 프로필 이미지)를 담을 배열.

    
    // // (1) 게시판 카테고리로 이미지 처리할지 말지 구분한다.
    // if($postCategory == "집구경"){

        // 이미지 처리 필요 (X)

        // 댓글/답글 내용 수정.
        $sql = "UPDATE comments SET content='$commentContent' WHERE comment_num = $commentNumResult";
        $res = mysqli_query($conn, $sql);

        // 내용 수정 성공
        if($res) {
            
            // (2) 수정된 댓글/답글 정보 클라이언트에 넘겨준다.
            // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
            $sql = "SELECT * FROM comments WHERE comment_num = $commentNumResult"; 
            $res = mysqli_query($conn, $sql);
            
            while($comment = $res->fetch_assoc()) { 
                
                // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
                // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
                $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
                $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                $user_info = mysqli_fetch_assoc($res_user);
                
                // ***** 게시물 데이터 최종 합치기 *****
                // 게시물 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
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
                    // 'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
                    'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
                ]; 
                array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
            }

            mysqli_close($conn); // DB 종료.

            echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

        }
        // 내용 수정실패.
        else{
            
           // (X)
           // 포스트 수정 실패
            $result = array("result" => "error"); // $result["success"] = false;
            echo "error";
        }

    // }
    // // 질문과 답변 댓글/대댓글인 경우.
    // else{

    //     // 이미지 처리 필요 (O)

    //     // (2) 댓글인지 답글이지 구분한다. (※ 이미지를 저장하는 파일명이 다르기 때문이다. ※)
    //     $sql = "SELECT parent FROM comments WHERE comment_num = $commentNumResult"; 
    //     $res = mysqli_query($conn, $sql);
    //     $check = mysqli_fetch_assoc($res); // 결과값 받아온다.

    //   // 댓글인 경우 (댓글/답글 고유번호와 부모번호가 같은경우)
    //     if($commentNumResult == $check['parent']){


    //         // 게시물별 이미지 디렉토리 생성.
    //         $dirBaseName = "comment_images";
    //         $commentDirName = $commentNum.'_'.$dirBaseName; // ex) 23_comment_images
    //         $commentUploadDir = "commentImages"; // 댓글 이미지 저장할 폴더명
    //         $commentDirPath = "$commentUploadDir/$commentDirName"; // 댓글이 수정 될 때 해당 댓글의 이미지들을 저장할 폴더경로.

    //         // umask : 권한을 설정할 때 수동적으로 권한을 주지 않고 파일이나 디렉토리가 생성됨가 동시에 지정된 권한이 주어주도록 함
    //         // 리눅스 umask 설정값이 0022로 잡혀 있어서 0777로 디렉토리를 생성해도 0755값으로 나온 것이다.
    //         // 이를 해결하기 위해 umask값의 옵션을 변경해서 생성하니 해결 되었다.
    //         $oldumask = umask(0);

    //         // (3) $postDirPath 경로에 $postDirName 폴더가 존재하지 않으면. (is_dir가 조금더 빠르다고 한다.)
    //         if (!is_dir($commentDirPath)) {
                
    //             // 댓글 이미지를 저장할 폴더를 만들어준다.
    //             mkdir($commentDirPath, 0777, true); // 777권한으로 폴더 생성.

    //             // (4) 이미지가 1장이상 있을 때
    //             if($cntImage > 0) {

    //                 // 서버에 저장된 사진의 URL 리스트
    //                 $urlList = array(); 

    //                 $server = 'http://49.247.148.192/'; // 서버주소.
    //                 $uploadDir = $commentDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_post_images

    //                 // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //                 for($i=0; $i<$cntImage; $i++) { 

    //                     $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
    //                     $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                     $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                     $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                     $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                     // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
    //                     $name = $commentNum.'_'.$i.'.'.$type; //ex) 댓글 고유번호_1.jpg
    //                     $path = "$commentUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
    //                     move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                     $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                        
    //                     // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //                 }

    //                 // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //                 $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    //                 // 댓글 내용 수정.
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = '$urlList' WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 이미지가 없을 때. ----> ※. 수정시 이미지를 삭제한 채로(이미지가 포함되지 않은채로) 수정하게 되면 [] 값이 들어서 불러올 떄 에러가 발생한다. 
    //             //                                따라서 이런경우 comment_imgPath 값을 comment_imgPath의 Default 값(Null)으로 넣어준다. 
    //             else{

    //                 // 수정 시 기존 이미지를 삭제하였으므로 폴더도 마저 삭제해준다.
    //                 @rmdir($delete_path);  // 폴더 삭제

    //                 // 댓글 내용 수정. ( 컬럽에 다른 컬럼의 Dfault 값을 넣고 싶으면 => DEFAULT('컬럼명'))
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = DEFAULT WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 수정된 댓글 정보조회.
    //             if($res1) {

    //                 // (5) 수정된 댓글/답글 정보 클라이언트에 넘겨준다.

    //                 // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //                 $sql = "SELECT * FROM comments WHERE comment_num = $commentNumResult"; 
    //                 $res = mysqli_query($conn, $sql);
                    
    //                 while($comment = $res->fetch_assoc()) { 
                        
    //                     // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                     // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                     $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                     $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                     $user_info = mysqli_fetch_assoc($res_user);
                        
    //                     // ***** 게시물 데이터 최종 합치기 *****
    //                     // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                     $data = [
    //                         'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                         'post_num' => $comment['post_num'], // 게시물 고유번호
    //                         'user_num' => $comment['user_num'], // 작성자 고유번호
    //                         'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                         'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                         'content' => $comment['content'], // 내용
    //                         'category' => $comment['category'], // 카테고리
    //                         'parent' => $comment['parent'], // 부모 번호
    //                         'regtime' => $comment['regtime'], // 작성날짜
    //                         'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                         'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                     ]; 
    //                     array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //                 }

    //                 mysqli_close($conn); // DB 종료.

    //                 echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //             } else {
    //                 // 포스트 수정 실패
    //                 $result = array("result" => "error"); // $result["success"] = false;
    //                 echo "error3";
    //             }

    //         }
    //         // 이미지 폴더가 존재하면
    //         else{

    //             // 폴더의 이미지를 모두 지운다.

    //             // 댓글 이미지들이 담겨있는 폴더명
    //             $commentImageDirName = $commentNum.'_comment_images';
                            
    //             $delete_path = "commentImages/$commentImageDirName";

    //             // 디렉토리 안의 파일들 전부 삭제하는 함수. 
    //             function rmdir_files_ok($dir) {
    //                 $dirs = dir($dir);
    //                 while(false !== ($entry = $dirs->read())) {
    //                     if(($entry != '.') && ($entry != '..')) {
    //                         if(is_dir($dir.'/'.$entry)) {
    //                             rmdir_files_ok($dir.'/'.$entry);
    //                         } else {
    //                                 @unlink($dir.'/'.$entry);
    //                         }
    //                     }
    //                 }
    //                 $dirs->close(); // 파일 닫기
    //                 // @rmdir($dir);  // 마지막에 해당 경로 폴더까지 삭제.
    //             }
    //             rmdir_files_ok($delete_path); // 해당 게시물 파일 안의 이미지들 삭제하기.

    //             // (4) 이미지가 1장이상 있을 때
    //             if($cntImage > 0) {

    //                 // 서버에 저장된 사진의 URL 리스트
    //                 $urlList = array(); 

    //                 $server = 'http://49.247.148.192/'; // 서버주소.
    //                 $uploadDir = $commentDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_comment_images

    //                 // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //                 for($i=0; $i<$cntImage; $i++) { 

    //                     $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
    //                     $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                     $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                     $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                     $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                     // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
    //                     $name = $commentNum.'_'.$i.'.'.$type; //ex) 댓글 고유번호_1.jpg
    //                     $path = "$commentUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
    //                     move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                     $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                        
    //                     // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //                 }

    //                 // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //                 $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    //                 // 댓글 내용 수정.
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = '$urlList' WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 이미지가 없을 때. ----> ※. 수정시 이미지를 삭제한 채로(이미지가 포함되지 않은채로) 수정하게 되면 [] 값이 들어서 불러올 떄 에러가 발생한다. 
    //             //                                따라서 이런경우 comment_imgPath 값을 comment_imgPath의 Default 값(Null)으로 넣어준다. 
    //             else{

    //                 // 수정 시 기존 이미지를 삭제하였으므로 폴더도 마저 삭제해준다.
    //                 @rmdir($delete_path);  // 폴더 삭제

    //                 // 댓글 내용 수정. 
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = DEFAULT WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 수정된 댓글 정보조회.
    //             if($res1) {

    //                 // (5) 수정된 댓글/답글 정보 클라이언트에 넘겨준다.

    //                 // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //                 $sql = "SELECT * FROM comments WHERE comment_num = $commentNumResult"; 
    //                 $res = mysqli_query($conn, $sql);
                    
    //                 while($comment = $res->fetch_assoc()) { 
                        
    //                     // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                     // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                     $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                     $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                     $user_info = mysqli_fetch_assoc($res_user);
                        
    //                     // ***** 게시물 데이터 최종 합치기 *****
    //                     // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                     $data = [
    //                         'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                         'post_num' => $comment['post_num'], // 게시물 고유번호
    //                         'user_num' => $comment['user_num'], // 작성자 고유번호
    //                         'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                         'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                         'content' => $comment['content'], // 내용
    //                         'category' => $comment['category'], // 카테고리
    //                         'parent' => $comment['parent'], // 부모 번호
    //                         'regtime' => $comment['regtime'], // 작성날짜
    //                         'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                         'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                     ]; 
    //                     array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //                 }

    //                 mysqli_close($conn); // DB 종료.

    //                 echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //             } else {
    //                 // 포스트 수정 실패
    //                 $result = array("result" => "error"); // $result["success"] = false;
    //                 echo "error3";
    //             }
    //         }

    //     }
    //   // 답글인 경우
    //     else{

    //         // 답글별 이미지 디렉토리 생성.
    //         $dirBaseName = "comment_reply_images";
    //         $replyDirName = $commentNum.'_'.$dirBaseName; // ex) 23_comment_reply_images
    //         $replyUploadDir = "commentImages"; // 답글 이미지 저장할 폴더명
    //         $replyDirPath = "$replyUploadDir/$replyDirName"; // 답글을 수정할 때 해당 답글의 이미지들을 저장할 폴더경로.

    //         // umask : 권한을 설정할 때 수동적으로 권한을 주지 않고 파일이나 디렉토리가 생성됨가 동시에 지정된 권한이 주어주도록 함
    //         // 리눅스 umask 설정값이 0022로 잡혀 있어서 0777로 디렉토리를 생성해도 0755값으로 나온 것이다.
    //         // 이를 해결하기 위해 umask값의 옵션을 변경해서 생성하니 해결 되었다.
    //         $oldumask = umask(0);

    //         // (3) $postDirPath 경로에 $postDirName 폴더가 존재하지 않으면. (is_dir가 조금더 빠르다고 한다.)
    //         if (!is_dir($replyDirPath)) {
                
    //             // 답글 이미지를 저장할 폴더를 만들어준다.
    //             mkdir($replyDirPath, 0777, true); // 777권한으로 폴더 생성.

    //             // (4) 이미지가 1장이상 있을 때
    //             if($cntImage > 0) {

    //                 // 서버에 저장된 사진의 URL 리스트
    //                 $urlList = array(); 

    //                 $server = 'http://49.247.148.192/'; // 서버주소.
    //                 $uploadDir = $replyDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_comment_reply_images

    //                 // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //                 for($i=0; $i<$cntImage; $i++) { 

    //                     $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
    //                     $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                     $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                     $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                     $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                     // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
    //                     $name = $commentNum.'_'.$i.'.'.$type; //ex) 댓글 고유번호_1.jpg
    //                     $path = "$replyUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) commentImages/23_comment_reply_images/게시물고유번호_1.jpg
    //                     move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                     $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                        
    //                     // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //                 }

    //                 // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //                 $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    //                 // 댓글 내용 수정.
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = '$urlList' WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 이미지가 없을 때. ----> ※. 수정시 이미지를 삭제한 채로(이미지가 포함되지 않은채로) 수정하게 되면 [] 값이 들어서 불러올 떄 에러가 발생한다. 
    //             //                                따라서 이런경우 comment_imgPath 값을 comment_imgPath의 Default 값(Null)으로 넣어준다. 
    //             else{

    //                 // 수정 시 기존 이미지를 삭제하였으므로 폴더도 마저 삭제해준다.
    //                 @rmdir($delete_path);  // 폴더 삭제

    //                 // 댓글 내용 수정. 
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = DEFAULT WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 수정된 댓글 정보조회.
    //             if($res1) {

    //                 // (5) 수정된 댓글/답글 정보 클라이언트에 넘겨준다.

    //                 // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //                 $sql = "SELECT * FROM comments WHERE comment_num = $commentNumResult"; 
    //                 $res = mysqli_query($conn, $sql);
                    
    //                 while($comment = $res->fetch_assoc()) { 
                        
    //                     // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                     // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                     $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                     $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                     $user_info = mysqli_fetch_assoc($res_user);
                        
    //                     // ***** 게시물 데이터 최종 합치기 *****
    //                     // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                     $data = [
    //                         'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                         'post_num' => $comment['post_num'], // 게시물 고유번호
    //                         'user_num' => $comment['user_num'], // 작성자 고유번호
    //                         'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                         'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                         'content' => $comment['content'], // 내용
    //                         'category' => $comment['category'], // 카테고리
    //                         'parent' => $comment['parent'], // 부모 번호
    //                         'regtime' => $comment['regtime'], // 작성날짜
    //                         'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                         'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                     ]; 
    //                     array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //                 }

    //                 mysqli_close($conn); // DB 종료.

    //                 echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //             } else {
    //                 // 포스트 수정 실패
    //                 $result = array("result" => "error"); // $result["success"] = false;
    //                 echo "error3";
    //             }

    //         }
    //         // 이미지 폴더가 존재하면
    //         else{

    //             // 폴더의 이미지를 모두 지운다.

    //             // 답글 이미지들이 담겨있는 폴더명
    //             $replyImageDirName = $commentNum.'_comment_reply_images';
            
    //             $delete_path = "commentImages/$replyImageDirName";

    //             // 디렉토리 안의 파일들 전부 삭제하는 함수. 
    //             function rmdir_files_ok($dir) {
    //                 $dirs = dir($dir);
    //                 while(false !== ($entry = $dirs->read())) {
    //                     if(($entry != '.') && ($entry != '..')) {
    //                         if(is_dir($dir.'/'.$entry)) {
    //                             rmdir_files_ok($dir.'/'.$entry);
    //                         } else {
    //                                 @unlink($dir.'/'.$entry);
    //                         }
    //                     }
    //                 }
    //                 $dirs->close(); // 파일 닫기
    //                 // @rmdir($dir);  // 마지막에 해당 경로 폴더까지 삭제.
    //             }
    //             rmdir_files_ok($delete_path); // 해당 게시물 파일 안의 이미지들 삭제하기.

    //             // (4) 이미지가 1장이상 있을 때
    //             if($cntImage > 0) {

    //                 // 서버에 저장된 사진의 URL 리스트
    //                 $urlList = array(); 

    //                 $server = 'http://49.247.148.192/'; // 서버주소.
    //                 $uploadDir = $replyDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_comment_reply_images

    //                 // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //                 for($i=0; $i<$cntImage; $i++) { 

    //                     $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
    //                     $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                     $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                     $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                     $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                     // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
    //                     $name = $commentNum.'_'.$i.'.'.$type; //ex) 댓글 고유번호_1.jpg
    //                     $path = "$replyUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) commentImages/23_comment_reply_images/게시물고유번호_1.jpg
    //                     move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                     $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                        
    //                     // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //                 }

    //                 // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //                 $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    //                 // 댓글 내용 수정.
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = '$urlList' WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 이미지가 없을 때. ----> ※. 수정시 이미지를 삭제한 채로(이미지가 포함되지 않은채로) 수정하게 되면 [] 값이 들어서 불러올 떄 에러가 발생한다. 
    //             //                                따라서 이런경우 comment_imgPath 값을 comment_imgPath의 Default 값(Null)으로 넣어준다. 
    //             else{

    //                 // 수정 시 기존 이미지를 삭제하였으므로 폴더도 마저 삭제해준다.
    //                 @rmdir($delete_path);  // 폴더 삭제

    //                 // 댓글 내용 수정. 
    //                 $sql1 = "UPDATE comments SET content='$commentContent' , comment_imgPath = DEFAULT WHERE comment_num = $commentNumResult";
    //                 $res1 = mysqli_query($conn, $sql1);
    //             }
    //             // 수정된 댓글 정보조회.
    //             if($res1) {

    //                 // (5) 수정된 댓글/답글 정보 클라이언트에 넘겨준다.

    //                 // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //                 $sql = "SELECT * FROM comments WHERE comment_num = $commentNumResult"; 
    //                 $res = mysqli_query($conn, $sql);
                    
    //                 while($comment = $res->fetch_assoc()) { 
                        
    //                     // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                     // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                     $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                     $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                     $user_info = mysqli_fetch_assoc($res_user);
                        
    //                     // ***** 게시물 데이터 최종 합치기 *****
    //                     // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                     $data = [
    //                         'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                         'post_num' => $comment['post_num'], // 게시물 고유번호
    //                         'user_num' => $comment['user_num'], // 작성자 고유번호
    //                         'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                         'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                         'content' => $comment['content'], // 내용
    //                         'category' => $comment['category'], // 카테고리
    //                         'parent' => $comment['parent'], // 부모 번호
    //                         'regtime' => $comment['regtime'], // 작성날짜
    //                         'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                         'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                     ]; 
    //                     array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //                 }

    //                 mysqli_close($conn); // DB 종료.

    //                 echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //             } else {
    //                 // 포스트 수정 실패
    //                 $result = array("result" => "error"); // $result["success"] = false;
    //                 echo "error3";
    //             }

    //         }

    //     }

    // }
?>