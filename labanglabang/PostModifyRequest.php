<?php
    //ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $postNum = $_POST["postNum"]; // 게시글 고유번호 (String)
    $postNumInt = (int)$postNum; // 게시글 고유번호 (String) -> (int)

    $postCategory = $_POST["postCategory"]; // 게시글 카테고리
    $postTitle = $_POST["postTitle"]; // 게시글 제목
    $postContent = $_POST["postContent"]; // 게시글 내용

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.
     

    // 해당 고유번호를 가진 게시글이 존재하는지 조회. 
    $sql = "SELECT * FROM post WHERE post_num = $postNumInt"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 게시글이 존재 하면.
    if($exist > 0){

        // 게시글 이미지들이 담겨있는 폴더명
        $postImageDirName = $postNum.'_post_images';
    
        $delete_path = "postImages/$postImageDirName";

        // 디렉토리 안의 파일들 전부 삭제하는 함수. 
        function rmdir_files_ok($dir) {
            $dirs = dir($dir);
            while(false !== ($entry = $dirs->read())) {
                if(($entry != '.') && ($entry != '..')) {
                    if(is_dir($dir.'/'.$entry)) {
                        rmdir_files_ok($dir.'/'.$entry);
                    } else {
                            @unlink($dir.'/'.$entry);
                    }
                }
            }
            $dirs->close(); // 파일 닫기
            // @rmdir($dir);  // 마지막에 해당 경로 폴더까지 삭제.
        }
        rmdir_files_ok($delete_path); // 해당 게시글 파일 안의 이미지들 삭제하기.

        // // 폴더안의 이미지 파일들 정상적으로 삭제 완료. 
        // if(rmdir_files_ok($delete_path)){
            
        // 게시글별 이미지 디렉토리 생성.
        $dirBaseName = "post_images";
        $postDirName = $postNum.'_'.$dirBaseName; // ex) 23_post_images
        $postUploadDir = "postImages"; // 게시글 이미지 저장할 폴더명
        $postDirPath = "$postUploadDir/$postDirName"; // 게시글이 추가될 때 해당 게시글의 이미지들을 저장할 폴더경로.
        
        // ※ 수정 시 디렉토리안의 이미지들은 모두 삭제하지만 디렉토리는 남아있으므로 해당 디렉토리에 수정한 이미지를 모두 저장한다. ※
            
        // 서버에 저장된 사진의 URL 리스트
        $urlList = array();

        // 첨부된 사진이 1장이상 있을 때
        if($cntImage > 0) {
        
            $server = 'http://49.247.148.192/labanglabang/'; // 서버주소.
            $uploadDir = $postDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_post_images

            // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
            for($i=0; $i<$cntImage; $i++) { 

                $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
                $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
                $type = $_FILES['image'.$i]["type"]; // application/octet-stream
                $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
                $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
                // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
                $name = $postNum.'_'.($i+$imgURLCount).'.'.$type; //ex) 게시글고유번호_1.jpg
                $path = "$postUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시글고유번호_1.jpg
                move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                
                // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
            }
        }
        else{
            $uriList[] = "Nothing";
        }
        
        // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
        $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    
        // DB에 게시글 수정한 내용 저장. (수정시 게시글 등록날짜는 변하지 X) , post_img_path='$urlList'
        $sql1 = "UPDATE post SET post_category = '$postCategory', post_title ='$postTitle', post_content ='$postContent', post_img_path = '$urlList' WHERE post_num = $postNum";
        $res1 = mysqli_query($conn, $sql1);

        if($res1) {

            $post_data_array = array(); // 게시글에 대한 모든 데이터를 담을 배열

// 기존에 post 테이블에 있는 데이터만 클라이언트에게 넘겨주는 코드.

            // 게시글 변경된 내용을 조회해서 클라이언트에 넘겨주고 변경된 내용을 바로 적용시켜준다.
            // $sql = "SELECT * FROM post WHERE post_num = $postNum"; 
            // $res = mysqli_query($conn, $sql);
            
            // while($post_info = $res->fetch_assoc()) { 
    
            //     // ***** 게시글 데이터 최종 합치기 *****
            //     // 게시글 데이터 + 게시글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
            //     $data = [
            //         'post_num' => $post_info['post_num'], // 게시글 고유번호
            //         'user_num' => $post_info['user_num'], // 게시글 작성자(사용자) 고유번호
            //         'post_title' => $post_info['post_title'], // 게시글 제목
            //         'post_content' => $post_info['post_content'], // 게시글 내용
            //         'post_thumbnail_image' => $post_info['post_img_path'], // 게시글 썸네일 URL(첫번째 사진)
            //         'post_regtime' => $post_info['post_regtime'], // 게시글 등록날짜
            //         'post_view_count' => $post_info['post_view_count'] // 게시글 조회수  
            //     ]; 
            //     array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시글에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
            // }

// 게시글에 관련 된 모든 데이터를 넘겨주는 코드.            
            $sql = "SELECT * FROM post WHERE post_num = $postNum";
            $res = mysqli_query($conn, $sql);
            // 게시물에 대해 조회된 결과들을 $post에 저장한다.
            $post = mysqli_fetch_assoc($res);

            // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장. -> 한 개시물에 대한 정보만 조회하므로 wgi
            // while($post = $res->fetch_assoc()) { 
                
                // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
                // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
                $sql_user = "select profile_image, user_nickname from members where user_num = $post[user_num]";
                $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                $user_info = mysqli_fetch_assoc($res_user);

                
                // ***** 즐겨찾기 클릭여부 조회 *****
                // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호, 북마크 카테고리 값들로 현재 접속중인 회원의 해당 게시물 북마크 체크여부 데이터를 조회한다. $post_category
                $sql_user2 = "select bookmark_check from bookmark where user_num = $login_user_num and post_num = $post[post_num] and bookmark_category = '게시글'";
                $res_user2 = mysqli_query($conn, $sql_user2); 
                $num_row = mysqli_num_rows($res_user2); 

                // 사용자가 해당 게시물에 대해 즐겨찾기 버튼을 한번이라도 누른적이 있다면.
                if($num_row > 0){
                    $post_info = array(); // post_info 배열 생성
                    $post_info = mysqli_fetch_assoc($res_user2); // bookmark_check값을 받아온다.
                }
                // 한번도 북마크 버튼을 누른적이 없으면.
                else{   

                    $post_info = array(); // post_info 배열 생성
                    $post_info['bookmark_check'] = 0; // bookmark_check값에 0값을 넣는다.
                }


                // ***** 좋아요 클릭여부 조회 *****
                // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호 값들로 현재 접속중인 회원의 해당 게시물 좋아요 체크여부 데이터를 조회한다.
                $sql_user2 = "select post_like_check from post_like where user_num = $login_user_num and post_num = $post[post_num] and post_like_category = '게시글'";
                $res_user2 = mysqli_query($conn, $sql_user2);
                $num_row = mysqli_num_rows($res_user2); 

                // 사용자가 해당 게시물에 대해 좋아요 버튼을 한번이라도 누른적이 있다면.
                if($num_row > 0){
                    $post_like_info = array(); // post_like_info 배열 생성
                    $post_like_info = mysqli_fetch_assoc($res_user2); // post_like_check값을 받아온다.
                }
                // 한번도 좋아요 버튼을 누른적이 없으면.
                else{

                    $post_like_info = array(); // post_info 배열 생성
                    $post_like_info['post_like_check'] = 0; // post_like_check값에 0값을 넣는다.
                }


                // ***** 게시글 댓글 총 개수 조회 *****
                // 현재 게시글 고유번호와 카테고리값을 통해 현재 게시물의 댓글 총 개수를 조회한다. $comment_category
                $sql_post = "select * from comments where post_num = $post[post_num] and category = '게시글'";
                $res_comment = mysqli_query($conn, $sql_post);
                $comment_total = mysqli_num_rows($res_comment); 


                // ***** 게시글 좋아요 총 개수 조회 *****
                // 현재 게시글 고유번호와 카테고리값을 통해 현재 게시물의 좋아요 총 개수를 조회한다.
                $sql_post = "select * from post_like where post_num = $post[post_num] and post_like_category = '게시글'";
                $res_post_like = mysqli_query($conn, $sql_post);
                $post_like_count = mysqli_num_rows($res_post_like); 

                
                // ***** 게시글 데이터 최종 합치기 *****
                // 게시글 데이터 + 게시글 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
                $data = [
                    'post_num' => $post['post_num'], // 게시글 고유번호
                    'user_num' => $post['user_num'], // 게시글 작성자(사용자) 고유번호
                    'user_nickname' => $user_info['user_nickname'], // 게시글 작성자(사용자) 닉네임 (추가한 유저정보)
                    'user_profile_image' => $user_info['profile_image'], // 게시글 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                    'post_title' => $post['post_title'], // 게시글 제목
                    'post_content' => $post['post_content'], // 게시글 내용
                    'post_category' => $post['post_category'], // 게시물 카테고리
                    'post_images' => $post['post_img_path'], // 게시글 이미지 URL들 
                    'post_regtime' => $post['post_regtime'], // 게시글 등록날짜
                    'post_view_count' => $post['post_view_count'], // 게시글 조회수
                    'bookmark_check' => $post_info['bookmark_check'], // 게시글 북마크 체크여부 (한번도 북마크 버튼을 누른적이 없다면 null값을 전달한다)
                    'post_like_check' => $post_like_info['post_like_check'], // 게시글 좋아요 체크여부 (한번도 좋아요 버튼을 누른적이 없다면 null값을 전달한다)
                    'post_comment_count' => $comment_total, // 게시글 댓글 총 개수
                    'post_like_count' => $post_like_count // 게시글 좋아요 총 개수
                ]; 
                array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시글에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.

            mysqli_close($conn); // DB 종료.

            echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
  
        } 
        else {
            // 포스트 수정 실패
            $result = array("result" => "error"); // $result["success"] = false;
            echo "error3";
        }

    }
    // 게시글이 존재하지 않으면.
    else{

        // 포스트 수정 실패
        $result = array("result" => "error"); // $result["success"] = false;
        echo "error4";
    }
    
    mysqli_close($conn); // DB종료.

    //echo json_encode($result); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (Retrofit은 서버로 부터 값 받을때 어떻게 전달하는지 참고.)

?>