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
 
import org.json.*;
import java.util.*;

/*콘솔 멀티채팅 서버 프로그램*/
public class ChatServer {

    /** * HashMap<key자료형,value자료형> -> HashMap 파라미터
          HashMap<int,HashMap<int,ServerRecThread>> 이중 HashMap으로 가장 겉에는 채팅방이며, 채팅방 고유번호 key값에 대한 value값을 회원 HashMap으로 하여 방에 존재하는 회원으로 관리. */    
    HashMap<String,HashMap<String,ServerRecThread>> globalMap; // 채팅방별 해쉬맵을 관리하는 해시맵 (※ 채팅방은 고유번호로 구분하므로 int형 선언, 유저도 유저고유번호로 구분하므로)
    ServerSocket serverSocket = null;
    Socket socket = null;
    static int connUserCount = 0; //서버에 접속된 유저 카운트
   
    //생성자
    public ChatServer(){

        globalMap = new HashMap<String,HashMap<String, ServerRecThread>>();       
        //clientMap = new HashMap<String,DataOutputStream>(); //클라이언트의 출력스트림을 저장할 해쉬맵 생성.
        Collections.synchronizedMap(globalMap); //해쉬맵 동기화 설정.


        // 채팅서버를 시작할 때 현재 접속한 유저가 들어가있는 채팅방을 생성해준다.
       
        // HashMap<String,ServerRecThread> group01 = new HashMap<String,ServerRecThread>();
        // Collections.synchronizedMap(group01); //해쉬맵 동기화 설정.
       
        // HashMap<String,ServerRecThread> group02 = new HashMap<String,ServerRecThread>();
        // Collections.synchronizedMap(group02); //해쉬맵 동기화 설정.
       

        // globalMap.put("서울",group01);
        // globalMap.put("경기",group02);
        
    }

    // 클라이언트로부터 오는 요청 체크.
    public void init(){
        try{
            serverSocket = new ServerSocket(8888); // 8888포트로 서버소켓 객체생성.
            System.out.println("##서버가 시작되었습니다.");
           
            while(true){ //서버가 실행되는 동안 클라이언트들의 접속을 기다림.
                socket = serverSocket.accept(); //클라이언트의 접속을 기다리다가 접속이 되면 Socket객체를 생성.
                System.out.println(socket.getInetAddress()+":"+socket.getPort()); //클라이언트 정보 (ip, 포트) 출력
               
                Thread msr = new ServerRecThread(socket); //쓰레드 생성.
                msr.start(); //쓰레드 시동.
            }      
           
        }catch(Exception e){
            e.printStackTrace();
        }
    }
   
// 전체 채팅 필요할 때 사용.   
    /** 접속된 모든 클라이언트들에게 메시지를 전달. */
    // public void sendAllMsg(String msg){

    //     /*HashMap을 Iterator로 수정하여 프린트한다 
    //     Collection을 상속받은 객체들만 iterator를 쓸수 있다.
    //     * Collection : co.interator();
    //     * Set:  set.iterator()
    //     * List: li.interator() 
    //     * 
    //     //Collection을 상속받지 못한 Map는 iterator가 없다.
    //     * Map -> Set: keySet() -> iterator() (iterator를 쓰기 위해 Map을 Set으로 바꿔서 사용하기)
    //     * Map -> Set: entrySet() ->iterator() (iterator를 쓰기 위해 Map을 Set으로 바꿔서 사용하기)
    //     * Map -> Collection : values() -> iterator()  (iterator를 쓰기 위해 Map을 Collection으로 바꿔서 사용하기)
    //     * 
    //     globalMap은 구조 -> HashMap<String,HashMap<String,ServerRecThread>> 이다 따라서 클라이언트 정보를 감지고 있는 ServerRecThread에 접근하기위해서는 두번.keySet().iterator() 헤줘야 한다. */
    //     Iterator global_it = globalMap.keySet().iterator(); // Iterator : 반복자(패턴)
       
    //     // .hasNext() : 읽어 올 요소가 남아있는지 확인하는 메소드
    //     while(global_it.hasNext()){
    //         try{

    //             HashMap<String, ServerRecThread> it_hash = globalMap.get(global_it.next()); // .next(): 읽어 올 요소가 남아있는지 확인하는 메소드
    //             Iterator it = it_hash.keySet().iterator();

    //             while(it.hasNext()){
    //                 ServerRecThread st = it_hash.get(it.next());
    //                 st.out.writeUTF(msg); // DataOutputStream 클래스의 메서드중 하나이며, UTF-8 형식으로 코딩된 문자열을 출력한다.
    //             } 

    //         }catch(Exception e){
    //             System.out.println("예외:"+e);
    //         }
    //     }
    // }//sendAllMsg()-----------
   
   
    /** 해당 클라이언트가 속해있는 그룹에대해서만 메시지 전달. */
    public void sendGroupMsg(String roomNum,String jsonString){      
       
        HashMap<String, ServerRecThread> gMap = globalMap.get(roomNum); // 그룹 HashMap에서 채팅방 번호를 통해 해당 채팅방의 유저 HashMap객체를 생성한다.
        Iterator<String> group_it = globalMap.get(roomNum).keySet().iterator(); // 채팅방 고유번호를 받아온다. (keySet() -> 키값만 필요한 경우)
        
        // * hasNext() : 읽어올 요소가 남아있는지 확인하는 메서드, 요소가 있으면 true, 없으면 false 
        // 즉 채팅방에 존재하는 유저수 만큼 while문을 반복한다는 뜻.
        while(group_it.hasNext()){

            try{    

                ServerRecThread st = gMap.get(group_it.next()); // * next() : 다음 데이터를 반환
                //if(!st.chatMode){ //1:1대화모드가 아닌 사람에게만.
                    st.out.writeUTF(jsonString);  // 채팅 메시지 보내기.
                //}
            }
            catch(Exception e){

                System.out.println("예외:"+e);
            }
        }  

    }
    
   
    /** 각그룹의 접속자수와 서버에 접속된 유저를 반환하는 메소드 */
    // public String getEachMapSize(){
    //     return getEachMapSize(null);    
    // }
   
    
    /**각그룹의 접속자수와 서버에 접속된 유저를 반환 하는 메소드 추가 지역을 전달받으면 해당 지역을 체크 */
    // public String getEachMapSize(String loc){
       
    //     Iterator global_it = globalMap.keySet().iterator();
    //     StringBuffer sb = new StringBuffer();
    //     int sum=0;
    //     sb.append("=== 그룹 목록 ==="+System.getProperty("line.separator"));
    //     while(global_it.hasNext()){
    //         try{
    //             String key = (String) global_it.next();
               
    //             HashMap<String, ServerRecThread> it_hash = globalMap.get(key);
    //             //if(key.equals(loc)) key+="(*)"; //현재 유저가 접속된 곳 표시
    //             int size = it_hash.size();
    //             sum +=size;
    //             sb.append(key+": ("+size+"명)"+(key.equals(loc)?"(*)":"")+"\r\n");
               
    //         }catch(Exception e){
    //             System.out.println("예외:"+e);
    //         }
    //     }
    //     //sb.append("⊙현재 대화에 참여하고있는 유저수 :"+ ChatServer.connUserCount);
    //     sb.append("⊙현재 대화에 참여하고있는 유저수 :"+ sum+ "명 \r\n");
    //     //System.out.println(sb.toString());
    //     return sb.toString();
    // }
      
    //main메서드
    public static void main(String[] args) {

        ChatServer ms = new ChatServer(); //서버객체 생성.
        ms.init();//실행.
    }
   
   
//--- 내부 클래스 ----------------------------------------------------------------------------------------------------------------------------------------------------------
   
    // 클라이언트로부터 읽어온 메시지를 다른 클라이언트(socket)에 보내는 역할을 하는 메서드
    class ServerRecThread extends Thread {
       
        Socket socket;
        DataInputStream in;
        DataOutputStream out;
        // String name=""; //이름 저장
        // String loc="";  //채팅방 제목 저장
        String toNameTmp = null;//1:1대화 상대  
        String fileServerIP; //파일서버 아이피 저장
        String filePath; //파일 서버에서 전송할 파일 패스 저장.
        boolean chatMode; //1:1대화모드 여부
       
       
        //생성자.
        public ServerRecThread(Socket socket){

            this.socket = socket;

            try{

                //Socket으로부터 입력스트림을 얻는다.
                in = new DataInputStream(socket.getInputStream());
                //Socket으로부터 출력스트림을 얻는다.
                out = new DataOutputStream(socket.getOutputStream());
            }
            catch(Exception e){

                System.out.println("ServerRecThread 생성자 예외:"+e);
            }
        }//생성자 ------------
       
       
        /**접속된 유저리스트 문자열로 반환*/        
        // public String showUserList(){
           
        //     StringBuilder output = new StringBuilder("==접속자목록==\r\n");
        //     Iterator it = globalMap.get(loc).keySet().iterator(); //해쉬맵에 등록된 사용자이름을 가져옴.
        //     while(it.hasNext()){ //반복하면서 사용자이름을 StringBuilder에 추가
        //          try{
        //             String key= (String) it.next();                                    
        //             //out.writeUTF(output);
        //             if(key.equals(name)){ //현재사용자 체크
        //                 key += " (*) ";
        //             }    
                   
        //             output.append(key+"\r\n");                  
        //          }catch(Exception e){
        //              System.out.println("예외:"+e);
        //          }
        //      }//while---------
        //     output.append("=="+ globalMap.get(loc).size()+"명 접속중==\r\n");
        //     System.out.println(output.toString());
        //     return output.toString();
        //  }
       
       
       /** String -> JSONObject 변환 메서드 (클라이언트로부터 받은 jsonString을 SONObject로 파싱한다.) */    
    
       
        @Override
        public void run(){ //쓰레드를 사용하기 위해서 run()메서드 재정의

            HashMap<String, ServerRecThread> clientMap=null; //현재 클라이언트가 저장되어있는 해쉬맵        
           
            try{ 

                //입력스트림이 null이 아니면 반복.
                while(in!=null){ 

                    // ★ 서버에서는 클라이언트로부터 받은 jsonString를 파싱하여 메시지를 보내줄 채팅방 고유번호데이터를 얻는다. ★
                    String jsonString = in.readUTF(); //입력스트림을 통해 읽어온 문자열을 jsonString에 할당.                 
                    JSONObject jsonObject = new JSONObject(jsonString); // JSONObject 객체로 생성.

                    int message_num = jsonObject.getInt("message_num");
                    int room_num = jsonObject.getInt("room_num"); // 채팅방 고유번호.
                    int user_num = jsonObject.getInt("user_num");
                    String message_content = jsonObject.getString("message_content");
                    String regtime = jsonObject.getString("regtime");
                    String message_type = jsonObject.getString("message_type");
                    String user_profile_image = jsonObject.getString("user_profile_image");

                    // *containsKey() : 맵에서 인자로 보낸 키가 있는지 체크하는 메서드 
                    // (globalMap맵의 키값이 채팅방 고유번호이고 따라서 채팅방 고유번호가 존재하는지 체크하는 것이다.)
                    if(globalMap.containsKey(room_num)){
                        
                    //sendGroupMsg(room_num, "show|[##] "+ user_num + "님이 입장하셨습니다."); // 채팅방에 들어와있는 클라이언트에게만 채팅 메시지 보내기. -> 기존
                    sendGroupMsg(room_num, jsonString); // 채팅방에 들어와있는 클라이언트에게만 채팅 메시지 보내기.
                    clientMap= globalMap.get(room_num); //현재그룹의 해쉬맵을 따로 저장.
                    clientMap.put(user_num, this); //현재 ChatServerRec인스턴스를 클라이언트맵에 저장.
                    System.out.println(getEachMapSize()); //서버에 그룹리스트 출력.                       
                    //out.writeUTF("enterRoom#yes|"+room_num); //접속된 클라이언트에게 그룹목록 제공
                        
                    }
                    // 채팅방이 없으면
                    else{   
                        
                    // 키 값이 없으면 채팅방을 생성한다.
                    HashMap<String,ServerRecThread> group = new HashMap<String,ServerRecThread>();
                    Collections.synchronizedMap(group); //해쉬맵 동기화 설정.
                    globalMap.put(room_num,group);

                    // 메시지를 보낸다.
                    sendGroupMsg(room_num, jsonString); // 채팅방에 들어와있는 클라이언트에게만 채팅 메시지 보내기.
                    clientMap= globalMap.get(room_num); //현재그룹의 해쉬맵을 따로 저장.
                    clientMap.put(user_num, this); //현재 ChatServerRec인스턴스를 클라이언트맵에 저장.
                    System.out.println(getEachMapSize()); //서버에 그룹리스트 출력.                       
                    //out.writeUTF("enterRoom#yes|"+room_num); //접속된 클라이언트에게 그룹목록 제공                          
                    }

// 파일 보내는 경우 필요시 수정.                         
                    // else if(msgArr[1].trim().startsWith("/파일전송")){  
                           
                    //         if(!chatMode){
                    //             out.writeUTF("show|[##] 1:1대화중일때만 사용할수있는 명령어입니다. ");
                    //             continue;                              
                    //         }
                           
                    //         String[] msgSubArr = msgArr[1].split(" ",2);
                    //         if(msgSubArr.length!=2){
                    //             out.writeUTF("show|[##] 파일전송 명령어 사용법이 잘못되었습니다.\r\n usage : /파일전송 [전송할파일경로]");
                    //             continue;                              
                    //         }
                    //         filePath = msgSubArr[1];                           
                    //         File sendFile = new File(filePath);
                    //         String availExtList = "txt,java,jpeg,jpg,png,gif,bmp";
                           
                           
                    //         if(sendFile.isFile()){                     
                    //             String fileExt = filePath.substring(filePath.lastIndexOf(".")+1);
                    //             if(availExtList.contains(fileExt)){
                    //                 Socket s = globalMap.get(loc).get(toNameTmp).socket;
                    //                 //파일서버역할을 하는 클라이언트 아이피 주소 알기위해 소켓 객체 얻어옴.
                                   
                    //                 //System.out.println("s.getLocalSocketAddress()=>"+s.getLocalSocketAddress());
                    //                 //System.out.println("s.getLocalAddress()=>"+s.getLocalAddress());
                    //                 System.out.println("s.getInetAddress():파일서버아이피=>"+s.getInetAddress());
                    //                 //파일서버역할을 하는 클라이언트 아이피 출력
                                   
                    //                 fileServerIP = s.getInetAddress().getHostAddress();
                    //                 clientMap.get(toNameTmp).out.writeUTF("req_fileSend|[##] "+name +"님께서 파일["+sendFile.getName()+"] 전송을 시도합니다. \r\n수락하시겠습니까?(Y/N)");                        
                    //                 out.writeUTF("show|[##] "+toNameTmp +"님께 파일["+sendFile.getAbsolutePath()+"] 전송을 시도합니다.");
                                   
                    //             }else{
                                   
                    //                 out.writeUTF("show|[##] 전송가능한 파일이 아닙니다. \r\n["+availExtList+"] 확장자를 가진 파일만 전송가능합니다.");                             
                    //             } //if                         
                           
                    //         }else{                             
                    //             out.writeUTF("show|[##] 존재하지 않는 파일입니다.");                            
                    //         } //if
                    //     }else{
                    //         out.writeUTF("show|[##] 잘못된 명령어입니다.");
                    //     }//if
                       
                    // }


                    // else if(msg.startsWith("fileSend")){ //파일전송
                    //     //fileSend|result    
                    //     String result = msgArr[0];
                    //     if(result.equals("yes")){
                    //         System.out.println("##파일전송##YES");                             
                    //         try {                      
                    //             String tmpfileServerIP = clientMap.get(toNameTmp).fileServerIP;
                    //             String tmpfilePath = clientMap.get(toNameTmp).filePath;
                               
                    //             //fileSender|filepath;    
                    //             clientMap.get(toNameTmp).out.writeUTF("fileSender|"+tmpfilePath);
                    //             //파일을 전송할 클라이언트에서 서버소켓을 열고 filePath로 저장된 파일을 읽어와서 OutputStream으로 출력
                               
                    //             //fileReceiver|ip|fileName;
                    //             //String fileName = tmpfilePath.substring(tmpfilePath.lastIndexOf("\\")+1); //파일 명만 추출
                    //             String fileName = new File(tmpfilePath).getName();
                    //             out.writeUTF("fileReceiver|"+tmpfileServerIP+"|"+fileName);                                        
                               
                    //             /*리셋*/
                    //             clientMap.get(toNameTmp).filePath="";
                    //             clientMap.get(toNameTmp).fileServerIP="";
                               
                    //         } catch (IOException e) {
                    //             e.printStackTrace();
                    //         }
                    //     }else /*(result.equals("no"))*/{
                    //         clientMap.get(toNameTmp).out.writeUTF("show|[##] "+name+" 님께서 파일전송을 거절하였습니다.");
                    //     }//if                      
                       
                    // }
                    // else if(msg.startsWith("req_exit")){ //종료  
                       
                    // }
                    //------------------------------------------------- 메세지 처리
                   
                }//while()---------

            }
            catch(Exception e){

                System.out.println("ChatServerRec:run():"+e.getMessage() + "----> ");
                //e.printStackTrace();
            }
            finally{
                //예외가 발생할때 퇴장. 해쉬맵에서 해당 데이터 제거.
                //보통 종료하거나 나가면 java.net.SocketException: 예외발생
                if(clientMap!=null){

                    //clientMap.remove(user_num);
                    //sendGroupMsg(room_num,"## "+ user_num + "님이 퇴장하셨습니다.");
                    //System.out.println("##현재 서버에 접속된 유저는 "+(--ChatServer.connUserCount)+"명 입니다."); // connUserCount = 현재 접속한 유저 수 -> -1해준값 출력.
                }              
            }
        }//run()------------

    }//class ChatServerRec-------------
    

}