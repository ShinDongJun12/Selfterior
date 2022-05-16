<?php
		  session_start();
          include "dbcon.php";
          mysqli_query($conn,'SET NAMES utf8'); 

		  $memberPw = $_POST["user_pw1"];  // 비밀번호
		  $memberPw2 = $_POST["user_pw2"]; // 비밀번호 확인
          $user_num = $_POST["user_num"];// 유저 고유번호
	   
		  // 비밀번호 일치하는지 확인 (서버에서 예외처리)
		  if($memberPw !== $memberPw2){
			  echo "<script>
			  alert('비밀번호가 일치하지 않습니다.');
			  history.back();
		      </script>";
			  exit;
		  }else{
			  //비밀번호를 암호화 처리. -> md5 <역함수 자체가 존재하지 않아 역추적이 불가능한 함수를 '해쉬 함수'라고 한다.>
			  $memberPw = md5($memberPw);
		  }
	   
          // 비밀번호 재설정.  
          $sql = "UPDATE members SET user_pass='$memberPw' WHERE user_num=$user_num"; 
          $result = mysqli_query($conn, $sql); 

		  // 정상적으로 변경 하였으면
		  if($result){
			  echo "<script>
			  alert('비밀번호가 재설정 되었습니다.');
			  history.back();
			  </script>";
			  exit;

		  }else{
    		  echo "<script>
    		  alert('비밀번호 재설정을 실패하였습니다.');
			  history.back();
    		  </script>";
    		  exit;
		  }