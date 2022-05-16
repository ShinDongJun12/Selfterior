$(function(){
 
    // var은 클래스 변수선언이다.
    //아이디 중복 확인 소스 (이거 signup페이지에서 각각의 클래스명을 받아오는게 아닌가 싶다. comment부분은 이거 예제 디비 생성할때 자기가 따로 달아준거 때문에 다는 듯하다. )
    //var memberIdCheck = $('.memberIdCheck'); // 중복확인 버튼 사용시 필요하다.
    var memberPw = $('.user_pw1');
    var memberPw2 = $('.user_pw2');
    var memberPw2Comment = $('.memberPw2Comment');

    var pwCheck2 = $('.pwCheck2');
 
    //비밀번호 동일 한지 체크
    memberPw2.blur(function(){  // -> 이뜻은 비밀번호 확인란을 입력 후 그 확인란을 떠날때 함수가 작동한다는 뜻.
        
        // 이거안됨 처리
        // if(strlen(memberPw.val()) > 7 && strlen(memberPw.val()) < 17)
        // {
            if(memberPw.val() == memberPw2.val()){
                memberPw2Comment.text('비밀번호가 일치합니다.');
                //memberPw2Comment.css('color', 'green');
                pwCheck2.val('1');
            }
            // 값 비교 안되는듯...
            else if(memberPw.val() == '' && memberPw2.val() == ''){
                memberPw2Comment.text('');
                pwCheck2.val('0');
            }
            else{
                memberPw2Comment.text('비밀번호가 일치하지 않습니다.');
                //memberPw2Comment.css('color', 'red');
                pwCheck2.val('0');
            }
        // }
        // else
        // {
        //     memberPw2Comment.text('비밀번호는 8 ~ 16자리로 설정해주세요.');
        //     //memberPw2Comment.css('color', 'red');
        //     pwCheck2.val('0');
        // }

    });

});

function checkSubmit(){
    
    var pwCheck2 = $('.pwCheck2');
    
    //val()은 양식(form)의 값을 가져오거나 값을 설정하는 메소드입니다.
    if(pwCheck2.val() == '1'){
        res = true;
    }else{
        res = false;
    }

    if(res == false){
        // if(pwCheck2.val() == '1')
        // {
            alert('입력정보를 다시 확인해 주세요.');
        // }
        // else
        // {
        //     alert('비밀번호는 8 ~ 16자리로 설정해주세요.');
        // }
    
    }
    return res;
}