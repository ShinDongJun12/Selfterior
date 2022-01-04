<?php
    //ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $postNum = $_POST["postNum"]; // 게시물 고유번호 (String)
    $postNumInt = (int)$postNum; // 게시물 고유번호 (String) -> (int)

    $postTitle = $_POST["postTitle"]; // 게시물 제목
    $postContent = $_POST["postContent"]; // 게시물 내용

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.
     

    // 해당 고유번호를 가진 게시물이 존재하는지 조회. 
    $sql = "SELECT * FROM house_tour_post WHERE post_num = $postNumInt"; 
    $ret = mysqli_query($conn, $sql); 
    $exist = mysqli_num_rows($ret); 

    // 게시물이 존재 하면.
    if($exist > 0){

        // 게시물 이미지들이 담겨있는 폴더명
        $postImageDirName = $postNum.'_post_images';
    
        $delete_path = "houseTourPostImages/$postImageDirName";

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
        rmdir_files_ok($delete_path); // 해당 게시물 파일 안의 이미지들 삭제하기.

        // // 폴더안의 이미지 파일들 정상적으로 삭제 완료. 
        // if(rmdir_files_ok($delete_path)){
            
        // 게시물별 이미지 디렉토리 생성.
        $dirBaseName = "post_images";
        $postDirName = $postNum.'_'.$dirBaseName; // ex) 23_post_images
        $postUploadDir = "houseTourPostImages"; // 집구경 게시물 이미지 저장할 폴더명
        $postDirPath = "$postUploadDir/$postDirName"; // 게시물이 추가될 때 해당 게시물의 이미지들을 저장할 폴더경로.
        
        // ※ 수정 시 디렉토리안의 이미지들은 모두 삭제하지만 디렉토리는 남아있으므로 해당 디렉토리에 수정한 이미지를 모두 저장한다. ※
            
        // 서버에 저장된 사진의 URL 리스트
        $urlList = array();

        // 첨부된 사진이 1장이상 있을 때
        if($cntImage > 0) {
        
            $server = 'http://49.247.148.192/'; // 서버주소.
            $uploadDir = $postDirName; // 서버에서 사진을 저장할 디렉토리 이름. ex) 23_post_images

            // 사용자가 업로드한 이미지들 서버에 업로드 한 후 업로드된 각각의 이미지 경로들을 uriList에 담는다.
            for($i=0; $i<$cntImage; $i++) { 

                $tmp_name = $_FILES['image'.$i]["tmp_name"]; 
                $oldName = $_FILES['image'.$i]["name"]; //ex) example.jpg (houseTourImage1.jpg)
                $type = $_FILES['image'.$i]["type"]; // application/octet-stream
                $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
                $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
                // (※ 여기서 이미지 파일 이름번호 생성시 j+'url주소 개수'로 파일명을 만들어 주어야한다. 그래야 이미지 파일 이름이 안겹친다. ※)
                $name = $postNum.'_'.($i+$imgURLCount).'.'.$type; //ex) 게시물고유번호_1.jpg
                $path = "$postUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
                move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                $urlList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                
                // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
            }
        }
        
        // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
        $urlList = json_encode($urlList); // jsonArray를 문자열로 변환

    
        // DB에 게시물 수정한 내용 저장. (수정시 게시물 등록날짜는 변하지 X) , post_img_path='$urlList'
        $sql1 = "UPDATE house_tour_post SET post_title ='$postTitle', post_content ='$postContent', post_img_path = '$urlList' WHERE post_num = $postNum";
        $res1 = mysqli_query($conn, $sql1);

        if($res1) {

            $post_data_array = array(); // 게시물에 대한 모든 데이터를 담을 배열

            // 집구경 게시물 변경된 내용을 조회해서 클라이언트에 넘겨주고 변경된 내용을 바로 적용시켜준다.
            $sql = "SELECT * FROM house_tour_post WHERE post_num = $postNum"; 
            $res = mysqli_query($conn, $sql);
            
            while($post_info = $res->fetch_assoc()) { 
    
                // ***** 게시물 데이터 최종 합치기 *****
                // 집구경 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
                $data = [
                    'post_num' => $post_info['post_num'], // 게시물 고유번호
                    'user_num' => $post_info['user_num'], // 게시물 작성자(사용자) 고유번호
                    'post_title' => $post_info['post_title'], // 게시물 제목
                    'post_content' => $post_info['post_content'], // 게시물 내용
                    'post_thumbnail_image' => $post_info['post_img_path'], // 게시물 썸네일 URL(첫번째 사진)
                    'post_regtime' => $post_info['post_regtime'], // 게시물 등록날짜
                    'post_view_count' => $post_info['post_view_count'] // 게시물 조회수  
                ]; 
                array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
            }

            mysqli_close($conn); // DB 종료.

            echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)
                                    
            // 포스트 수정 완료 (★최종 성공★)
            // $result = array("result" => "success", "value" => $oldName); //$result["success"] = true;  value는 클라이언트(안드로이드 자바) 에서 response.body() 출력했을때 보여줄 값인듯
            // echo "success : ".$oldName;

        } else {
            // 포스트 수정 실패
            $result = array("result" => "error"); // $result["success"] = false;
            echo "error3";
        }

    }
    // 게시물이 존재하지 않으면.
    else{

        // 포스트 수정 실패
        $result = array("result" => "error"); // $result["success"] = false;
        echo "error4";
    }
    
    mysqli_close($conn); // DB종료.

    //echo json_encode($result); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (Retrofit은 서버로 부터 값 받을때 어떻게 전달하는지 참고.)

?>