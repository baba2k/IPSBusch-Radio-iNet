<?
/********************************************
    Busch-Radio iNet by baba (baba@baba.tk)
    			Aktionsskript
********************************************/

IPS_SetScriptTimer($_IPS['SELF'], 0);

if ($_IPS['SENDER'] == "Execute") {
	setDummyModuleAsParent();
}

$configId = updateConfigScript();
if ($configId !== false) {
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($configId));
} else {
	echo "ERROR: Busch-Radio iNet Konfiguration nicht gefunden.";
	return;
}

$funcId = updateFunctionsScript();
if ($funcId !== false) {
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($funcId));
} else {
	echo "ERROR: Busch-Radio iNet Funktionen nicht gefunden.";
	return;
}

if (($_IPS['SENDER'] == "TimerEvent") or ($_IPS['SENDER'] == "Execute")) {
	onExecute();
	if (radioIsOnline() === false) {
		echo "ERROR: Busch-Radio iNet nicht erreichbar.";
		IPS_SetScriptTimer($_IPS['SELF'], RADIO_REFRESHRATE);
		return;
	}

	$indexData = getIndexData();
	updateStationScripts($indexData);
	updateVolumeVar($indexData["Lautstaerke"]);
	updateNowPlayingVar($indexData);
    
	if ($indexData["ZurZeitlaeuft"]["stationNr"] === 0) {
		resetVolume($indexData["Lautstaerke"]["value"]);
	}
}

if ($_IPS['SENDER'] == "WebFront") {
	$ident = IPS_GetObject($_IPS['VARIABLE'])["ObjectIdent"];

	if (strcmp($ident, "Lautstaerke") === 0) {
		setVolume($_IPS["VALUE"]);
	}
}
IPS_SetScriptTimer($_IPS['SELF'], RADIO_REFRESHRATE);

/*****************
  Funktionen
*****************/

function setDummyModuleAsParent() {
	$parent = IPS_GetParent($_IPS["SELF"]);
	if (IPS_GetObject($parent)["ObjectType"] !== 1) {
		$insID = IPS_CreateInstance("{485D0419-BE97-4548-AA9C-C083EB82E61E}");
		IPS_SetParent($insID, $parent);
		IPS_SetParent($_IPS["SELF"], $insID);
		IPS_SetName($insID, "Busch-Radio iNet");
	}
}

function updateConfigScript() {
	if ($_IPS["SENDER"] === "TimerEvent") {
		$parent = IPS_GetParent(IPS_GetParent($_IPS["EVENT"]));
	} else {
		$parent = IPS_GetParent($_IPS["SELF"]);
	}
   
	$configId = @IPS_GetObjectIDByIdent("config", $parent);

	if ($configId === false) {
		$configId = IPS_CreateScript(0);
		IPS_SetParent($configId, $parent);
		IPS_SetName($configId, "Busch-Radio iNet Konfiguration");
		IPS_SetIdent($configId, "config");
		IPS_SetHidden($configId, true);
		IPS_SetPosition($configId, 98);
		IPS_SetScriptContent($configId, getConfigContent());
	}

    return $configId;
}

function getConfigContent() {
$beginTag = "<?\n";
$endTag = "\n?>";
$title =
'/********************************************
    Busch-Radio iNet by baba (baba@baba.tk)
                 Konfiguration
********************************************/' . "\n\n";
$config =
'/***************************
  Konfiguration Allgemein
***************************/
// IP Adresse vom Busch-Radio iNet
// Hinweis: Bei Aenderung dieser Konsante muss das Aktionsskript einmal manuell ausgefuehrt werden
define("RADIO_IP", "0.0.0.0");

// Aktualisierungsrate der Variablen in Sekunden
// Hinweis: Der Wert sollte zwischen 5 und 60 Sekunden liegen. Empfohlen: 30
define("RADIO_REFRESHRATE", 30);

/*************************************************
  (Optional) Konfiguration Lautstärke-Automatik
*************************************************/
// Lautstärke-Automatik verwenden (true = Ja und false = Nein)
// Hinweis: Bei Aenderung dieser Konsante muss das Aktionsskript einmal manuell ausgefuehrt werden
define("RADIO_VOLUMEAUTO", false);

// Lautstärke für den Tag (von 0 bis 31)
// Hinweis: Bei Aenderung dieser Konsante muss das Aktionsskript einmal manuell ausgefuehrt werden
define("RADIO_VOLUMEAUTO_DAY", 5);

// Lautstärke für die Nacht (von 0 bis 31)
// Hinweis: Bei Aenderung dieser Konsante muss das Aktionsskript einmal manuell ausgefuehrt werden
define("RADIO_VOLUMEAUTO_NIGHT", 2);

// Beim Ausschalten des Radios wird die Lautstärke (falls sie geändert wurde) auf
// die Lautstärke für den Tag/ die Nacht zurückgesetzt
// Hinweis: Diese Option ist nur aktiv, wenn auch die Lautstärke-Automatik angestellt ist
// Hinweis: Bei Aenderung dieser Konsante muss das Aktionsskript einmal manuell ausgefuehrt werden
define("RADIO_VOLUMEAUTO_RESETVOL", false);

/***************************************
  (Optional) Erweiterte Konfiguration
***************************************/
// Download URL vom PHP Simple HTML DOM Parser Skript http://simplehtmldom.sourceforge.net/
define("RADIO_SIMPLEHTMLDOM", "https://raw.githubusercontent.com/baba2k/IPSBusch-Radio-iNet/main/simple_html_dom.php");';
return $beginTag . $title . $config . $endTag;
}

function updateFunctionsScript() {
	$parent = IPS_GetParent($_IPS['SELF']);
	$funcId = @IPS_GetObjectIDByIdent("functions", $parent);

	if ($funcId === false) {
		$funcId = IPS_CreateScript(0);
		IPS_SetParent($funcId, $parent);
		IPS_SetName($funcId, "Busch-Radio iNet Funktionen");
		IPS_SetIdent($funcId, "functions");
		IPS_SetHidden($funcId, true);
		IPS_SetPosition($funcId, 100);
		IPS_SetScriptContent($funcId, getFunctionsContent());
	} else if ($_IPS['SENDER'] == "Execute") {
		IPS_SetScriptContent($funcId, getFunctionsContent());
	}

	return $funcId;
}

function getFunctionsContent() {
$beginTag = "<?\n";
$endTag = "\n?>";
$title =
'/********************************************
    Busch-Radio iNet by baba (baba@baba.tk)
                 Funktionen
********************************************/' . "\n\n";
$functions =
'$parent = getParent();
$configId = @IPS_GetObjectIDByIdent("config", $parent);
if ($configId !== false) {
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($configId));
} else {
	echo "ERROR: Busch-Radio iNet Konfiguration nicht gefunden.";
	return;
}

function getParent() {
	if ($_IPS["SENDER"] === "TimerEvent") {
		if (IPS_GetObject($_IPS["EVENT"])["ObjectIdent"] === "volumeAuto") {
			return IPS_GetParent($_IPS["EVENT"]);
		} else {
			return IPS_GetParent(IPS_GetParent($_IPS["EVENT"]));
		}
	} else {
		return IPS_GetParent($_IPS["SELF"]);
	}
}

function setVolume($volume) {
	if ($volume < 0 || $volume > 31) {
		echo "ERROR: Ungültige Lautstärke (Erlaubt: 0-31).";
		return false;
	}
	if (!radioIsOnline()) {
		echo "ERROR: Busch-Radio iNet nicht erreichbar.";
		return false;
	}
	$simplehtmldomId = getSimplehtmldom();
	if ($simplehtmldomId === false) {
		echo "ERROR: PHP Simple HTML DOM Parser nicht gefunden.";
		return false;
    }
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($simplehtmldomId));
	if (!function_exists("file_get_html")) {
		echo "ERROR: Funktion file_get_html() nicht gefunden.";
		return false;
	}
	$html = file_get_html("http://" . RADIO_IP . "/de/index.cgi?vo=" . $volume);
	if ($html === false) {
		echo "ERROR: Keine HTML Daten erhalten.";
		return false;
	}
	$result = extractIndexValuesFromHtml($html);
	$newVolume = intval($result["Lautstaerke"]["value"]);
	if ($volume !== $newVolume) {
		echo "ERROR: Lautstärke konnte nicht korrekt gesetzt werden.";
		return false;
	}
	if (isset($_IPS["SENDER"]) && $_IPS["SENDER"] == "WebFront") {
		SetValueInteger($_IPS["VARIABLE"], $newVolume);
	} else {
		$parent = getParent();
		$volumeId = @IPS_GetObjectIDByIdent("Lautstaerke", $parent);
		if ($volumeId !== false) {
			SetValueInteger($volumeId, $newVolume);
		}
	}
	return true;
}

function setStation($stationNr) {
	if ($stationNr < 1 || $stationNr > 8) {
		echo "ERROR: Ungültige Station (Erlaubt: 1-8).";
		return false;
	}
	if (!radioIsOnline()) {
		echo "ERROR: Busch-Radio iNet nicht erreichbar.";
		return false;
	}
   $simplehtmldomId = getSimplehtmldom();
    if ($simplehtmldomId === false) {
        echo "ERROR: PHP Simple HTML DOM Parser nicht gefunden.";
        return false;
    }
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($simplehtmldomId));
	if (!function_exists("file_get_html")) {
		echo "ERROR: Funktion file_get_html() nicht gefunden.";
		return false;
	}
	$html = file_get_html("http://" . RADIO_IP . "/de/index.cgi?p" . $stationNr);
	if ($html === false) {
		echo "ERROR: Keine HTML Daten erhalten.";
		return false;
	}
	$indexValues = extractIndexValuesFromHtml($html);
	$newStationNr = $indexValues["ZurZeitlaeuft"]["stationNr"];
	$newStationName = $indexValues["Station" . $newStationNr]["value"];
	if ($stationNr !== $newStationNr) {
		echo "ERROR: Station konnte nicht korrekt gesetzt werden.";
		return false;
	}
	$parent = IPS_GetParent($_IPS["SELF"]);
	$playingStationId = @IPS_GetObjectIDByIdent("ZurZeitlaeuft", $parent);
	if ($playingStationId !== false) {
		SetValueString($playingStationId, $newStationName);
	}
	return true;
}

function updateStationScripts($indexData) {
	$parent = IPS_GetParent($_IPS["SELF"]);
	foreach ($indexData as $key => $value) {
		if (strpos($key, "Station") !== false) {
			$stationNr = intval(substr($value["name"], 8));
			$content = getStationScriptContent($stationNr);
			updateIPSscript($parent, trim($value["name"]) . ": " . trim($value["value"]), trim($value["name"]), $content, $stationNr - 1);
		}
	}
}

function getStationScriptContent($stationNr) {
$beginTag = "<?\n";
$endTag = "\n?>";
$title = \'/********************************************
    Busch-Radio iNet by baba (baba@baba.tk)
                 Station \' . $stationNr . \'
********************************************/\' . "\n\n";
$content = \'$parent = IPS_GetParent($_IPS["SELF"]);
$funcId = @IPS_GetObjectIDByIdent("functions", $parent);
if ($funcId !== false) {
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($funcId));
} else {
	echo "ERROR: Busch-Radio iNet Funktionen nicht gefunden.";
	return;
}
setStation(\' . $stationNr . \');\';
return $beginTag . $title . $content . $endTag;
}

function updateVolumeVar($value) {
	$parent = IPS_GetParent($_IPS["SELF"]);
	$varId = updateIPSvar($parent, trim($value["name"]), intval($value["value"]), 1, 8);
	if ($_IPS["SENDER"] == "Execute") {
		createVolumeVariableProfile();
		IPS_SetVariableCustomProfile($varId, "RadioBusch.Volume");
		IPS_SetVariableCustomAction($varId, $_IPS["SELF"]);
		IPS_SetIcon($varId, "Speaker");
	}
}

function updateNowPlayingVar($indexValues) {
	$parent = IPS_GetParent($_IPS["SELF"]);
	$newStationNr = $indexValues["ZurZeitlaeuft"]["stationNr"];
	if ($newStationNr < 1 || $newStationNr > 8) {
		$newStationName = "";
	} else {
		$newStationName = $indexValues["Station" . $newStationNr]["value"];
	}
	$varId = updateIPSvar($parent, trim($indexValues["ZurZeitlaeuft"]["name"]), trim($newStationName), 3, 9);
	if ($_IPS["SENDER"] == "Execute") {
		IPS_SetIcon($varId, "Melody");
	}
}

function getIndexData() {
	if (!radioIsOnline()) {
		echo "ERROR: Busch-Radio iNet nicht erreichbar.";
		return false;
	}

	$simplehtmldomId = getSimplehtmldom();
	if ($simplehtmldomId === false) {
		echo "ERROR: PHP Simple HTML DOM Parser nicht gefunden.";
		return false;
	}
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($simplehtmldomId));
	if (!function_exists("file_get_html")) {
		echo "ERROR: Funktion file_get_html() nicht gefunden.";
		return false;
	}
	$html = file_get_html("http://" . RADIO_IP . "/de/index.shtml");
	if ($html === false) {
		return false;
	}
	$result = extractIndexValuesFromHtml($html);
	return $result;
}

function extractIndexValuesFromHtml($html) {
	$result = array();
	foreach($html->find("form[action=index.cgi]") as $form) {
		foreach($form->find("legend") as $legend) {
			foreach($form->find("input[type=text]") as $input) {
				$name = utf8_decode(trim(html_entity_decode($legend->plaintext)));
				$key = str_replace(array(".",":","-","_"," ",utf8_decode("ä"),utf8_decode("ö"),utf8_decode("ü"),utf8_decode("ß"),"ä","ö","ü","ß"), array("","","","","","ae","oe","ue","ss","ae","oe","ue","ss"), $name);
				$result[$key] = array("name" => $name, "value" => utf8_decode(html_entity_decode(trim($input->value))));
				if (strcmp($key, "ZurZeitlaeuft") === 0) {
					$result[$key]["stationNr"] = extractPlayingStationNrFromHtml($html);
				}
			}
		}
	}
	return $result;
}

function extractPlayingStationNrFromHtml($html) {
	foreach($html->find("p") as $p) {
		$strpos = strpos($p, ":");
		if ($strpos !== false) {
			$str = trim($p->plaintext);
			return intval(substr($str,  ($strpos - 1) - (strlen($str)), 1));
		}
	}
	return false;
}

function getSimplehtmldom() {
	$parent = getParent();
	$simplehtmldomId = @IPS_GetObjectIDByIdent("simplehtmldom", $parent);
	if ($simplehtmldomId === false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, RADIO_SIMPLEHTMLDOM);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);

		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$content = Sys_GetURLContent($url);
       
		if ($content === false) {
			echo "ERROR: PHP Simple HTML DOM Parser Skript konnte nicht heruntergeladen werden. Bitte Downloadlink anpassen (siehe Erweiterte Konfiguration).";
			return false;
		}
		$simplehtmldomId = IPS_CreateScript(0);
		IPS_SetParent($simplehtmldomId, $parent);
		IPS_SetName($simplehtmldomId, "Busch-Radio iNet PHP Simple HTML DOM Parser Skript");
		IPS_SetScriptContent($simplehtmldomId, $content);
		IPS_SetIdent($simplehtmldomId, "simplehtmldom");
		IPS_SetHidden($simplehtmldomId, true);
		IPS_SetPosition($simplehtmldomId, 200);
	}
	return $simplehtmldomId;
}

function onExecute() {
    if ($_IPS["SENDER"] == "Execute") {
		IPS_SetName($_IPS["SELF"], "Busch-Radio iNet Aktionsskript");
		IPS_SetPosition($_IPS["SELF"], 99);
		IPS_SetHidden($_IPS["SELF"], true);
		setVolumeAuto();
	}
}

function setVolumeAuto() {
	$parent = IPS_GetParent($_IPS["SELF"]);
	$wpId = @IPS_GetObjectIDByIdent("volumeAuto", $parent);
	if (RADIO_VOLUMEAUTO === true) {
		if ($wpId === false) {
			$wpId = IPS_CreateEvent(2);
			IPS_SetEventScheduleGroup($wpId, 0, 127);
			IPS_SetParent($wpId, $parent);
			IPS_SetName($wpId, "Lautstärke-Automatik");
			IPS_SetIdent($wpId, "volumeAuto");
			IPS_SetPosition($wpId, 10);
			IPS_SetIcon($wpId, "Clock");
		}
		IPS_SetEventScheduleAction($wpId, 0, "Tag", 0xFFFFFF, getWeeklyScheduleContent(RADIO_VOLUMEAUTO_DAY));
		IPS_SetEventScheduleAction($wpId, 1, "Nacht", 0x585858, getWeeklyScheduleContent(RADIO_VOLUMEAUTO_NIGHT));
	} else {
		if ($wpId !== false) {
			IPS_DeleteEvent($wpId);
		}
	}
}

function resetVolume($volume) {
	if (RADIO_VOLUMEAUTO === false || RADIO_VOLUMEAUTO_RESETVOL === false) {
		return false;
	}
	$time = time();
	$parent = IPS_GetParent($_IPS["SELF"]);
	$wpId = @IPS_GetObjectIDByIdent("volumeAuto", $parent);
	$e = IPS_GetEvent($wpId);
	$actionID = false;
	foreach($e["ScheduleGroups"] as $g) {
		if ($g["Days"] & date("N") > 0) {
			foreach($g["Points"] as $p) {
				if (date("H") * 3600 + date("i") * 60 + date("s") >= $p["Start"]["Hour"] * 3600 + $p["Start"]["Minute"] * 60 + $p["Start"]["Second"]) {
					$actionID = intval($p["ActionID"]);
				} else {
					break;
				}
			}
			break;
		}
	}
	if ($actionID === 0 && $volume != RADIO_VOLUMEAUTO_DAY) {
		setVolume(RADIO_VOLUMEAUTO_DAY);
		return true;
	} else if ($actionID === 1 && $volume != RADIO_VOLUMEAUTO_NIGHT) {
		setVolume(RADIO_VOLUMEAUTO_NIGHT);
		return true;
	} else if ($actionID === false) {
		echo "ERROR: Busch-Radio iNet konnte Lautstärke nicht zurücksetzen.";
	}
	return false;
}

function getWeeklyScheduleContent($volume) {
$content = \'$parent = IPS_GetParent($_IPS["EVENT"]);
$funcId = @IPS_GetObjectIDByIdent("functions", $parent);
if ($funcId !== false) {
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($funcId));
} else {
	echo "ERROR: Busch-Radio iNet Funktionen nicht gefunden.";
	return;
}
setVolume(\' . $volume . \');\';
return $content;
}

function createVolumeVariableProfile() {
	$profileName = "RadioBusch.Volume";
	if (IPS_VariableProfileExists($profileName)) {
		return;
	}
	IPS_CreateVariableProfile($profileName, 1);
	IPS_SetVariableProfileValues($profileName, 0, 31, 1);
	IPS_SetVariableProfileText($profileName, "", "%");
	IPS_SetVariableProfileIcon($profileName, "Intensity");
}

function deleteVolumeVariableProfile() {
	$profileName = "RadioBusch.Volume";
	if (!IPS_VariableProfileExists($profileName)) {
		return;
	}
	IPS_DeleteVariableProfile($profileName);
}

function updateIPSscript($parent, $name, $ident, $content, $pos=0) {
	$ident = str_replace(array(".",":","-","_"," ",utf8_decode("ä"),utf8_decode("ö"),utf8_decode("ü"),utf8_decode("ß"),"ä","ö","ü","ß"), array("","","","","","ae","oe","ue","ss","ae","oe","ue","ss"), $ident);
	if (!ctype_alnum($ident)) {
		echo "ERROR: Konnte Variable nicht hinzufügen. Name kann nicht als Ident verwendet werden: " . $ident . PHP_EOL;
		return;
	}
	$varId = @IPS_GetObjectIDByIdent($ident, $parent);
	if ($varId === false) {
		$varId = IPS_CreateScript(0);
		IPS_SetName($varId, $name);
		IPS_SetIdent($varId, $ident);
		IPS_SetParent($varId, $parent);
		IPS_SetPosition($varId, $pos);
		IPS_SetScriptContent($varId, $content);
	} else {
		if (strpos($ident, "Station") !== false) {
			if (strcmp(IPS_GetName($varId), $name) != 0) {
				IPS_SetName($varId, $name);
			}
		}
		if ($_IPS["SENDER"] == "Execute") {
			IPS_SetScriptContent($varId, $content);
		}
	}
	return $varId;
}

function updateIPSvar($parent, $name, $value, $type, $pos=0) {
	$ident = str_replace(array(".",":","-","_"," ",utf8_decode("ä"),utf8_decode("ö"),utf8_decode("ü"),utf8_decode("ß"),"ä","ö","ü","ß"), array("","","","","","ae","oe","ue","ss","ae","oe","ue","ss"), $name);
	if (!ctype_alnum($ident)) {
		echo "ERROR: Konnte Variable nicht hinzufügen. Name kann nicht als Ident verwendet werden: " . $ident . PHP_EOL;
		return;
	}
	if (is_int($type)) {
		$ipsType = $type;
	} else {
		switch ($type) {
			case "int":
				$ipsType = 1;
				break;
			case "boolean":
				$ipsType = 0;
				break;
			case "string":
				$ipsType = 3;
				break;
			default:
				echo "ERROR: Unbekannter Datentyp:" . $type . PHP_EOL;
				return;
		}
	}
	$varId = @IPS_GetObjectIDByIdent($ident, $parent);
	if ($varId === false) {
		$varId = IPS_CreateVariable($ipsType);
		IPS_SetName($varId, $name);
		IPS_SetIdent($varId, $ident);
		IPS_SetParent($varId, $parent);
		IPS_SetPosition($varId, $pos);
	}
	switch ($ipsType) {
		case 0:
			if (GetValueBoolean($varId) <> (bool)$value) {
				SetValueBoolean($varId, (bool)$value);
			}
			break;
		case 1:
			if (GetValueInteger($varId) <> (int)$value) {
				SetValueInteger($varId, (int)$value);
			}
			break;
		case 2:
			if (GetValueFloat($varId) <> round((float)$value, 2)) {
				SetValueFloat($varId, round((float)$value, 2));
			}
			break;
		case 3:
			if (GetValueString($varId) <> $value) {
				SetValueString($varId, $value);
			}
			break;
		}
	return $varId;
}

function radioIsOnline() {
	$simplehtmldomId = getSimplehtmldom();
	if ($simplehtmldomId === false) {
		echo "ERROR: PHP Simple HTML DOM Parser nicht gefunden.";
		return false;
	}
	require_once(IPS_GetKernelDir() . "/scripts/" . IPS_GetScriptFile($simplehtmldomId));
	if (!function_exists("file_get_html")) {
		echo "ERROR: Funktion file_get_html() nicht gefunden.";
		return false;
	}
	$html = @file_get_html("http://" . RADIO_IP . "/de/index.shtml");
	if ($html === false) {
		return false;
	} else {
		return true;
	}
}';
return $beginTag . $title . $functions . $endTag;
}
?>
