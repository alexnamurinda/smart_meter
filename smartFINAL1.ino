#include <Arduino.h>
#include "EmonLib.h"
#include <WiFi.h>
#include <HTTPClient.h>
#include <EEPROM.h>
#include <time.h>

#define EEPROM_SIZE 512
#define ENERGY_DATA_ADDR 0
#define LAST_SAVE_TIME_ADDR 128
#define SAVE_INTERVAL 300000

const char* ssid = "ICT HUB";
const char* password = "1ctn4k4w4";

const char* serverURL = "http://10.180.200.89/smart_METER_project/overview3.php?action=get_power_status&room_id=";
const char* energyDataURL = "http://10.180.200.89/smart_METER_project/pay4.php?room_id=";

const String roomIds[] = {
  "A001-R001", "A001-R002", "A001-R003", "A001-R004"
};

const int relayPins[] = { 27, 26, 25, 33 };
const int NUM_ROOMS = 4;

const int MUX_S0 = 2;
const int MUX_S1 = 4;
const int MUX_S2 = 5;
const int MUX_S3 = 18;
const int MUX_SIG = 34;
const int MUX_E = 15;

#define SENSOR_PIN 32
#define SAMPLES 100
#define SENSITIVITY 0.0270

#define SAMPLE_COUNT 1500
#define MIN_ENERGY_THRESHOLD 0.01

#define WEB_UPDATE_CHECK_INTERVAL 30000
unsigned long lastWebUpdateCheck = 0;

struct RoomEnergyData {
  float energyKWh;
  float lastPower;
  float lastVoltage;
  float lastCurrent;
  bool outOfEnergy;
  float previousEnergy;
  bool energyUpdated;
};

RoomEnergyData roomData[NUM_ROOMS];
unsigned long lastEnergyCalcTime = 0;
unsigned long lastSaveTime = 0;

EnergyMonitor emonRooms[NUM_ROOMS];

void selectMUXChannel(int channel);
float readACVoltage();
void getPowerStatus(String roomId, int relay);
void monitorEnergy(float Vrms);
void saveEnergyData();
void loadEnergyData();
void calculateEnergy();
void updateEnergyData(String roomId, int roomIndex);
void checkRoomEnergy();
void displayAllRoomData();
void checkForWebUpdates();

void setup() {
  Serial.begin(115200);
 
  if (!EEPROM.begin(EEPROM_SIZE)) {
    Serial.println("Failed to initialize EEPROM");
  }
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], LOW);
    roomData[i].outOfEnergy = false;
    roomData[i].energyUpdated = false;
    roomData[i].previousEnergy = 0;
  }

  pinMode(MUX_S0, OUTPUT);
  pinMode(MUX_S1, OUTPUT);
  pinMode(MUX_S2, OUTPUT);
  pinMode(MUX_S3, OUTPUT);
  pinMode(MUX_SIG, INPUT);
  pinMode(MUX_E, OUTPUT);
  digitalWrite(MUX_E, LOW);

  float calibration[] = {15.5, 11.0, 15.5, 10.5};
  for (int i = 0; i < NUM_ROOMS; i++) {
    emonRooms[i].current(MUX_SIG, calibration[i]);
  }

  pinMode(SENSOR_PIN, INPUT);

  loadEnergyData();
 
  lastEnergyCalcTime = millis();
  lastSaveTime = millis();
  lastWebUpdateCheck = millis();

  delay(1000);

  Serial.println("Energy monitoring and power control for rooms started...");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
  Serial.print("ESP32 IP: ");
  Serial.println(WiFi.localIP());

  Serial.println("Loaded energy data from EEPROM:");
  for (int i = 0; i < NUM_ROOMS; i++) {
    roomData[i].previousEnergy = roomData[i].energyKWh;
    Serial.print("Room ");
    Serial.print(roomIds[i]);
    Serial.print(" energy: ");
    Serial.print(roomData[i].energyKWh, 3);
    Serial.println(" kWh");
  }

  checkRoomEnergy();
 
  Serial.println("=== TESTING MODE: Checking for web updates every 30 seconds ===");
  Serial.println("=== Regular EEPROM saves still occur every 5 minutes ===");

  delay(2000);
}

void loop() {
  float Vrms = readACVoltage();

  Serial.print("Measured AC Voltage: ");
  Serial.print(Vrms);
  Serial.println(" V");

  for (int i = 0; i < NUM_ROOMS; i++) {
    if (!roomData[i].outOfEnergy) {
      getPowerStatus(roomIds[i], relayPins[i]);
    }
  }

  monitorEnergy(Vrms);
 
  calculateEnergy();
 
  checkRoomEnergy();
 
  if (millis() - lastWebUpdateCheck >= WEB_UPDATE_CHECK_INTERVAL) {
    checkForWebUpdates();
    lastWebUpdateCheck = millis();
  }
 
  if (millis() - lastSaveTime >= SAVE_INTERVAL) {
    saveEnergyData();
    lastSaveTime = millis();
  }

  delay(3000);
}

void checkForWebUpdates() {
  Serial.println("\n===== CHECKING FOR WEB UPDATES =====");
  Serial.println("Room ID\t\tPrevious\tNew\t\tChange\tStatus");
  Serial.println("------------------------------------------------------------------");
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    roomData[i].energyUpdated = false;
  }
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    roomData[i].previousEnergy = roomData[i].energyKWh;
    updateEnergyData(roomIds[i], i);
   
    float change = roomData[i].energyKWh - roomData[i].previousEnergy;
   
    Serial.print(roomIds[i]);
    Serial.print("\t");
    Serial.print(roomData[i].previousEnergy, 2);
    Serial.print(" kWh\t");
    Serial.print(roomData[i].energyKWh, 2);
    Serial.print(" kWh\t");
   
    if (change != 0) {
      if (change > 0) {
        Serial.print("+");
      }
      Serial.print(change, 2);
      Serial.print("\t");
      Serial.println("UPDATED!");
      roomData[i].energyUpdated = true;
    } else {
      Serial.print("0.00\t");
      Serial.println("No change");
    }
  }
 
  Serial.println("------------------------------------------------------------------");
 
  checkRoomEnergy();
 
  displayAllRoomData();
}

void displayAllRoomData() {
  Serial.println("\n===== CURRENT ROOM STATUS =====");
  Serial.println("Room ID\t\tEnergy\t\tPower\tVoltage\tCurrent\tStatus");
  Serial.println("------------------------------------------------------------------");
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    Serial.print(roomIds[i]);
    Serial.print("\t");
    Serial.print(roomData[i].energyKWh, 2);
    Serial.print(" kWh\t");
    Serial.print(roomData[i].lastPower, 1);
    Serial.print(" W\t");
    Serial.print(roomData[i].lastVoltage, 1);
    Serial.print(" V\t");
    Serial.print(roomData[i].lastCurrent, 3);
    Serial.print(" A\t");
   
    if (roomData[i].outOfEnergy) {
      Serial.print("NO ENERGY");
    } else if (digitalRead(relayPins[i]) == HIGH) {
      Serial.print("ON");
    } else {
      Serial.print("OFF");
    }
   
    if (roomData[i].energyUpdated) {
      Serial.print(" (UPDATED)");
    }
   
    Serial.println();
  }
  Serial.println("------------------------------------------------------------------\n");
}

void checkRoomEnergy() {
  for (int i = 0; i < NUM_ROOMS; i++) {
    if (roomData[i].energyKWh <= MIN_ENERGY_THRESHOLD) {
      if (!roomData[i].outOfEnergy) {
        digitalWrite(relayPins[i], LOW);
        roomData[i].outOfEnergy = true;
        Serial.print("Room ");
        Serial.print(roomIds[i]);
        Serial.println(" has run out of energy. Power turned OFF.");
      }
    } else {
      if (roomData[i].outOfEnergy) {
        roomData[i].outOfEnergy = false;
        Serial.print("Room ");
        Serial.print(roomIds[i]);
        Serial.println(" energy restored. Power control resumed.");
      }
    }
  }
}

void selectMUXChannel(int channel) {
  digitalWrite(MUX_S0, channel & 0x01);
  digitalWrite(MUX_S1, (channel >> 1) & 0x01);
  digitalWrite(MUX_S2, (channel >> 2) & 0x01);
  digitalWrite(MUX_S3, (channel >> 3) & 0x01);
}

float readACVoltage() {
  float sum = 0;
  for (int i = 0; i < SAMPLES; i++) {
    int sensorValue = analogRead(SENSOR_PIN);
    float voltage = sensorValue * (3.3 / 4095.0);
    sum += voltage * voltage;
    delay(1);
  }
  float rmsVoltage = sqrt(sum / SAMPLES);
  float acVoltage = rmsVoltage / SENSITIVITY;
  return acVoltage;
}

void getPowerStatus(String roomId, int relay) {
  String url = String(serverURL) + roomId;
 
  HTTPClient http;
  http.begin(url);
  http.addHeader("User-Agent", "ESP32");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.setTimeout(10000);

  int httpCode = http.GET();
 
  if (httpCode == 200) {
    String payload = http.getString();
   
    if (payload.indexOf("power_status\":\"ON") != -1) {
      digitalWrite(relay, HIGH);
      Serial.println(roomId + " Relay: ON");
    } else if (payload.indexOf("power_status\":\"OFF") != -1) {
      digitalWrite(relay, LOW);
      Serial.println(roomId + " Relay: OFF");
    }
  } else {
    Serial.println("Error on HTTP request for " + roomId);
  }

  http.end();
}

void monitorEnergy(float Vrms) {
  for (int i = 0; i < NUM_ROOMS; i++) {
    selectMUXChannel(i);
    delay(200);
   
    double Irms = 0;
    if (digitalRead(relayPins[i]) == HIGH) {
      Irms = emonRooms[i].calcIrms(SAMPLE_COUNT);
    }
   
    double power = Irms * Vrms;
   
    roomData[i].lastVoltage = Vrms;
    roomData[i].lastCurrent = Irms;
    roomData[i].lastPower = power;
   
    Serial.print("Room ");
    Serial.print(roomIds[i]);
    Serial.print(" - Current: ");
    Serial.print(Irms, 3);
    Serial.print(" A, Power: ");
    Serial.print(power, 2);
    Serial.print(" W, Energy: ");
    Serial.print(roomData[i].energyKWh, 3);
    Serial.print(" kWh, Status: ");
    Serial.println(roomData[i].outOfEnergy ? "OUT OF ENERGY" : "OK");
  }
}

void calculateEnergy() {
  unsigned long currentTime = millis();
  float elapsedHours = (currentTime - lastEnergyCalcTime) / 3600000.0;
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    if (digitalRead(relayPins[i]) == HIGH && !roomData[i].outOfEnergy) {
      float energyUsed = (roomData[i].lastPower * elapsedHours) / 1000.0;
     
      if (roomData[i].energyKWh > energyUsed) {
        roomData[i].energyKWh -= energyUsed;
      } else {
        roomData[i].energyKWh = 0;
      }
    }
  }
 
  lastEnergyCalcTime = currentTime;
}

void saveEnergyData() {
  Serial.println("Saving energy data to EEPROM...");
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    EEPROM.writeFloat(ENERGY_DATA_ADDR + (i * sizeof(float)), roomData[i].energyKWh);
  }
 
  EEPROM.writeULong(LAST_SAVE_TIME_ADDR, millis());
 
  if (EEPROM.commit()) {
    Serial.println("EEPROM data saved successfully");
  } else {
    Serial.println("EEPROM data save failed");
  }
}

void loadEnergyData() {
  Serial.println("Loading energy data from EEPROM...");
 
  for (int i = 0; i < NUM_ROOMS; i++) {
    roomData[i].energyKWh = EEPROM.readFloat(ENERGY_DATA_ADDR + (i * sizeof(float)));
   
    roomData[i].lastPower = 0;
    roomData[i].lastVoltage = 0;
    roomData[i].lastCurrent = 0;
    roomData[i].outOfEnergy = false;
    roomData[i].previousEnergy = 0;
    roomData[i].energyUpdated = false;
  }
 
  unsigned long savedTime = EEPROM.readULong(LAST_SAVE_TIME_ADDR);
  Serial.print("Last save timestamp: ");
  Serial.println(savedTime);
}

void updateEnergyData(String roomId, int roomIndex) {
    String url = String(energyDataURL) + roomId;
   
    HTTPClient http;
    http.begin(url);
    http.addHeader("User-Agent", "ESP32");
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    http.setTimeout(10000);
    int httpCode = http.GET();
    if (httpCode == 200) {
        String payload = http.getString();
        float newEnergyUnits = payload.toFloat();
       
        if (newEnergyUnits >= 0) {
            roomData[roomIndex].previousEnergy = roomData[roomIndex].energyKWh;
           
            roomData[roomIndex].energyKWh = newEnergyUnits;
           
            if (roomData[roomIndex].energyKWh != roomData[roomIndex].previousEnergy) {
                roomData[roomIndex].energyUpdated = true;
            }
        } else {
            Serial.println("Invalid energy value received for " + roomId);
        }
    } else {
        Serial.println("Failed to fetch updated energy data for " + roomId + " (HTTP Code: " + String(httpCode) + ")");
    }
    http.end();
}