<?php
// root 계정이 아닌 phpMyAdmin에 접근 하기 위해 서로 만든 jun이라는 계정의 아이디와 비밀번호를 입력해준다. 
// -> 'jun'이라는 계정도 만들면서 슈퍼관리자 권한을 부여하였다. (phpmyadmin을 웹상에서 접속하면 root계정을 그대로 사용할 시 보안상의 문제가 있을 수 있기 때문에 
// 보통은 DB에서 다른 사용자를 새로 생성하고 그 사용자에게 슈퍼권한을 부여하여 이 계정을 통해 접근한다고 한다.)
// servername: 별거 다 해봤는데 이게(localhost:3306) 맞다.
// 원래 이거.
 $servername = "localhost:3306"; // AWS EC2 인스턴스에 할당된 퍼블릭 IPv4 주소. localhost는 안해봤지만 될 것 같다. localhost = 127.0.0.1
 $username = "jun";		  		 // 사용자 이름
 $password = "qhekwjdwns1@"; 	 // MySQL 비밀번호
 $db_name = "selfterior";  	     // DB 이름
 
 $conn = mysqli_connect($servername, $username, $password, $db_name);
 
?>