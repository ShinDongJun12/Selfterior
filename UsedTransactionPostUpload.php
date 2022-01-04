<?php

    //ini_set('display_errors', true);
    
    include "dbcon.php"; // DB 연결.
    mysqli_query($conn,'SET NAMES utf8'); // DB 한글깨짐 방지.
    header("Content-Type:text/html;charset=utf-8"); // utf-8로 설정 -> PHP 한글깨짐 방지. ex) echo "가나다라";를 출력하면 그래도 '가나다라'로 출력이 가능하게 해주는 것.

    $userNum = $_POST["userNum"]; // 작성자 고유번호.
    $postTitle = $_POST["postTitle"]; // 게시물 제목
    $postContent = $_POST["postContent"]; // 게시물 내용
    $itemCategory = $_POST["itemCategory"]; // 아이템 카테고리

    $itemPriceStr = $_POST["itemPriceStr"]; // 아이템 가격
    $itemPrice = (int)$itemPriceStr; 

    $address = $_POST["address"]; // 주소

    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage; // 이미지 개수.

    // 클라이언트로 보낼 응답 배열
    $result = array();

    // post_img_path값을 ''으로 하고 나머지 정보들은 그대로 DB에 저장한다. (post_num가 생긴다.).
    $sql1 = "INSERT INTO used_transaction_post (user_num, post_title, item_category, item_price, sale_address, post_content, post_imgPath, post_regtime)";
    $sql1.= " VALUES ('$userNum', '$postTitle', '$itemCategory', '$itemPrice', '$address', '$postContent', 'mmm', now())";
    $res1 = mysqli_query($conn, $sql1);
    
    // DB에 정상적으로 저장되었으면.
    if($res1) {
        
        // ★마지막 PK 값을 획득한다. (방금 생성된 게시물의 고유번호)★
        $get_post_num = mysqli_insert_id($conn);
        $get_post_num2 = (string)$get_post_num; // 받아온 게시물 고유번호(int) -> (string)으로 변환 후 다시 저장.
        
        // 게시물별 이미지 디렉토리 생성.
        $dirBaseName = "used_transaction_post_images";
        $postDirName = $get_post_num2.'_'.$dirBaseName; // ex) 23_post_images
        $postUploadDir = "usedTransactionPostImages"; // 중고거래 게시물 이미지 저장할 폴더명
        $postDirPath = "$postUploadDir/$postDirName"; // 게시물이 추가될 때 해당 게시물의 이미지들을 저장할 폴더경로.
        
        // umask : 권한을 설정할 때 수동적으로 권한을 주지 않고 파일이나 디렉토리가 생성됨가 동시에 지정된 권한이 주어주도록 함
        // 리눅스 umask 설정값이 0022로 잡혀 있어서 0777로 디렉토리를 생성해도 0755값으로 나온 것이다.
        // 이를 해결하기 위해 umask값의 옵션을 변경해서 생성하니 해결 되었다.
        $oldumask = umask(0);

        // $postDirPath 경로에 $postDirName 폴더가 존재하지 않으면. (is_dir가 조금더 빠르다고 한다.)
        if (!is_dir($postDirPath)) {
            
            mkdir($postDirPath, 0777, true); // 777권한으로 폴더 생성.

            // 서버에 저장된 사진의 URL 리스트
            $uriList = array();
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
                    $name = $get_post_num2.'_'.$i.'.'.$type; //ex) 게시물고유번호_1.jpg
                    $path = "$postUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
                    move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                    $uriList[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                    
                    // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
                }
            }
            else{

                $uriList[] = "Nothing";
            }
            
            // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
            $uriList = json_encode($uriList); // jsonArray를 문자열로 변환

            // DB에 게시물의 post_img_path, post_regtime 값을 추가적으로 저장한다.
            $sql2 = "UPDATE used_transaction_post SET post_imgPath = '$uriList', post_regtime = now() WHERE post_num = $get_post_num";
            $res2 = mysqli_query($conn, $sql2);
            if($res2) {

                // 새로 추가된 게시물의 모든 정보를 조회하여 클라이언트에 응답으로 넘겨준다.

                $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에 대한 모든 데이터를 담을 배열

                // 먼저 날짜 빠른순으로 게시물 각각의 데이터를 배열에 담아 둔다.
                // 중고거래 게시물 등록날짜 기준 내림차순으로 데이터 조회(가장 최근에 업로드한 게시물이 먼저오도록(맨 위로 오도록)조회.)
                // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
                $sql = "SELECT * FROM used_transaction_post WHERE post_num = $get_post_num";
                $res = mysqli_query($conn, $sql);
                
                // 게시물에 대해 조회된 결과들을 $post에 저장한다.
                $post = mysqli_fetch_assoc($res);
                // // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장. -> 한 개시물에 대한 정보만 조회하므로 wgi
                // while($post = $res->fetch_assoc()) { 
                    
                    // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
                    // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
                    $sql_user = "select profile_image, user_nickname from members where user_num = $post[user_num]";
                    $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                    $user_info = mysqli_fetch_assoc($res_user);

                    
                    // ***** 북마크 클릭여부 조회 *****
                    // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호, 북마크 카테고리 값들로 현재 접속중인 회원의 해당 게시물 북마크 체크여부 데이터를 조회한다. $post_category
                    $sql_user2 = "select bookmark_check from bookmark where user_num = $login_user_num and post_num = $post[post_num] and bookmark_category = '중고거래게시물'";
                    $res_user2 = mysqli_query($conn, $sql_user2); 
                    $num_row = mysqli_num_rows($res_user2); 

                    // 사용자가 해당 게시물에 대해 북마크 버튼을 한번이라도 누른적이 있다면.
                    if($num_row > 0){
                        $post_info = array(); // post_info 배열 생성
                        $post_info = mysqli_fetch_assoc($res_user2); // bookmark_check값을 받아온다.
                    }
                    // 한번도 북마크 버튼을 누른적이 없으면.
                    else{

                        $post_info = array(); // post_info 배열 생성
                        $post_info['bookmark_check'] = 0; // bookmark_check값에 0값을 넣는다.
                    }
                    

                    // ***** 관심 총 개수 조회(여기서 말하는 관심은 해당 개시물을 북마크한 회원 수를 의미하므로 북마크 테이블에서 조회한다.) *****
                    // 게시물 고유번호, 북마크 체크 값이 2, 북마크 카테고리 값으로 게시물을 북마크한 총 개수를 조회한다.
                    $sql_user3 = "select * from bookmark where post_num = $post[post_num] and bookmark_check = 2 and bookmark_category = '중고거래게시물'";
                    $res_user3 = mysqli_query($conn, $sql_user3); 
                    $num_row2 = mysqli_num_rows($res_user3); // 해당 값이 게시물에대한 관심(북마크) 총 개수이다.


                    // ***** 게시물 데이터 최종 합치기 *****
                    // 중고거래 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
                    $data = [
                        'post_num' => $post['post_num'], // 게시물 고유번호
                        'user_num' => $post['user_num'], // 게시물 작성자(사용자) 고유번호
                        'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                        'user_profile_image' => $user_info['profile_image'], // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                        'post_title' => $post['post_title'], // 게시물 제목
                        'post_content' => $post['post_content'], // 게시물 내용
                        'post_imgPath' => $post['post_imgPath'], // 게시물 이미지 URL(저장된 이미지 경로 전체)
                        'post_regtime' => $post['post_regtime'], // 게시물 등록날짜
                        'item_category' => $post['item_category'], // 아이템 카테고리
                        'item_price' => $post['item_price'], // 아이템 가격
                        'sale_address' => $post['sale_address'], // 판매자 주소
                        'post_view_count' => $post['post_view_count'], // 게시물 조회수
                        'transaction_status' => $post['transaction_status'], // 거래상태
                        'total_interest_count' => $num_row2, // 관심 총 개수
                        'bookmark_check' => $post_info['bookmark_check'] // 게시물 북마크 체크여부 (한번도 북마크 버튼을 누른적이 없다면 null값을 전달한다)
                    ]; 
                    array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
                // }

                mysqli_close($conn); // DB 종료.

                echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

                // // 포스트 저장 완료 (★최종 성공★)
                // $result =array("result" => "success", "value" => $oldName); //$result["success"] = true;  value는 클라이언트(안드로이드 자바) 에서 response.body() 출력했을때 보여줄 값인듯
                // echo "success";
                // // echo "success : ".$oldName;

            } else {
                // 포스트 저장 실패
                // $result = array("result" => "error"); // $result["success"] = false;
                // echo "error";
            }

            umask($oldumask);
        } 
        // 폴더가 존재하면 
        else {
            
            // 바로 해당 폴더에 이미지 파일들 저장.
            // 서버에 저장된 사진의 URL 리스트
            $uriList2 = array();
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
                    $name = $get_post_num2.'_'.$i.'.'.$type; //ex) 게시물고유번호_1.jpg
                    $path = "$postUploadDir/$uploadDir/$name"; // 서버에 이미지를 저장할 경로. ex) houseTourPostImages/23_post_images/게시물고유번호_1.jpg
                    move_uploaded_file($tmp_name, "./$path"); // 임시 경로에 저장된 사진을 $path로 옮김 (서버에서 내가원하는 폴더에 이미지들을 저장해 두는 것)
                    $uriList2[] = $server.$path; // 서버에 저장된 이미들을 불러올 때 사용할 이미지들의 uri들의 List
                    
                    // 이미지 파일들 임시경로에 옮기고 서버 주소와 임시경로를 합친 값을  uriList에 담아서 jsonArray를 문자열로 변환하고 그것을 DB의 이미지 리스트 컬럼에 넣는다.
                }
            }
            else{
                $uriList[] = "Nothing";
            }
            
            // 서버에 저장된 사진의 uri 리스트 (서버에 이미지 저장하는 폴더로 업로드된 이미지 옮기고 그 경로를 uriList에 담는다.)
            $uriList2 = json_encode($uriList); // jsonArray를 문자열로 변환

            // DB에 게시물의 post_img_path, post_regtime 값을 추가적으로 저장한다.
            $sql3 = "UPDATE used_transaction_post SET post_imgPath = '$uriList2', post_regtime = now() WHERE post_num = $get_post_num";
            $res3 = mysqli_query($conn, $sql3);
            if($res3) {
                
                // 새로 추가된 게시물의 모든 정보를 조회하여 클라이언트에 응답으로 넘겨준다.

                $post_data_array = array(); // 리사이클러뷰에 보여줄 한 게시물에 대한 모든 데이터를 담을 배열

                // 먼저 날짜 빠른순으로 게시물 각각의 데이터를 배열에 담아 둔다.
                // 집구경 게시물 등록날짜 기준 내림차순으로 데이터 조회(가장 최근에 업로드한 게시물이 먼저오도록(맨 위로 오도록)조회.)
                // $page 행 부터 $limit만큼 데이터 조회해서 가져오기.
                $sql = "SELECT * FROM used_transaction_post WHERE post_num = $get_post_num";
                $res = mysqli_query($conn, $sql);
                
                // 게시물에 대해 조회된 결과들을 $post에 저장한다.
                $post = mysqli_fetch_assoc($res);
                // // 게시물 갯수만큼 while문 돌면서 데이터 조회하고 저장. -> 한 개시물에 대한 정보만 조회하므로 wgi
                // while($post = $res->fetch_assoc()) { 
                    
                    // ***** 사용자의 닉네임과 프로필 이미지 Uri 조회 *****
                    // 저장한 값들 중 사용자 고유번호를 통해 사용자의 닉네임과 프로필 이미지 Uri 데이터를 조회.
                    $sql_user = "select profile_image, user_nickname from members where user_num = $post[user_num]";
                    $res_user = mysqli_query($conn, $sql_user); // 다른 형식의 SQL 구문, INSERT, UPDATE, DELETE, DROP 등에서 성공하면 TRUE를, 실패하면 FALSE를 반환합니다.
                    $user_info = mysqli_fetch_assoc($res_user);

                    
                    // ***** 북마크 클릭여부 조회 *****
                    // $login_user_num (현재 접속중인 회원고유번호), 게시물 고유번호, 북마크 카테고리 값들로 현재 접속중인 회원의 해당 게시물 북마크 체크여부 데이터를 조회한다. $post_category
                    $sql_user2 = "select bookmark_check from bookmark where user_num = $login_user_num and post_num = $post[post_num] and bookmark_category = '중고거래게시물'";
                    $res_user2 = mysqli_query($conn, $sql_user2); 
                    $num_row = mysqli_num_rows($res_user2); 

                    // 사용자가 해당 게시물에 대해 북마크 버튼을 한번이라도 누른적이 있다면.
                    if($num_row > 0){
                        $post_info = array(); // post_info 배열 생성
                        $post_info = mysqli_fetch_assoc($res_user2); // bookmark_check값을 받아온다.
                    }
                    // 한번도 북마크 버튼을 누른적이 없으면.
                    else{

                        $post_info = array(); // post_info 배열 생성
                        $post_info['bookmark_check'] = 0; // bookmark_check값에 0값을 넣는다.
                    }


                    // ***** 관심 총 개수 조회(여기서 말하는 관심은 해당 개시물을 북마크한 회원 수를 의미하므로 북마크 테이블에서 조회한다.) *****
                    // 게시물 고유번호, 북마크 카테고리 값으로 게시물을 북마크한 총 개수를 조회한다.
                    $sql_user3 = "select * from bookmark where post_num = $post[post_num] and bookmark_category = '중고거래게시물'";
                    $res_user3 = mysqli_query($conn, $sql_user3); 
                    $num_row2 = mysqli_num_rows($res_user3); // 해당 값이 게시물에대한 관심(북마크) 총 개수이다.
                    
                    
                    // ***** 게시물 데이터 최종 합치기 *****
                    // 중고거래 게시물 데이터 + 게시물 작성자 정보(닉네임, 프로필 이미지)를 $data에 다시 저장.
                    $data = [
                        'post_num' => $post['post_num'], // 게시물 고유번호
                        'user_num' => $post['user_num'], // 게시물 작성자(사용자) 고유번호
                        'user_nickname' => $user_info['user_nickname'], // 게시물 작성자(사용자) 닉네임 (추가한 유저정보)
                        'user_profile_image' => $user_info['profile_image'], // 게시물 작성자(사용자) 프로필 이미지 Uri (추가한 유저정보)
                        'post_title' => $post['post_title'], // 게시물 제목
                        'post_content' => $post['post_content'], // 게시물 내용
                        'post_imgPath' => $post['post_imgPath'], // 게시물 이미지 URL(저장된 이미지 경로 전체)
                        'post_regtime' => $post['post_regtime'], // 게시물 등록날짜
                        'item_category' => $post['item_category'], // 아이템 카테고리
                        'item_price' => $post['item_price'], // 아이템 가격
                        'sale_address' => $post['sale_address'], // 판매자 주소
                        'post_view_count' => $post['post_view_count'], // 게시물 조회수
                        'transaction_status' => $post['transaction_status'], // 거래상태
                        'total_interest_count' => $num_row2, // 관심 총 개수
                        'bookmark_check' => $post_info['bookmark_check'] // 게시물 북마크 체크여부 (한번도 북마크 버튼을 누른적이 없다면 null값을 전달한다)
                    ]; 
                    array_push($post_data_array, $data); // 리사이클러뷰에 보여줄 게시물에대한 모든 정보를 담은 $data를 $post_data_array배열에 푸쉬.
                // }

                mysqli_close($conn); // DB 종료.

                echo json_encode($post_data_array); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (json형식으로 인코딩)

                // // 포스트 저장 완료 (★최종 성공★)
                // $result =array("result" => "success", "value" => $oldName); //$result["success"] = true;  value는 클라이언트(안드로이드 자바) 에서 response.body() 출력했을때 보여줄 값인듯
                // echo "success";
                // // echo "success : ".$oldName;

            } else {
                // 포스트 저장 실패
                // $result = array("result" => "error"); // $result["success"] = false;
                // echo "error";
            }

        }

    }
    // DB에 정상적으로 저장되지 않았으면.
    else{
        // 포스트 저장 실패
        // $result = array("result" => "error"); // $result["success"] = false;
        // echo "error";
    }
    
    mysqli_close($conn); // DB종료.

    //echo json_encode($result); // 배열을 json 문자열로 변환하여 클라이언트에 전달. (Retrofit은 서버로 부터 값 받을때 어떻게 전달하는지 참고.)
?>