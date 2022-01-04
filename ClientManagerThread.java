import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.Socket;

// 클라이언트가 보내는 메세지를 받아서 다시 클라이언트에게 보내는 스레드
public class ClientManagerThread extends Thread{

    private Socket m_socket;
    private String m_ID;

    @Override
    public void run(){
        super.run();
        try{
            BufferedReader in = new BufferedReader(new InputStreamReader(m_socket.getInputStream()));
            String text;

            while(true){
                text = in.readLine();
                if(text!=null) {
                    for(int i=0;i<MyServer.m_OutputList.size();++i){
                        MyServer.m_OutputList.get(i).println(text);
                        MyServer.m_OutputList.get(i).flush();
                    }
                }
            }


        }catch(IOException e){
            e.printStackTrace();
        }
    }
    public void setSocket(Socket _socket){
        m_socket = _socket;
    }
}
