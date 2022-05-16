<?php 
    //ini_set('display_errors', true);

    include "dbcon.php";

    mysqli_query($conn,'SET NAMES utf8'); 

    $postNum = $_POST["postNum"]; // 댓글/답글이 작성된 게시물 고유번호
    $postNumInt = (int)$postNum; // int형으로 변환.

    $parentNum = $_POST["parentNum"]; // 댓글/답글 부모번호
    $parentNumInt = (int)$parentNum; // int형으로 변환.

    $itemPositionNum = $_POST["itemPositionNum"]; // 답글달려고 하는 댓글/답글 item position 값.
    $itemPositionNumInt = (int)$itemPositionNum; // int형으로 변환.

    $commentNum = $_POST["commentNum"]; // 댓글/답글 고유번호.
    $commentNumInt = (int)$commentNum; // int형으로 변환.

    $sameParentsArray = array();
    $value=0; // 최종 itemPosition 계산을 위해 뻬줄 값을 담을 변수.
    $resultNum=0; // 최종 itemPosition 값을 담을 변수.
    
    // 해당 게시물에 받아온 parent번호로 작성된 댓글/답글 총 개수 조회.  comment_num  comment_num기준 오름차순 -> comment_num가 빠른것 부터 정렬 
    $sql = "SELECT comment_num FROM comments WHERE post_num = $postNumInt AND parent = $parentNumInt ORDER BY regtime ASC"; //  regtime기준 오름차순 -> 빠른 시간 부터 정렬
    $ret = mysqli_query($conn, $sql); 

    $total = mysqli_num_rows($ret); // 총 개수를 가져온다.

    // 조회된 횟수만큼 while문 돌면서 $sameParents배열에 comment_num값을 순차적으로 추가한다. ex) key:'comment_num' -> value:comment_num값
    while ($sameParents = $ret->fetch_assoc()){

        array_push($sameParentsArray, $sameParents['comment_num']); // PHP 배열에 값 추가 (comment_num값만 조회하므로 comment_num값만 배열에 넣어준다.)

    }

    // 배열 크기만큼 for문 돌려준다. count($sameParentsArray)
    for($i=0; $i < count($sameParentsArray); $i++){

        // 해당 인덱스의 값과 받아온 댓글/답글 고유번호가 같으면 순번을 체크해서 답글이 들어갈 itemPosition 값을 계산한다.
        if($sameParentsArray[$i] == $commentNumInt){

            $value = $total-$i; // 빼줄값 세팅.

            break;
        }
        
    }
    

    // 최종 결과값 음수 예외처리.
    if($itemPositionNumInt-$value < 0){

        $resultNum = 0; 
    }
    else{

        // 최종 itemPosition 값 세팅.
        $resultNum = ($itemPositionNumInt-$value)+1; // (※ 새로달 답글을 최종 itemPosition 값에 넣을때 add()로 넣기 때문에 itemPosition에 추가되고 기존에 itemPosition에 있던 값이 뒤로 한칸 밀린다 따라서 최종 itemPosition+'1'을 해주어야 정렬 순서가 내가 원하는대로 출력된다.)
    }

    $response = array(); // response라는 배열 생성
    $response["success"] = true; 
    $response["resultNum"] = $resultNum; 

    echo json_encode($response);
    
    exit();
    
    // // 조회 실패
    // }else{
    //     $response = array(); // response라는 배열 생성
    //     $response["success"] = false; // response배열에서 success라는 이름을 가진 변수의 디폴트값을 false로 초기화 해준다.
        
    //     echo json_encode($response);
    //     exit();
    // }
?>