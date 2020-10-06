# v1.5 - 24.04.2016
* Lautstaerke-Automatik Reset: Beim Ausschalten des Radios wird die Lautstärke (falls sie geändert wurde) auf
  die Lautstärke für den Tag/ die Nacht zurückgesetzt
  Hinweis: Diese Option ist nur aktiv, wenn auch die Lautstärke-Automatik aktiv ist
  Hinweis: Diese Automatik-Funktion ist für Radios mit Verbindung zum Lichtschalter gedacht. So kann die Lautstaerke
  nach dem Ausschalten des Radios automatisch auf die eingestellte Laustaerke fuer den Tag oder die Nacht zurueckgesetzt werden.

# v1.4 - 28.02.2016
* Kompatibel mit IP-Symcon v4.0 (Windows und Linux)

# v1.3 - 26.01.2016
* Fix: Im Webinterface vom Radio wird bei der Variable ZurZeitlaueft nur die Stationsnummer richtig mitgeführt, der Stationsname 
  wird erst beim nächsten aktualisieren nachgeführt. Nun wird nurnoch die Stationsnummer ausgelesen und anhand der 
  Stationsliste der Stationsname ermittelt
* Fix: Bei der Lautstaerke-Automatik wird nun auf Antwort gewartet und ggf. ein Fehler auf der Konsole ausgegeben

# v1.2 - 25.01.2016
* Lautstaerke-Automatik: Die Lautstaerke des Radios wird zu festen Uhrzeiten, welche in einem Wochenkalender
  eingetragen werden, auf eine bestimmte Lautsaerke gestellt (0 bis 31). Beispiel: 10 Uhr -> 4 und 22 Uhr -> 2
  Hinweis: Diese Automatik-Funktion ist für Radios mit Verbindung zum Lichtschalter gedacht. So kann nachts die Lautstaerke
  gesenkt und morgens wieder angehoben werden.
* Fix: Lautstaerkeumrechnung von 0-31 auf 0-100% uebernimmt jetzt IPS über ein Variablenprofil "RadioBusch.Volume"
* Fix: Beim Lautstärke einstellen und Station auswählen, wird nun auf Rückmeldung gewartet und ggf. ein Fehler ausgegeben
* Fix: Eine index.cgi Abfrage führt zum aufleuchten des Displays, index.shtml nicht. Jetzt wird beim Nachführen der Variable 
  index.shtml abgefragt

# v1.1 - 23.01.2016
* Fix: Variable "Zur Zeit läuft" wird nun korrekt mitgefuehrt

# v1.0 - 23.01.2016
* Kompatibel mit IP Symcon v3.4 (andere Versionen nicht getestet)
* Kompatibel mit Firmware-Version 01.19 (andere Versionen nicht getestet)
* Stationen abspielen und Stationsnamen anzeigen
* Lautstärke anzeigen und regeln
* Momentan abgespielte Station anzeigen
