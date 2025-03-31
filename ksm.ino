#include "EmonLib.h"
#include <WiFi.h>
#include <HTTPClient.h>

//connection
const char* ssid = "Eng Alex";
const char* password = "Eng@@Alx";
const char* serverURL = "http://192.168.43.159/hackathon/overview3.php?action=get_power_status&room_id=";

//room array
const String roomIds[] = {
  "A001-R001", "A001-R002", "A001-R003", "A001-R004", "A002-R001",
  "A002-R002", "A002-R003", "A003-R001", "A003-R002", "A003-R003"
};

const int relayPins[] = {
  27, 26, 25, 33, 32, 12, 13, 14, 15, 16
};

#define SAMPLE_COUNT 1500 
const double Vrms = 230.0;

EnergyMonitor emonRoom1, emonRoom2; //instances for both rooms

void setup() {
  Serial.begin(115200);

  for (int i = 0; i < 10; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], LOW);//initially
  }

  emonRoom1.current(32, 80.5); // Sensor 1 
  emonRoom2.current(33, 35.5); // Sensor 2 

  Serial.println("Energy monitoring and power control for rooms started...");

  // Connect to WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
  Serial.print("ESP32 IP: ");
  Serial.println(WiFi.localIP());

  delay(2000);
}

void loop() {
  // Fetch power status
  for (int i = 0; i < 10; i++) {
    getPowerStatus(roomIds[i], relayPins[i]);
  }

  // Monitor and display energy data
  monitorEnergy();

  delay(1000); // Delay for stability
}

// Function to fetch power status from the server for a specific room
void getPowerStatus(String roomId, int Relay) {
  String url = String(serverURL) + roomId; // Append the room ID to the URL
  Serial.println("Requesting URL: " + url);

  HTTPClient http;
  http.begin(url);
  http.addHeader("User-Agent", "ESP32");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.setTimeout(10000);

  int httpCode = http.GET();
  Serial.print("HTTP Status Code: ");
  Serial.println(httpCode);

  if (httpCode == 200) {
    String payload = http.getString();
    Serial.println("Response: " + payload);

    // Parse the response
    if (payload.indexOf("power_status\":\"ON") != -1) {
      digitalWrite(Relay, HIGH);
      Serial.println("Relay is ON");
    } else if (payload.indexOf("power_status\":\"OFF") != -1) {
      digitalWrite(Relay, LOW);
      Serial.println("Relay is OFF");
    } else {
      Serial.println("Invalid response or power status not found.");
    }
  } else {
    Serial.println("Error on HTTP request");
  }

  http.end();
}

// Function to monitor and display energy data
void monitorEnergy() {
  // Read current for Room 1
  double Irms1 = emonRoom1.calcIrms(SAMPLE_COUNT);
  double powerR11 = Irms1 * Vrms;

  Serial.print("R11 - Current: ");
  Serial.print(Irms1, 3);
  Serial.println(" A");

  Serial.print("R11 - Power: ");
  Serial.print(powerR11, 2);
  Serial.println(" W");
  Serial.println(" ");

  // Read current for Room 2
  double Irms2 = emonRoom2.calcIrms(SAMPLE_COUNT);
  double powerR12 = Irms2 * Vrms;

  Serial.print("R12 - Current: ");
  Serial.print(Irms2, 3);
  Serial.println(" A");

  Serial.print("R12 - Power: ");
  Serial.print(powerR12, 2);
  Serial.println(" W");
  Serial.println(" ");
}

