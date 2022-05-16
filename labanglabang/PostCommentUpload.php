<?php
    //ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $postNum = $_POST["postNum"]; // 게시물 고유번호.
    $postNum = (int)$postNum; // int형으로 변환

    $userNum = $_POST["userNum"]; // 작성자 고유번호.
    $userNum = (int)$userNum; // int형으로 변환

    $commentContent = $_POST["commentContent"]; // 댓글/대댓글 내용.
    $commentCategory = $_POST["commentCategory"]; // 댓글/대댓글 카테고리.
    
    // $parentNum = $_POST["parentNum"]; // 부모번호.(부모번호 없으면 즉, 댓글이면 Null)
    // // String 0 이 아니면 부모번호가 존재하는 것이므로 int형으로 변환해준다.  ---> 댓글이라 int형의 기본 값인 0 값이 들어오더라도 어차피 int형으로 다 바꿔준다.
    // // if($parentNum != Null){
    // $parentNum = (int)$parentNum; // int형으로 변환 
    // // }

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.

    //$get_comment_num = 0; // 댓글/대댓글 고유번호 담을 변수.

    // 클라이언트로 보낼 응답 배열
    $comment_data_array = array(); // 작성한 댓글/대댓글의 정보 및 작성자 정보(닉네임, 프로필 이미지)를 담을 배열.

    // 집구경 게시물의 경우 이미지가 포함돼있지 않다. (comment_imgPath값을 Null 저장한다.)       

    // 이미지 경로 값은 일단 Default값인 Null로 저장, parent 값도 임의로 댓글 저장
    $sql = "INSERT INTO comments (post_num,user_num,content,category,regtime) VALUES ($postNum,$userNum,'$commentContent','$commentCategory',now())";
    $res = mysqli_query($conn, $sql); // 쿼리문 실행
    $get_comment_num = mysqli_insert_id($conn); // ★마지막 PK 값을 획득한다. (방금 생성된 댓글의 고유번호)★

    // 댓글의 경우 자기자신의 comment_num값을 parent값으로 갖도록 변경한다.
    $sql2 = "UPDATE comments SET parent = $get_comment_num WHERE comment_num = $get_comment_num";
    $res2 = mysqli_query($conn, $sql2);


    // // (2) category값을 통해 집구경 게시물과 질문과 답변 게시물을 구분한다.
    // if($commentCategory == "집구경"){

        // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
        $sql = "SELECT * FROM comments WHERE comment_num = $get_comment_num"; 
        $res = mysqli_query($conn, $sql);
        
        while($comment = $res->fetch_assoc()) { 
            
            // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
            // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
            $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
            $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
            $user_info = mysqli_fetch_assoc($res_user);
            
            // ***** 게시물 데이터 최종 합치기 *****
            // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
            $data = [
                'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
                'post_num' => $comment['post_num'], // 게시물 고유번호
                'user_num' => $comment['user_num'], // 작성자 고유번호
                'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
                'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Url (추가한 유저정보)
                'content' => $comment['content'], // 내용
                'category' => $comment['category'], // 카테고리
                'parent' => $comment['parent'], // 부모 번호
                'regtime' => $comment['regtime'], // 작성날짜
                //'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
                'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
            ]; 
            array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
        }

        mysqli_close($conn); // DB 종료.

        echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    // }
    // else{
    // // else if($commentCategory == "질문과답변"){

    //     $get_comment_num2 = (string)$get_comment_num; // 받아온 댓글/대댓글 고유번호(int) -> (string)으로 변환 후 다시 저장.

    //     // 클라이언트로 보낼 응답 배열
    //     $comment_data_array = array(); // 작성한 댓글/대댓글의 정보 및 작성자 정보(닉네임, 프로필 이미지)를 담을 배열.  
            
    //     // 댓글별 이미지 디렉토리 생성.
    //     $dirBaseName = "comment_images";
    //     $commentDirName = $get_comment_num2.'_'.$dirBaseName; // ex) 23_comment_images
    //     $commentUploadDir = "commentImages"; // 질문과 답변 게시물의 '댓글'에 포함된 이미지 저장할 폴더명
    //     $commentDirPath = "$commentUploadDir/$commentDirName"; // 댓글이 추가될 때 해당 댓글의 이미지들을 저장할 폴더경로.

    //     // 이미지가 존재할경우        
    //     if($cntImage > 0) {

    //         // umask : 권한을 설정할 때 수동적으로 권한을 주지 않고 파일이나 디렉토리가 생성됨가 동시에 지정된 권한이 주어주도록 함
    //         // 리눅스 umask 설정값이 0022로 잡혀 있어서 0777로 디렉토리를 생성해도 0755값으로 나온 것이다.
    //         // 이를 해결하기 위해 umask값의 옵션을 변경해서 생성하니 해결 되었다.
    //         $oldumask = umask(0);

    //         // 서버에 저장된 사진의 URL 리스트
    //         $uriList = array();

    //         // $postDirPath 경로에 $postDirName 폴더가 존재하지 않으면. (is_dir가 조금더 빠르다고 한다.)
    //         if (!is_dir($commentDirPath)) {
                
    //             mkdir($commentDirPath, 0777, true); // 777권한으로 폴더 생성.

    //             $server = 'http://49.247.148.192/'; // 서버주소.
    //             $uploadDir = $commentDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_comment_images

    //             // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //             for($i=0; $i<$cntImage; $i++) { 
    //                 $tmp_name = $_FILES['image'.$i]["tmp_name"];
    //                 $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                 $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                 $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                 $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                 $name = $get_comment_num2.'_'.$i.'.'.$type; //ex) 게시물고유번호_1.jpg
    //                 $path = "$commentUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_comment_images/게시물고유번호_1.jpg
    //                 move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                 $uriList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                    
    //                 // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //             }
                
    //             // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //             $uriList = json_encode($uriList); // jsonArray를 문자열로 변환

    //             // DB에 게시물의 post_img_path, post_regtime 값을 추가적으로 저장한다.
    //             $sql2 = "UPDATE comments SET comment_imgPath = '$uriList', regtime = now() WHERE comment_num = $get_comment_num";
    //             $res2 = mysqli_query($conn, $sql2);

    //             //$comment_data_array = array(); // 작성한 댓글/대댓글의 정보 및 작성자 정보(닉네임, 프로필 이미지)를 담을 배열.

    //             // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //             $sql = "SELECT * FROM comments WHERE comment_num = $get_comment_num"; 
    //             $res = mysqli_query($conn, $sql);
                
    //             while($comment = $res->fetch_assoc()) { 
                    
    //                 // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                 // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                 $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                 $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                 $user_info = mysqli_fetch_assoc($res_user);
                    
    //                 // ***** 댓글/대댓글 데이터 최종 합치기 *****
    //                 // 댓글/대댓글 데이터 + 댓글/대댓글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                 $data = [
    //                     'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                     'post_num' => $comment['post_num'], // 게시물 고유번호
    //                     'user_num' => $comment['user_num'], // 작성자 고유번호
    //                     'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                     'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                     'content' => $comment['content'], // 내용
    //                     'category' => $comment['category'], // 카테고리
    //                     'parent' => $comment['parent'], // 부모 번호
    //                     'regtime' => $comment['regtime'], // 작성날짜
    //                     'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                     'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                 ]; 
    //                 array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //             }

    //             mysqli_close($conn); // DB 종료.

    //             echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //             // umask() : 프로세스가 새로운 파일을 생성할 때 적용할 수 있는 접근 권한 중 설정되지 않아야 하는 항목을 제한할때 사용합니다.
    //             umask($oldumask);
    //         } 
    //         // 폴더가 존재하면 
    //         else {
                
    //             // 바로 해당 폴더에 이미지 파일들 저장.
     
    //             $server = 'http://49.247.148.192/'; // 서버주소.
    //             $uploadDir = $commentDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_post_images

    //             // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
    //             for($i=0; $i<$cntImage; $i++) { 
    //                 $tmp_name = $_FILES['image'.$i]["tmp_name"];
    //                 $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
    //                 $type = $_FILES['image'.$i]["type"]; // application/octet-stream
    //                 $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
    //                 $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
    //                 $name = $get_comment_num2.'_'.$i.'.'.$type; //ex) 게시물고유번호_1.jpg
    //                 $path = "$commentUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
    //                 move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
    //                 $uriList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                    
    //                 // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
    //             }
                
    //             // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
    //             $uriList = json_encode($uriList); // jsonArray를 문자열로 변환

    //             // DB에 게시물의 post_img_path, post_regtime 값을 추가적으로 저장한다.
    //             $sql3 = "UPDATE comments SET comment_imgPath = '$uriList', regtime = now() WHERE comment_num = $get_comment_num";
    //             $res3 = mysqli_query($conn, $sql3);

    //             //$comment_data_array = array(); // 작성한 댓글/대댓글의 정보 및 작성자 정보(닉네임, 프로필 이미지)를 담을 배열.

    //             // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //             $sql = "SELECT * FROM comments WHERE comment_num = $get_comment_num"; 
    //             $res = mysqli_query($conn, $sql);
                
    //             while($comment = $res->fetch_assoc()) { 
                    
    //                 // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //                 // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //                 $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //                 $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //                 $user_info = mysqli_fetch_assoc($res_user);
                    
    //                 // ***** 게시물 데이터 최종 합치기 *****
    //                 // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //                 $data = [
    //                     'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                     'post_num' => $comment['post_num'], // 게시물 고유번호
    //                     'user_num' => $comment['user_num'], // 작성자 고유번호
    //                     'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                     'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                     'content' => $comment['content'], // 내용
    //                     'category' => $comment['category'], // 카테고리
    //                     'parent' => $comment['parent'], // 부모 번호
    //                     'regtime' => $comment['regtime'], // 작성날짜
    //                     'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                     'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //                 ]; 
    //                 array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //             }

    //             mysqli_close($conn); // DB 종료.

    //             echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
    //         }

    //     }
    //     // 이미지가 존재하지 않을 경우. -> 질문과답변 게시물에 이미지를 포함하지 않고 텍스트 내용만 입력해서 댓글/대댓글 작성한 경우.
    //     // (질문과 답변 댓글/답글 이미지 폴더 만들지않고 이미지URL 빈 Array로 저장)
    //     else{

    //     // 그냥 이미지 컬럼은 Null로 돼있고 조회해서 정보만 클라이언트에 넘겨준다.

    //         // 댓글/대댓글 고유번호로 해당 댓글/대댓글의 정보를 모두 조회한다.
    //         $sql = "SELECT * FROM comments WHERE comment_num = $get_comment_num"; 
    //         $res = mysqli_query($conn, $sql);
            
    //         while($comment = $res->fetch_assoc()) { 
                
    //             // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
    //             // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
    //             $sql_user = "select profile_image, user_nickname from members where user_num = $comment[user_num]";
    //             $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
    //             $user_info = mysqli_fetch_assoc($res_user);
                
    //             // ***** 댓글/대댓글 데이터 최종 합치기 *****
    //             // 댓글/대댓글 데이터 + 댓글/대댓글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
    //             $data = [
    //                 'comment_num' => $comment['comment_num'], // 댓글/대댓글 고유번호
    //                 'post_num' => $comment['post_num'], // 게시물 고유번호
    //                 'user_num' => $comment['user_num'], // 작성자 고유번호
    //                 'user_nickname' => $user_info['user_nickname'], // 작성자(사용자) 닉네임 (추가한 유저정보)
    //                 'profile_image' => $user_info['profile_image'], // 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
    //                 'content' => $comment['content'], // 내용
    //                 'category' => $comment['category'], // 카테고리
    //                 'parent' => $comment['parent'], // 부모 번호
    //                 'regtime' => $comment['regtime'], // 작성날짜
    //                 'comment_imgPath' => $comment['comment_imgPath'], // 이미지.
    //                 'comment_delete' => $comment['comment_delete'] // 댓글/답글 삭제여부
    //             ]; 
    //             array_push($comment_data_array, $data); // 댓글/대댓글에 대한 모든 정보를 담은 $data를 $comment_data_array배열에 푸쉬.
    //         }

    //         mysqli_close($conn); // DB 종료.

    //         echo json_encode($comment_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

    //         //umask($oldumask);
    //     }
    // }
   
?>