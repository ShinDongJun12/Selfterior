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
    
    <title>라방라방 비밀번호 재설정</title>

        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="./images/selfteriorFavicon.ico"/>
        <!-- Bootstrap core CSS -->
        <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <!-- JavaScript -->
        <!-- mySignupForm.js가 필요한 이유는 실시간으로 일치/불일치 여부를 사용자에게 알려주기 위함이 크다. -->
        <script type="text/javascript" src="./labangJS/ResetPass.js"></script>

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
            background-color: #fc7120;
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
            color: #fc7120; text-decoration: none; 
        }

        .links{
            text-align: center;
            margin-bottom: 10px;
        }

    </style>

  </head>

  <body cellpadding="0" cellspacing="0" marginleft="0" margintop="0" width="100%" height="100%">
	
    
    <!-- start header div font-family: 굴림, 돋움, 궁서, Arial, 등등 ;align="center"--> 
    <div class="container">
    <div id="header">
        <h5><img src="./labangImages/mainicon.png" alt="메인 아이콘" height="35" width="35"/><b style="padding-top: 10px;"> 라방라방</b></h5>
    </div>
    </div>
    <br>

<?php
    // DB연결
    include "dbcon.php";
    require "JWT.php"; // JWT 클래스가 선언되어있는 php파일을 포함한다.        

    mysqli_query($conn,'SET NAMES utf8'); 

    // *** JWT토큰 문자열이 존재유무 체크. [isset(): 변수가 존재하는지 체크, empty(): 변수의 값이 0 또는 null 값인지 체크] ***
    // JWT토큰 문자열이 존재하면
    if(isset($_GET['reset_password_token']) && !empty($_GET['reset_password_token'])){
        
        $jwt = new JWT();

        $token = $_GET['reset_password_token']; // 받아온 토큰 저장. (String)

        // jwt에서 유저 정보 가져오기
        $data = $jwt->dehashing($token); // JWT클래스에 정의해둔 토큰 파싱 함수를 실행하여 그 결과값을 받아온다.
    
        // 시그니처 오류인 경우.
        if($data === "시그니쳐 오류")
        {
?>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
            <div class="card align-middle" style="width:25rem;">
            <div class="card-title" style="margin-top:30px;">
                <!-- <h3 class="card-title" style="color:#fc7120;"><b>비밀번호 재설정</b></h3> -->
            </div>
            <div class="card-body">
                <p><b>URL정보가 올바르지 않습니다.</b></p>
            </div>
        </div>
        <br>
<?php 
        }
        // 만료 오류인 경우.
        else if($data === "만료 오류")
        {
?>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
            <div class="card align-middle" style="width:26rem;">
            <div class="card-title" style="margin-top:30px;">
                <!-- <h3 class="card-title" style="color:#fc7120;"><b>비밀번호 재설정</b></h3> -->
            </div>
            <div class="card-body">
                <p><b>비밀번호 재설정 페이지 유효 기간이 만료되었습니다.</b></p>
            </div>
        </div>
        <br>
<?php 
        }
        // 토큰이 정상적이고 유효한 경우.
        else{
            
            $parted = explode('.', base64_decode($token));

            $payload = json_decode($parted[1], true); // 비밀번호를 재설정 하려는 유저의 고유번호 데이터.

            $userNum = base64_decode($payload['user_num']); // 토큰 파싱해서 유저 고유번호 값 저장.

            // * var_dump(): 말그대로 var의 정보를 dump 해주는 함수. -> ()안에 있는 변수형, 즉 변수가 int인지, float인지, array등등 인지 출력해준다.
            // var_dump($payload);
            // echo "email: " . base64_decode($payload['email']); // 유저 고유번호 값만 출력.



            // 지역 변수를 지정하고, MySQL escape 문자열을 사용하여 다시 한번 MySQL 주입 방지를 추가 합니다.
            // Verify data (참고했던 자료는 mysql_escape_string()합수를 사용하고 있었는데 mysqli방식으로 검색해서 적용하니 되더라.)
            // 유저 고유번호.
            $userNum = mysqli_real_escape_string($conn, $userNum);
            
            // 다음은 MySQL 쿼리로 데이터베이스의 데이터와 url 데이터를 비교 검사한다.
            // GET으로 받은 유저 고유번호와 일치하는 계정이 존재하는지 조회한다.
            $sql = "select * from members where user_num = $userNum";
            $res = $conn->query($sql);

            // 해당 유저 고유번호와 일치하는 계정이 있으면.
            if($res->num_rows >= 1){
?>            
<!-- ★ 최종적으로 토큰 기간이 유요하고, 받아온 이메일로 계정이 존재하면 비밀번호 재설정 입력창을 띄워준다. ★ -->
            <div class="card align-middle" style="width:25rem;">
                <div class="card-title" style="margin-top:30px;">
                    <h3 class="card-title" style="color:#fc7120;"><b>비밀번호 재설정</b></h3>
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
                        <!-- 유저 고유번호 값도 POST로 넘겨준다. -->
                        <input type="hidden" name="user_num" value="<?= $userNum;?>"/> 
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
<?php 
            }     
            // 해당 유저 고유번호와 일치하는 계정이 없으면
            else{
                // 일치하는 항목 없음 - invalid 잘못된 URL 또는 계정이 이미 활성화되었습니다. history.back();
                // 기본 문구: alert('URL이 잘못되었거나 이미 비밀번호 변경을 마쳤습니다.');
                echo "<script>
                alert('URL정보가 올바르지 않습니다.');
                <div class='statusmsg'>URL정보가 올바르지 않습니다.</div>
                </script>";
                exit;

            }    
        
        }
    }
    else
    {
        // 잘못된 접근 예외처리.
        echo "<script>
            alert('잘못된 접근 방식입니다. 전자 메일로 보낸 링크를 사용하십시오.');
            <div class='statusmsg'>잘못된 접근 방식입니다. 전자 메일로 보낸 링크를 사용하십시오.</div>
            </script>";
            exit;
    }   
?>
<!-- stop PHP Code -->
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <!-- Footer  <p class="m-0 text-center"> , <a class="footerglanlink" href="#"> : 개인정보 취급방침 -->
    <footer class="py-5" style="background-color: #fdf6f1; color : black;">
        <div class="container">
        <p class="m-0" style="font-size : 13px ; color : #848484;">상호: 라방라방 | 이메일: ehdwnstls12@naver.com | 연락처: 010-2222-3333 | 대표자명: 신동준 | 개인정보 책임자: 신동준 | 개인정보취급방침</p>
        <!-- <p class="m-0" style="font-size : 13px ;">이메일: ehdwnstls12@naver.com | 연락처: 010-2222-3333</p> -->
        <p class="m-0" style="font-size : 13px ;">Copyright &copy; Selfterior 2021</p>
        </div>
    <!-- /.container -->
    </footer>	
  </body>

 <!-- JavaScript -->
   <!-- <script >
     필요시 사용
    </script> -->

</html>