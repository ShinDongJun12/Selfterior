<?php
 
    header("Content-Type:text/html; charset=UTF-8");
 
    include "dbcon.php";
    mysqli_query($conn,'SET NAMES utf8');  //한글 깨짐 방지

    $postTitle= $_POST['postTitle'];
    $postContent= $_POST['postContent'];
    $user_num= $_POST['user_num'];
    $user_num= (int)$user_num;
    $imgPath= $_POST['imgPath'];
    $cntImage = $_POST["cntImage"]; // 첨부된 사진 개수
    $cntImage = (int)$cntImage;
    $file= $_FILES['img'];
 
    //이미지 파일을 영구보관하기 위해
    //이미지 파일의 세부정보 얻어오기
    $srcName= $file['name'];
    $tmpName= $file['tmp_name']; //php 파일을 받으면 임시저장소에 넣는다. 그곳이 tmp
 
    //임시 저장소 이미지를 원하는 폴더로 이동
    $dstName= "uploads/".date('Ymd_his').$srcName;
    $result=move_uploaded_file($tmpName, $dstName);
    if($result){
        echo "upload success\n";
    }else{
        echo "upload fail\n";
    }
 
 
    //글 작성 시간 변수
    $now= date('Y-m-d H:i:s');
 
    //insert하는 쿼리문
    $sql="insert into post(user_num, post_title, post_content, post_regtime, post_imgPath) values($user_num,'$postTitle','$postContent','$now','$imgPath')";
 
    $result =mysqli_query($conn, $sql); //쿼리를 요청하다. 
 
    if($result) echo "insert success \n";
    else echo "insert fail \n";
 
    mysqli_close($conn);
    
?>