/*LIBRERIAS NECESARIAS PARA IMPLEMENTAR EL PROTOCOLO, LA CONEXIÓN A LA RED Y LA LIBRERIA PARA LA COMUNICACIÓN EN SERIE*/
#include <SoftwareSerial.h>

#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <Modbus.h>
#include <ModbusIP_ESP8266.h>

ModbusIP Mb; //Se crea un objeto de tipo ModbusIP  llamado mb 
ESP8266WebServer serverL(80); //el objeto que me va a manejar la pagina que esta dentro del node para configurar la red wifi 
SoftwareSerial arduino(13,15);//RX, TX objeto para la trasmisión de datos con el arduino

const char* ssid = "Reyes Vargas";
const char* password = "24152665Reyes";
String ssidActual;
String estado="conectado";
String ip;
String vredes[10], senales[10];
int nRedes=0;
bool coneccion=false;
//---------------- PAGINA PARA LA CONFIGURACION DEL WIFE DEL NODEMCU
void handleRoot() {
  String pagina = "<html>\
  <head>\
  <title>Configuracion Wifi</title>\
  <script type=\"text/javascript\">\
    var contenedor;\
    var seleccionado;\
    var contrasenaVisible = false;\
    var boton = document.createElement(\"button\");\
    boton.innerHTML = \"Conectar\";\
    boton.setAttribute(\"onclick\",\"conectar()\");\
    var contrasena = document.createElement(\"input\");\
    contrasena.type = \"text\";\
    var label = document.createElement(\"label\");\
    label.innerHTML = \"Ingrese contraseña:\";\
    function addElement(id,contenido)\
    {\
      var div = document.createElement(\"div\");\
      div.innerHTML = contenido;\
      div.style=\"padding:10px\";\
      div.id=id;\
      div.setAttribute(\"onclick\",\"seleccionar('\"+id+\"')\");\
      contenedor = document.getElementById(\"contenedor\");\
      contenedor.appendChild(div);\
    }\
    function deleteElement(id)\
    {\
      if(id==seleccionado)\
      {\
        document.body.removeChild(boton);\
        if(contrasenaVisible)\
        {\
          document.body.removeChild(label);\
          document.body.removeChild(contrasena);\
          contrasenaVisible=false;\
        }\
        seleccionado=null;\
      }\
      contenedor = document.getElementById(\"contenedor\");\
      var hijo = document.getElementById(id);\
      contenedor.removeChild(hijo);\
    }\
    function modifyElement(id,señal)\
    {\
      var hijo = document.getElementById(id+\"senal\");\
      hijo.innerHTML = señal;\
      var color;\
      if(señal==\"baja\")\
        color=\"red\";\
      else if(señal==\"normal\")\
        color=\"orange\";\
      else if(señal==\"buena\")\
        color=\"#bfbf00\";\
      else {\
        color=\"green\";\
      }\
      hijo.color=color;\
    }\
    function seleccionar(id)\
    {\
      if(seleccionado!=null)\
      {\
        var anterior = document.getElementById(seleccionado);\
        anterior.style=\"padding:10px\";\
      }\
      var divSeleccionado = document.getElementById(id);\
      divSeleccionado.style=\"background:gray;padding:10px\";\
      seleccionado=id;\
      var seguridad = document.getElementById(id+\"seguridad\");\
      if(seguridad.innerHTML==\"protegida\")\
      {\
        document.body.appendChild(label);\
        document.body.appendChild(contrasena);\
        contrasenaVisible=true;\
      }\
      else if(contrasenaVisible)\
      {\
        document.body.removeChild(contrasena);\
        document.body.removeChild(label);\
        contrasenaVisible=false;\
      }\
      document.body.appendChild(boton);\
    }\
    function conectar()\ /*funcion que se llama al seleciona la red a conectarse*/
    {\
      var red = document.getElementById(\"red\");\
      red.innerHTML=\"Red configurada: \"+seleccionado;\
      var estado = document.getElementById(\"estado\");\
      estado.innerHTML=\"Estado: conectando...\";\
      var seguridad = document.getElementById(seleccionado+\"seguridad\");\
      if(seguridad.innerHTML==\"abierta\")\
        enviar('/conectar','red='+seleccionado+'&contrasena=\"\"');\
      else {\
        enviar('/conectar','red='+seleccionado+'&contrasena='+contrasena.value);\
      }\
      contrasena.value=\"\";\
    }\
    var READY_STATE_COMPLETE=4;\
    var peticion_http = null;\
    var busy=0;\
    function inicializa_xhr() {\
      if (window.XMLHttpRequest) {\
        return new XMLHttpRequest();\
      } else if (window.ActiveXObject) {\
        return new ActiveXObject(\"Microsoft.XMLHTTP\");\
      }\
    }\
    function enviar(url,parametros) {\
      busy=1;\
      peticion_http = inicializa_xhr();\
      if(peticion_http) {\
        peticion_http.onreadystatechange = procesaRespuesta;\
        peticion_http.open(\"POST\", url, true);\
        peticion_http.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");\
        peticion_http.send(parametros);\
      }\
    }\
    function procesaRespuesta() {\
      if(peticion_http.readyState == READY_STATE_COMPLETE) {\
        if (peticion_http.status == 200) {\
          busy=0;\
          manejarRespuesta(peticion_http.responseText);\
        }\
      }\
    }\
    function manejarRespuesta(respuesta)\
    {\
        var arreglo = respuesta.split(\"|\");\
        if(arreglo[0] == \"redes\")\
          actualizarRedes(arreglo[1]);\
        else if(arreglo[1]!=\"error\")\
          setEstado(arreglo[1]);\
    }\
    function actualizarRedes(lista)\ /*se encarga de actualizar las redes, a traves de ajax*/
    {\
      if(lista!=null){\
        var arreglo = lista.split(\"$\");\
        for(var i=0;i<arreglo.length-1;i++)\
        {\
          linea = arreglo[i].split(\"&\");\
          if(linea[0]==\"modificar\")\
          {\
            modifyElement(linea[1],linea[2]);\
          }else if(linea[0]==\"eliminar\")\
          {\
            deleteElement(linea[1]);\
          }else {\
            addElement(linea[0],linea[1]);\
          }\
        }\
        var estadoIp = arreglo[arreglo.length-1].split(\"*\");\
        var estado = document.getElementById(\"estado\");\
        estado.innerHTML=\"Estado: \"+estadoIp[0];\
        var ip = document.getElementById(\"ip\");\
        ip.innerHTML=\"IP: \"+estadoIp[1];\
      }\
    }\
    function setEstado(señal)\
    {\
      var separador = señal.split(\"&\");\
      var estado = document.getElementById(\"estado\");\
      estado.innerHTML=\"Estado: \"+separador[0];\
      var ip = document.getElementById(\"ip\");\
      ip.innerHTML=\"IP: \"+separador[1];\
    }\
    setInterval(function(){if(busy==0)enviar(\"/redes\",null);},3000);\
  </script>\
  </head>\
  <body>\
    <h1 id=\"red\">Red configurada: ";
  pagina += ssidActual;
  pagina += "</h1>\
    <h2 id=\"estado\">Estado: ";
  pagina+=estado;
  pagina += "</h2><h2 id=\"ip\">IP: ";
  pagina += ip;
  pagina += "</h2><br>\
    <div id=\"contenedor\" align=\"center\">\
      <h1>Redes Disponibles</h1>\
    </div>\
  </body>\
  </html>";
  serverL.send(200, "text/html", pagina); // llamado de la pagina
}

void conectar()
{
  serverL.send(200, "text/plain", "estado|conectando...&"+ip);
  WiFi.disconnect(); // se desconecta la red 
  coneccion=false;
  ssidActual = serverL.arg("red");
  char red[30];
  char pw[30];
  ssidActual.toCharArray(red,30);
  String contrasena = serverL.arg("contrasena");
  contrasena.toCharArray(pw,30);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssidActual);
  // conectar a la red wife que escoji en la pagina
  WiFi.begin(red,pw);
  
  Serial.print(red);
  Serial.print(" - ");
  Serial.println(pw);
  estado="conectando...";
  int cont=0;
  while (WiFi.status() != WL_CONNECTED && cont<20) { // en el while se espera por 10 seg a la conexion
    delay(500);
    Serial.print(".");
    cont++;
  }
  if(cont==20){ // mensaje de erro de conexion pasado los 10 seg  
    estado="error";
    Serial.println("error de conexion");  
  }
  else{ // conexion exitosa, imprimo la ip que le asigno el router al nodemcu
    coneccion=true;
    ip=WiFi.localIP().toString();
    estado="conectado";
    Serial.println("");
    Serial.println("WiFi connected");  
    Serial.print("IP address: ");
    
    Serial.println(ip);
  }
  
  
  delay(100);
  
}

String calcularSenal(long rssi) // funcion que me calcula la intencidad de la se;al de las redes que tengo cerca 
{
  if(rssi<=-80)
    return "mala";
  else if(rssi<-70)
   return "normal";
  else if(rssi<-60)
   return "buena";
  else
    return "muy buena";
}

void redes() // imprimir en la pagina con que intencidad esta cada red 
{
  int n = WiFi.scanNetworks();
  String pagina = "redes|",seguridad;
  int marcas[10];
  for(int i=0;i<n;i++)
  {
    String auxRed = WiFi.SSID(i);
    String auxSenal = calcularSenal(WiFi.RSSI(i));
    
    int ban = 0;
    for(int i=0;i<nRedes;i++)
    {
      if(vredes[i]==auxRed)
      {
        if(senales[i]==auxSenal)
        {
          ban=1;
        }
        else
        {
          ban=2;
        }
        marcas[i]=1;
        break;
      }
    }
    if(ban==1)
      continue;
    else if(ban==2)
    {
      pagina += "modificar&";
      pagina += auxRed + "&";
      pagina += auxSenal;
    }else
    {
      vredes[nRedes] = auxRed;
      senales[nRedes] = auxSenal;
      String color;
      if(senales[nRedes]=="baja")
        color="red";
      else if(senales[nRedes]=="normal")
        color="orange";
      else if(senales[nRedes]=="buena")
        color="#bfbf00";
      else {
        color="green";
      }
      seguridad = WiFi.encryptionType(i)==7?"abierta":"protegida";
      pagina += vredes[nRedes] + "&";
      pagina += "<font id=\"" + vredes[nRedes] + "nombre\">" + vredes[nRedes] + "</font>  ";
      pagina += "<font id=\"" + vredes[nRedes] + "senal\" color=\""+color+"\">" + senales[nRedes] + "</font>  ";
      pagina += "<font id=\"" + vredes[nRedes] + "seguridad\">" + seguridad + "</font>  ";
      
      marcas[nRedes]=1;
      nRedes++;
      
    }
      pagina += "$";
      if(nRedes == 10)
        break;
  }

  for(int i=0;i<nRedes;i++)
  {
    if(marcas[i]!=1)
    {
      pagina += "eliminar&";
      pagina += vredes[i]+"$";
    }
  }
  pagina += estado + "*" + ip;
  
  serverL.send(200, "text/plain", pagina);
}



void setup() {
  Serial.begin(115200);
  arduino.begin(9600); // velocidad a trasmitir los datos 
  delay(20);

  pinMode(2, OUTPUT);
  digitalWrite(2,1);


  
  ssidActual = WiFi.SSID();
  int cont=0;
  while (WiFi.status() != WL_CONNECTED && cont<20) { // en el while se espera por 10 seg a la conexion
    delay(500);
    Serial.print(".");
    cont++;
  }
  if(cont==20){ // mensaje de erro de conexion pasado los 10 seg 
    estado="desconectado";
    WiFi.disconnect();
    Serial.println("error de conexion");  
  }
  else{ // conexion exitosa, imprimo la ip que le asigno el router al nodemcu
    coneccion=true;
    Serial.println("");
    Serial.println("WiFi connected");  
    Serial.println("IP address: ");
    ip=WiFi.localIP().toString();
    Serial.println(ip);
    
  }
 // se llaman a las funciones de la pagina 
  serverL.on("/", handleRoot); 
  serverL.on("/conectar", conectar);
  serverL.on("/redes", redes);
  serverL.begin(); 
// se configura la red a la que va ir conectado el modbus 
  Mb.config(ssid, password);
//Se configuran los coils y registros del objeto ModbusIP
  Mb.addCoil(0);
  Mb.addCoil(1);
  Mb.addCoil(2);
  Mb.addCoil(3);
  Mb.addCoil(4);
  Mb.addCoil(5);
  Mb.addHreg(6, 0);
  Mb.addHreg(7, 0);
  Mb.addHreg(8, 0);
  Mb.addHreg(9, 0);
  Mb.addHreg(10, 0);
  Mb.addHreg(11, 0);

  // Print the IP address
  Serial.println(WiFi.localIP());
  delay(100); 
}





void loop() {
    serverL.handleClient();
    if(WiFi.status() != WL_CONNECTED && coneccion) // validar si estoy conectado 
    {
      Serial.println("loop disconnect"); // mensajes de desconeccion 
      WiFi.disconnect();
      estado="desconectado";
      coneccion=false;
    }
    if(coneccion) // si esta conectado a la red entra en el if
    {
      Mb.task(); //funcion que permite escuchar comandos Modbus
	    delay(200);
      String linea;
      for(int i=3;i<6;i++){ // escribir el valor de los coils en el string linea, son coils de salida 
        linea += i;
        linea += ":";
        linea += (Mb.Coil(i)?1:0);
        linea += "/";
      }
      for(int i=9;i<12;i++){ // escribir el valor de los registro en el string linea, son registros de salida
        linea += i;
        linea += ":";
        linea += Mb.Hreg(i);
        linea += "/";
      }
      arduino.println(linea);// enviar al arduino la informacion de los registro y coils guargados en linea 
      for(int i=0;i<3;i++){ // escribir el valor de los coils en el string linea, son coils de entrada
        linea += i;
        linea += ":";
        linea += (Mb.Coil(i)?1:0);
        linea += "/";
      }
      for(int i=6;i<9;i++){// escribir el valor de los registro en el string linea, son registros de entrada
        linea += i;
        linea += ":";
        linea += Mb.Hreg(i);
        linea += "/";
      }
      Serial.println(linea); // imprimir en consola la variable linea 
  
      if(arduino.available()>0) // si hay bytes en el puerto y el numero de bytes disponibles para leer desde el puerto
      {
        for(int i=0;i<6;i++) // leo lo que me llega del arduino 
        {
          int var = arduino.parseInt();
          if(var<6)
            Mb.Coil(var,arduino.parseInt()==1); // coils
          else
            Mb.Hreg(var,arduino.parseInt()); // registros 
        }
        if(Mb.Hreg(8)>=900) // prueba, encender el led del nodencu dependiendo lo que me llegue en el registro 8 
          digitalWrite(2,0);
        else
          digitalWrite(2,1);
    }
  }
  delay(200);

}


