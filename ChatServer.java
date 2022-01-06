import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;

// 서버 소켓을 생성하고 클라이언트를 기다린다.
public class ChatServer {
    public static ArrayList<PrintWriter> m_OutputList;

    public static void main(String[] args){
    	
        m_OutputList = new ArrayList<PrintWriter>();

        try{
            ServerSocket s_socket = new ServerSocket(8888);
            while(true){
                Socket c_socket = s_socket.accept();
                ClientManagerThread c_thread = new ClientManagerThread();
                c_thread.setSocket(c_socket);

                m_OutputList.add(new PrintWriter(c_socket.getOutputStream()));
                System.out.println(m_OutputList.size());
                c_thread.start();
            }

        }
        catch(IOException e){
            e.printStackTrace();
        }
    }
}
