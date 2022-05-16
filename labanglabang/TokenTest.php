<?php 
    
    $jwt = new JWT();

    $email = "simple@mail.com";
    $email = base64_encode($email); // .이 들어가도 JWT가 분리되지 않기 위한 base64 인코딩
    
    $password = "password..1234";
    $password = base64_encode($password); // .이 들어가도 JWT가 분리되지 않기 위한 base64 인코딩
    
    
    // 유저 정보를 가진 jwt 만들기
    $token = $jwt->hashing(array(
        'exp' => time() + (360 * 30), // 만료기간
        'iat' => time(), // 생성일
        'id' => 10,
        'email' => $email,
        'password' => $password
    ));
    
    // *var_dump() : var의 정보를 dump해주는 함수. -> ()안에 있는 변수형, 즉 변수가 int인지, float인지 array등등 인지 출력해준다.
    var_dump($token);


    
    class JWT
    {
        protected $alg;
        protected $secret_key;

        //  생성자
        function __construct()
        {
            //사용할 알고리즘
            $this->alg = 'sha256';

            // 비밀 키
            $this->secret_key = "your secret key";
        }

        // jwt 발급하기
        function hashing(array $data): string
        {
            // 헤더 - 사용할 알고리즘과 타입 명시
            $header = json_encode(array(
                'alg' => $this->alg,
                'typ' => 'JWT'
            ));

            // 페이로드 - 전달할 데이터
            $payload = json_encode($data);

            // 시그니처
            $signature = hash($this->alg, $header . $payload . $this->secret_key);

            return base64_encode($header . '.' . $payload . '.' . $signature);
        }

    }
?>