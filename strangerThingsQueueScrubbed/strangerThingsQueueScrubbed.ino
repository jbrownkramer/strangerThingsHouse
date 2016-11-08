
#include "WalgreenLights.h"
#include "WiFiFunctions.h"

//Globals
class WalgreenLights *lights, *lights2;
class WalgreenLights *lightArray[2];
char *strands[] = {"**zyxwvutsrqijk", "*abcdefgh*ponml"};
unsigned char state[15];
String lastString = "";
char charBuf[142];

void setup()
{
  Serial.begin(115200);

  lights = new WalgreenLights(5, 15);
  lights2 = new WalgreenLights(4, 15);
  lightArray[0] = lights;
  lightArray[1] = lights2;

  setupWifi("Your Wireless SSID", "Your Password");
}

void loop()
{
  String next = nextString();

  if (next == "")
  {
    for (int i = 0 ; i < 2; i++)
    {
      lightArray[i]->AllOn();
    }
    delay(1000);
  }
  else
  {
    next.toCharArray(charBuf, 141);
    //Blink to indicate new message
    scaryBlink();
    doString(&charBuf[0]);
    lastString = next;
  }
}

String nextString()
{
  return getWebPage("www.strangerthingshouse.com", "/popQueue.php"); //Formerly currentMessage.txt
}

void doString(char *s)
{
  for (int i = 0 ; s[i] != 0; i++)
  {
    doCharacter(s[i]);
  }
  doCharacter(' ');
  for (int i = 0 ; i < 2; i++)
  {
    lightArray[i]->AllOn();
  }
  delay(3000);
}

void doCharacter(char l)
{
  int i;
  if ( l == ' ')  //Turn off for a second if there is a space
  {
    for (int s = 0 ; s < 2; s++)
    {
      lightArray[s]->AllOff();
    }
    delay(1000);
  }

  for (int s = 0 ; s < 2; s++)
  {
    i = indexOf2(strands[s], l);
    if (i != -1)
    {
      if (i == 0)
      {
        lightSingleBulb(s, i, 15);
        delay(600);
        for (int s = 0 ; s < 2; s++)
        {
          lightArray[s]->AllOff();
        }
        delay(300);
      }
      else
      {
        for (int level = 15; level >= 0; level--)
        {
          lightSingleBulb(s, i , (char)level);
          delay(60);
        }
      }
    }
  }


}

void scaryBlink()
{
  double delayTime = 500;
   
  for (int i = 0 ; i < 25; i++)
  {
    blinkOnce(delayTime);
    delayTime *= .75;
  }
  
  allOff();
  delay(1000);
}

void allOff()
{
  for (int i = 0 ; i < 2; i++)
  {
    lightArray[i]->AllOff();
  }
}

void allOn()
{
  for (int i = 0 ; i < 2; i++)
  {
    lightArray[i]->AllOn();
  }
}

void blinkOnce(double delayTime)
{
  allOff();
  delay(delayTime);
  allOn();
  delay(delayTime);
}

void lightSingleBulb(int s, int i, char level)
{
  unsigned char localState[15];
  for (int j = 0 ; j < 15; j++)
    localState[j] = 0;
  localState[i] = level;

  lightArray[s]->SendValue(localState);
}

int indexOf2(char *s, char l)
{
  for (int i = 0 ; s[i] != 0; i++)
  {
    if (s[i] == l)
      return i;
  }

  return -1;
}




