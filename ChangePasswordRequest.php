<?php
		  session_start();
          include "dbcon.php";
          mysqli_query($conn,'SET NAMES utf8'); 

		  $changePass = $_POST["changePass"]; // 비밀번호 확인
          //비밀번호를 암호화 처리. -> md5 <역함수 자체가 존재하지 않아 역추적이 불가능한 함수를 '해쉬 함수'라고 한다.>
		  $memberPw = md5($changePass);
          $userNum = $_POST["userNum"]; // 고유 식별자 (숫자를 문자로 보내서 다시 PHP에서 숫자로 변환한다.)
          $resultNum = (int)$userNum;
	   
		  // 비밀번호 일치여부에 따른 예외처리는 서버에서 따로 하지 않는다.
          // (클라이언트 단에서 일치하지 않거나 비밀번호 작성 조건에 맞지 않으면 버튼자체가 활성화 되지 않기 때문.)
	   
          // 비밀번호 변경.  
          $sql = "UPDATE members SET user_pass='$memberPw' WHERE user_num=$resultNum"; 
          $result = mysqli_query($conn, $sql); 

		  // 정상적으로 변경 하였으면
		  if($result){

			$response = array(); // response라는 배열 생성
			$response["success"] = true; 
	
			echo json_encode($response);
			exit();

		  }else{

			$response = array(); // response라는 배열 생성
			$response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
			
			echo json_encode($response);
			exit();
 		  }