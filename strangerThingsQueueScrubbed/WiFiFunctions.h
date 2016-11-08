#include <ESP8266WiFi.h>

void setupWifi(char* ssid, char* password)
{
  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");  
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

//Note : not at all sure if this strips header correctly, also potentially adds an extra line feed
String getWebPage(char* host, String url)
{
  Serial.print("connecting to ");
  Serial.println(host);
  
  // Use WiFiClient class to create TCP connections
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    return "";
  }
  

  Serial.print("Requesting URL: ");
  Serial.println(url);
  
  // This will send the request to the server
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" + 
               "Connection: close\r\n\r\n");
  delay(1000);
  
  // Read all the lines of the reply from server and print them to Serial
  if (!client.available())
  {
    Serial.println("Failure");
    return "";
  }
  String toReturn = "";
  bool doneWithHeaders = false;
  while(client.available()){
    String line = client.readStringUntil('\n');
    if (doneWithHeaders)
    {
      toReturn += line;
      if (client.available())
        toReturn += "\n";
    }
      
    if(line.length() == 1)
      doneWithHeaders = true;
  }

  Serial.print(toReturn);

  return toReturn;
}

