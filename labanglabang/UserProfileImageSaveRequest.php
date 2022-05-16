<?php
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    //$uri = $_POST["uri"]; // 사용자가 선택한 이미지 URI String값
    $userNum = $_POST["userNum"]; // 사용자 고유번호 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
    $resultNum = (int)$userNum;

    $sql = "SELECT * FROM members WHERE user_num = $resultNum";
    $result1 = mysqli_query($conn, $sql);
    $rs_num = mysqli_num_rows($result1);
    
    // user_num로 조회된 회원이 없으면.
    if($rs_num == 0){
 
        // 조회 실패
        $result = array("result" => "error"); // $result["success"] = false;
        echo "error";
        exit();

    // user_num로 조회된 회원이 있으면.(즉, 해당 고유번호의 회원이 존재하면)
    }else{

        $server = 'http://49.247.148.192/labanglabang/'; // 서버주소.
        $uploadDir = 'labangUserProfileImages'; // 서버에서 사용자 프로필 이미지를 저장할 디렉토리 이름.

        // 사용자가 변경한 프로필 이미지를 서버에 업로드 한 후 업로드된 이미지 경로를 프로필 이미지 컬럼에 저장한다.
        // 사용자가 변경한 이미지에 대한 정보를 각가의 변수에 저장한다.
        $tmp_name = $_FILES['profileImage']["tmp_name"];
        $oldName = $_FILES['profileImage']["name"]; //ex) example.jpg (houseTourImage1.jpg)
        $type = $_FILES['profileImage']["type"]; // application/octet-stream
        // ***********************************************************************************************************************************
        $oldName_array = explode(".", $oldName); //  '.'을 기준으로 분리하여 배열에 넣는다. ex) oldName_array[0] = example , oldName_array[1] = jpg 
        $type = array_pop($oldName_array); // array_pop(): 배열의 마지막 원소(확장자)를 반환한다. ex) jpg 
        $name = $userNum.'_'.'profileImage'.'.'.$type; //ex) 유저고유번호_profileImage.jpg
        $path = "$uploadDir/$name"; // 서버에 이미지를 저장할 경로.
        // ***********************************************************************************************************************************
        // 회원 프로필이미지 폴더에 이미 같은 이름의 파일이 존재하면 
		if (file_exists("./".$path)){ // (해당경로에있는 특정 파일이 존재하면 1을 반환한다.)
            
            // 해당 파일을 삭제한다.
            if(!unlink("./".$path)) {
                
                // 삭제 실패
                $result = array("result" => "error"); // $result["success"] = false;
                echo "error";
                exit();
              }
              else {
                // 삭제 성공
                
              }
		}
		else{
            // 같은 이름의 파일이 존재하지 않으면 그대로 진행
		}
        // ***********************************************************************************************************************************
        move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
        $profile_image = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지의 uri값을 최종적으로 $profile_image 변수에 저장한다.
        // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  profile_image 담아서 그것을 DB의 사용자 프로필 이미지 컬럼에 저장한다.

        // 프로필 이미지 변경
        $sql2 = "UPDATE members SET profile_image='$profile_image' WHERE user_num=$resultNum";
        $result2 = mysqli_query($conn, $sql2);

        // DB에 성공적으로 프로필 이미지 경로 업데이트했으면
        if($result2){

            // 변경 후 변경된 회원 정보 모두 조회.
            $sql3 = "SELECT profile_image FROM members WHERE user_num=$resultNum";
            $result3 = mysqli_query($conn, $sql3);
            $row = mysqli_fetch_assoc($result3); // 조회값 가져오기.
        
            // 사용자 프로필 이미지 조회 성공
            if($result3) {
                // 조회한 값 저장
                
                $profileImg = $row['profile_image']; // 사용자 프로필 이미지 저장된 값 $profileImg에 저장.
            
                // 사용자 프로필 이미지 경로를 value로 넘겨준다.
                $result = array("result" => "success", "value" => $profileImg); //$result["success"] = true;  value는 클라이언트(안드로이드 자바) 에서 response.body() 출력했을때 보여줄 값인듯
                echo $profileImg;
                exit();
        
            } else {
                // 조회 실패
                $result = array("result" => "error"); // $result["success"] = false;
                echo "error";
                exit();
            }
        }
        else{
            
            // 프로필 경로 업데이트 실패
            $result = array("result" => "error"); // $result["success"] = false;
            echo "error";

            exit();
        }
       
    }
?>