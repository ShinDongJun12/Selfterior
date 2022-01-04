<!-- <%@ page language="java" contentType="text/html; charset=UTF-8"
    pageEncoding="UTF-8"%>
      <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %> -->
<!DOCTYPE html>
<html lang="ko">
  <head>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <title>셀프테리어 비밀번호 재설정</title>

        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="./images/selfteriorFavicon.ico"/>
        <!-- Bootstrap core CSS -->
        <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <!-- JavaScript -->
        <!-- mySignupForm.js가 필요한 이유는 실시간으로 일치/불일치 여부를 사용자에게 알려주기 위함이 크다. -->
        <script type="text/javascript" src="./js/ResetPass.js"></script>

    <style>
        @import url("http://fonts.googleapis.com/earlyaccess/nanumgothic.css");
	
        html {
            height: 100%;
        }
        
        body {
            width:100%;
            height:100%;
            margin: 0;
            padding-top: 40px;
            padding-bottom: 40px;
            font-family: "Nanum Gothic", arial, helvetica, sans-serif;
            background-repeat: no-repeat;
        }

        #header{
            height: 45px; 
            align:"left";
            /* background: darkred;  */
            color: white;
        }
        
        #header h5{
            color: #6A6A6A; 
            font size:"13px";
            padding: 0px; 
            align:"left";
            /* 자간간격 */
            letter-spacing :0.1px; 
            font-weight: normal; 
            font-family: "고딕";
        }
        
        .card {
            margin: 0 auto; /* Added */
            float: none; /* Added */
            margin-bottom: 10px; /* Added */
        }

        #btn-Yes{
            background-color: #20B2AA;
            border: none;
        }
        
        .form-signin .form-control {
            position: relative;
            height: auto;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
                box-sizing: border-box;
            padding: 10px;
            font-size: 16px;
        }
        .checkbox{
            margin-right: 20px;
            text-align: right;
        }
        .card-title{
            margin-left: 10px;
        }

        a{ 
            color: #20B2AA; text-decoration: none; 
        }

        .links{
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
  </head>

  <body cellpadding="0" cellspacing="0" marginleft="0" margintop="0" width="100%" height="100%">
	
    <?php
    // DB연결
    include "dbcon.php";
    mysqli_query($conn,'SET NAMES utf8'); 

    // 처음 해야 될 일은 (이메일 & 해시) $_GET 변수들이 있는지 검사하는것 입니다.
    if(isset($_GET['nick']) && !empty($_GET['nick'])){
        
        // 지역 변수를 지정하고, MySQL escape 문자열을 사용하여 다시 한번 MySQL 주입 방지를 추가 합니다.
        // Verify data (참고했던 자료는 mysql_escape_string()합수를 사용하고 있었는데 mysqli방식으로 검색해서 적용하니 되더라.)
        // 사용자 닉네임
        $nick = mysqli_real_escape_string($conn, $_GET['nick']);
        
        // 다음은 MySQL 쿼리로 데이터베이스의 데이터와 url 데이터를 비교 검사한다.
        // GET으로 받은 닉네임과 일치하는 계정이 존재하는지 조회한다.
        $sql = "select * from members where user_nickname = '$nick'";
        $res = $conn->query($sql);
        // 해당 닉네임과 일치하는 계정이 있으면.
        if($res->num_rows >= 1){
    ?>
            <!-- start header div font-family: 굴림, 돋움, 궁서, Arial, 등등 ;align="center"--> 
            <div class="container">
            <div id="header">
                <h5><img src="./images/mainicon.png" alt="메인 아이콘" height="35" width="35"/><b style="padding-top: 10px;"> 셀프테리어</b></h5>
            </div>
            </div>
            <br>
            <!-- start PHP code -->
    <?php       
            // update 문으로 해당 email과 hash 그리고 activition값이 일치하는 계정의 activation값을 '1'로 바꾸어 주어 계정을 활성화 시켜준다.
            // $sqlgo = "update members set activation='1' where email='$email' and hash='$hash' and activation='0'";

            // //쿼리문이 정상적으로 실행됐으면 인증성공 메세지를 띄운다.
            // if($conn->query($sqlgo)){
            //     echo '<div class="statusmsg">이메일 인증에 성공하였습니다!!! </div>';
            //     echo '<div class="statusmsg">계정이 활성화 되었으므로 로그인이 가능합니다. </div>';
            // }
        }
        // 없으면
        else{
            // 일치하는 항목 없음 - invalid 잘못된 URL 또는 계정이 이미 활성화되었습니다. history.back();
            echo "<script>
			  alert('URL이 잘못되었거나 이미 비밀번호 변경을 마쳤습니다.');
			  <div class='statusmsg'>URL이 잘못되었거나 이미 비밀번호 변경을 마쳤습니다.</div>
		      </script>";
			  exit;
        }                  
    }
    else{
            // 잘못된 접근 예외처리.
            echo "<script>
			  alert('잘못된 접근 방식입니다. 전자 메일로 보낸 링크를 사용하십시오.');
			  <div class='statusmsg'>잘못된 접근 방식입니다. 전자 메일로 보낸 링크를 사용하십시오.</div>
		      </script>";
			  exit;
        }   
    ?>
    <!-- stop PHP Code -->

	<div class="card align-middle" style="width:25rem;">
		<div class="card-title" style="margin-top:30px;">
			<h3 class="card-title" style="color:#20B2AA;"><b>비밀번호 재설정</b></h3>
		</div>
	    <div class="card-body">
        <form action="./ResetPassword_ok.php" class="form-signin" name="mform" method="post" onsubmit="return checkSubmit()">
        <!-- <form action="resetPw" class="form-signin" method="POST"> -->
            <input type="password" name="user_pw1" id="exampleInputPassword1" class="form-control user_pw1" placeholder="비밀번호" />
            <br>
            <!-- <input type="password" name="pw" id="pw" class="form-control" placeholder="비밀번호" required ><br> --> 
            <input type="password" name="user_pw2" id="exampleInputPassword2" placeholder="비밀번호 확인" class="form-control user_pw2"/>
            <br>       
            <!-- <input type="password" name="pw2" id="pw2" class="form-control" placeholder="비밀번호 재확인" required><br> -->
            <div class="memberPw2Comment"></div>
            <!-- 사용자 닉네임 값도 POST로 넘겨준다. -->
            <input type="hidden" name="user_nick" value="<?= $nick;?>"/> 
            <br>
            <!-- <button type="button"  id="btn-Yes" onclick="regist()" class="btn btn-lg btn-primary btn-block"><b>비밀번호 재설정</b></button> -->
            <button type="submit" value="비밀번호 재설정" id="btn-Yes" class="btn btn-lg btn-primary btn-block"><b>비밀번호 재설정</b></button>
        </form>
            <!--실시간 일치/불일치 여부 알림-->
            <div class="formCheck">
                <input type="hidden" name="pwCheck2" class="pwCheck2" />
            </div>
        </div>
	</div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <!-- Footer  <p class="m-0 text-center"> , <a class="footerglanlink" href="#"> : 개인정보 취급방침 -->
    <footer class="py-5" style="background-color: #FAFBFB; color : black;">
        <div class="container">
        <p class="m-0" style="font-size : 13px ; color : #848484;">상호: 셀프테리어 | 이메일: ehdwnstls12@naver.com | 연락처: 010-2222-3333 | 대표자명: 신동준 | 개인정보 책임자: 신동준 | 개인정보취급방침</p>
        <!-- <p class="m-0" style="font-size : 13px ;">이메일: ehdwnstls12@naver.com | 연락처: 010-2222-3333</p> -->
        <p class="m-0" style="font-size : 13px ;">Copyright &copy; Selfterior 2021</p>
        </div>
    <!-- /.container -->
    </footer>	
  </body>
  
  <!-- JavaScript -->
  <!-- <script >
	// 비밀번호 정규식
	// var pwJ = /^[a-z0-9]{6,20}$/; 
	// var pwc = false;
	// var pwc2 = false;
	
	$("#pw").focusout(function(){
	     if($('#pw').val() == ""){
	   		$('#check').text('비밀번호를 입력해주세요.');
	   	  	$('#check').css('color', 'red');
	   	  	
	     }else if(!pwJ.test($(this).val())){
			$('#check').text('6~20자의 영문 소문자, 숫자만 사용가능합니다');
			$('#check').css('color', 'red');
	     }else{
	    	 pwc2 = true;
	    	 $('#check').hide();
	     }
	  });
	
  </script> -->
</html>