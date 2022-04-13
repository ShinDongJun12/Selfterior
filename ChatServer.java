import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.File;
import java.io.IOException;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.Arrays;
import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.ArrayList;
import java.util.Date;
import java.util.List; 

import java.net.InetSocketAddress;
import java.net.SocketException;

// json-simple-1.1.1.jar (json simple 라이브러리)
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;


public class ChatServer { 

    public static ArrayList<ServerRecThread> userListMap; // 현재 접속한(소켓이 생성되어있는)유저리스트 맵 
 
    ServerSocket serverSocket = null; // 서버 소켓
    Socket socket = null; // 클라이언트 소캣
    ServerRecThread serverRecThread; // 클라이언트로부터 데이터를 받는 스레드
    // static int connUserCount = 0; //서버에 접속된 유저 카운트
   
    // 생성자
    public ChatServer(){

        userListMap = new ArrayList<ServerRecThread>(); // ArrayList생성.
    }

    // 클라이언트로부터 오는 요청 체크.
    public void init(){

        try{

            serverSocket = new ServerSocket(8888); // 8888포트로 서버소켓 객체생성.
            System.out.println("◀ ◁ ◀ ◁ 채팅 서버가 실행되었습니다. ▷ ▶ ▷ ▶");
           
            // 채팅서버가 계속 돌면서 클라이언트들의 접속을 기다림.
            while(true){
                
                socket = serverSocket.accept(); // .accept(): 클라이언트의 접속을 기다리다가 접속이 되면 Socket객체를 생성.
                System.out.println(socket.getInetAddress()+":"+socket.getPort()+":"+socket); // 클라이언트 정보 (ip, 포트) 출력

                serverRecThread = new ServerRecThread(socket); // ServerRecThread 스레드 생성.
                userListMap.add(serverRecThread); // 유저리스트에 저장.
                serverRecThread.start(); // ServerRecThread 스레드 실행.
            }      
           
        }catch(Exception e){
            e.printStackTrace();
        }
    }
   
   
    /** 메시지 전달 메서드 */
    public void sendMsg(String message){      
        
        Iterator it = userListMap.iterator(); //it객체를 만들어 <Integer>로 써주는게 훨씬 낫다.
        //numbers라는 arraylist의 iterator메소드를 호출해서 it라는 iterator데이터 타입의 변수 객체를 만든다.
        System.out.println("\niterator");
 
        // * hasNext() : 읽어올 요소가 남아있는지 확인하는 메서드, 요소가 있으면 true, 없으면 false. (즉, 채팅방에 존재하는 유저수 만큼 while문을 반복한다는 뜻)
        while(it.hasNext()){ // it가 hasNext로 가져올 element가 있는지 확인

            try{
                ServerRecThread st = (ServerRecThread)it.next(); //it.next를 해서 그 return된 값은 value에 담는다.
                //return된 값이 object데이터 타입이라서 ServerRecThread로 캐스팅 해준다. 그래서 it를 <ServerRecThread>로 처리하는게 훨씬 바람직
                st.out.writeUTF(message);  // 클라이언트에 채팅 메시지 보내기.
            }
            catch(Exception e){

                System.out.println("예외:"+e);
            }
        }

        System.out.println(">> 메시지 전송완료");
    }
    
   
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 채팅서버(ChatServer) main메서드 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    public static void main(String[] args) {

        ChatServer ms = new ChatServer(); // ChatServer(채팅서버) 객체 생성.
        ms.init(); //init() 실행.
    }
//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>


//--- 내부 클래스 (ServerRecThread) ----------------------------------------------------------------------------------------------------------------------------------------------------------
   
    // 클라이언트로부터 읽어온 메시지를 다른 클라이언트(socket)에 보내는 역할을 하는 메서드
    class ServerRecThread extends Thread {
       
        Socket socket;
        DataInputStream in;
        DataOutputStream out;
    
        //생성자.
        public ServerRecThread(Socket socket){

            this.socket = socket;

            try{

                //Socket으로부터 입력스트림을 얻는다.
                in = new DataInputStream(socket.getInputStream());
                //Socket으로부터 출력스트림을 얻는다.
                out = new DataOutputStream(socket.getOutputStream());

                System.out.println(">> ServerRecThread 생성.");
            }
            catch(Exception e){

                System.out.println(">> ServerRecThread 생성자 예외:"+e);
            }

        }
       
    
        @Override
        public void run(){

            try{ 

                // 입력스트림이 null이 아니면 반복.
                while(in!=null){ 

                    // ★ 서버에서는 클라이언트로부터 받은 jsonString를 파싱하여 메시지를 보내줄 채팅방 고유번호 데이터를 얻는다. ★
                    String jsonString = in.readUTF(); // 입력스트림을 통해 읽어온 문자열을 jsonString에 저장.   
                        
                    System.out.println(">> 클라이언트로 부터 받은 데이터 : "+ jsonString);

                    // JSONObject jsonObject = new JSONObject(jsonString); // JSONObject 객체로 생성. 

                    // ※ 참고한 예제에는 이런식으로 클라이언트로부터 받아온 jsonString문자열을 JSONObject의 파라미터로 바로넣어서 객체를 만들었는데 
                    //    더 안전하게 파싱하기위해서 아래와 같은 방법으로 진행하였다.
                    JSONParser parser = new JSONParser();
                    Object obj = null;
                    JSONObject jsonObject = null;

                    try {
                        // parse() 를 사용하여 문자열을 Object로 변환
                        obj = parser.parse(jsonString);
                        // Object를 JSONObject로 변환
                        jsonObject = (JSONObject)obj;

                    } catch (ParseException e) {
                        e.printStackTrace();
                    }

                    String handlerKind = (String) jsonObject.get("handlerKind"); // 메시지 전달 목적을 구분하기 위한 변수 저장.

                    // 로그아웃 처리 목적.
                    if(handlerKind.equals("logout")){

                        int remove_user_num = ((Long)jsonObject.get("remove_user_num")).intValue(); // 로그아웃한 유저 고유번호.
                        String remove_user_num_str = Integer.toString(remove_user_num);

                        userListMap.remove(remove_user_num_str);

                        System.out.println(remove_user_num_str+"번 유저 로그아웃 처리.");
                        System.out.println("총 인원수 : " + userListMap.size());
                    }
                    // 채팅 메시지 전달 목적.
                    else{

                        // ★ 채팅 서버는 에코서버이므로 말그대로 받은 채팅 메시지 데이터를 그대로 전달하는 역할만 한다.
                        System.out.println("채팅 메시지 받음");

                        sendMsg(jsonString); // 메시지 전송.
                    //------------------------------------------------- 메세지 처리
                    }
   
                }//while()---------

            }
            catch(Exception e){

                System.out.println("ChatServerRec:run():"+e.getMessage() + "----> ");
            }
            finally{
                
                //예외가 발생할때 퇴장. 해쉬맵에서 해당 데이터 제거.
                //보통 종료하거나 나가면 java.net.SocketException: 예외발생
                if(userListMap!=null){

                    //clientMap.remove(user_num);
                    //sendGroupMsg(room_num,"## "+ user_num + "님이 퇴장하셨습니다.");
                    //System.out.println("##현재 서버에 접속된 유저는 "+(--ChatServer.connUserCount)+"명 입니다."); // connUserCount = 현재 접속한 유저 수 -> -1해준값 출력.
                }              
            }
        }//run()------------

    }//class ChatServerRec-------------
    

}
