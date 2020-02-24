<?php
require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen

class HikvisionCam extends IPSModule 
{
	use ProfileHelper, DebugHelper;

	public function Create() 
	{
		//Never delete this line!
		parent::Create();
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.  
		
		// Camera Parameter
		$this->RegisterPropertyString('IPAdress', "");
		$this->RegisterPropertyString('UserName', "admin");
		$this->RegisterPropertyString('UserPassword', "");
		
		// Snapshot Parameter
		$this->RegisterPropertyInteger('No_Picture', 10);
		$this->RegisterPropertyInteger('Break', 50);
		$this->RegisterPropertyString('Picture_Path', "");
		$this->RegisterPropertyInteger('TriggerID', null);
		
		// Message Telegram    
        	$this->RegisterPropertyBoolean('CheckTelegram', false);
		$this->RegisterPropertyInteger('TelegramID', null);
		
		// Logging
		$this->RegisterPropertyBoolean('Logging', false);
		$this->RegisterPropertyString('Title', "");
		
		// Media
		$this->RegisterPropertyBoolean('Media', false);
		
		// Trigger
		//$this->RegisterTimer('UpdateTrigger', 0, "HKVC_Update(\$_IPS['TARGET']);");
		
		//Startposition Cam
		$this->RegisterPropertyInteger('StartPos', 0);
		
		//Zielposition Cam
		$this->RegisterPropertyInteger('ZielPos', 0);		
	}
	
	public function Media()
	{
		//Media Datei anlegen
            	$this->RegisterMedia("Media", "Mediadatei", 0, '', 0,"HKVC_Media(\$_IPS['TARGET']);");
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();  
		
		// ID Instanz
		$Instance = $this->InstanceID;

		// Trigger Auslöser
		If ($this->ReadPropertyInteger('TriggerID') > NULL)
		{
			$this->RegisterTrigger("Auslöser", "TriggerAusloeser", 0, $Instance, 0,"HKVC_Update(\$_IPS['TARGET']);");
		};
		
		// Variable Startposition erstellen
		$this->MaintainVariable('StartPos', 'Ausgangsposition', vtInteger, '', 1, true);
		
		// Variable Zielposition erstellen
		$this->MaintainVariable('ZielPos', 'Zielposition', vtInteger, '', 2, true);
		$this->RegisterTriggerZiel("Position", "TriggerZielposition", 0, $Instance, 0,"HKVC_Position(\$_IPS['TARGET']);");
	}
	
	public function Update()
    	{
		//IP-Adress
		$IP = $this->ReadPropertyString('IPAdress');
		
		//User
		$user = $this->ReadPropertyString('UserName');

		//Password
		$pass = $this->ReadPropertyString('UserPassword');

		//URL Snapshot
		$url = "http://$IP/Streaming/channels/1/picture";

		//ISAPI Target
		$ZielPos = GetValue($this->GetIDForIdent('ZielPos'));
		$ISAPI_Target = "/ISAPI/PTZCtrl/channels/1/presets/$ZielPos/goto";
		
		//ISAPI Start
		$StartPos = GetValue($this->GetIDForIdent('StartPos'));
		$ISAPI_Start = "/ISAPI/PTZCtrl/channels/1/presets/$StartPos/goto";
			
		//Anzahl Bilder
		$anz_bilder = $this->ReadPropertyInteger('No_Picture');
		
		//Bildverzeichnis
		$name_cam = $this->ReadPropertyString('Name');
		
		//Bildpfad
		$bildpfad = $this->ReadPropertyString('Picture_Path');
		
		//Break
		$pause = $this->ReadPropertyInteger('Break');
		
		//Logging
		$log = $this->ReadPropertyBoolean('Logging'); 
		
		//Messagetexte und Titel
		$text 	= $this->ReadPropertyString('Name')." - ".date("d.m.y - H:i:s");
		$titel	= $this->ReadPropertyString('Title');
		
		//Go to preset
		$xml_data = '<PTZPreset version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">              
		</PTZPreset>'."\r\n";

		//Socket öffnen
		$fp = @fsockopen("tcp://".$IP, 80, $errno, $errstr, 10);
		if (!$fp)
		{
		    die($errstr.':'.$errno);
		}
		else
		{
		    $header  = "PUT $ISAPI_Target HTTP/1.1\r\n";
		    $header .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
		    $header .= "User-Agent: php-script\r\n";
		    $header .= "Host: $IP\r\n";
		    $header .= "Accept: */*\r\n";
		    $header .= "Content-Length: ".strlen($xml_data)."\r\n\r\n";

		    //senden von Daten
		    fwrite($fp, $header.$xml_data);

		    $headers='';

		    //Header lesen
		    while ($str = trim(fgets($fp, 4096)))
		    $headers .= "$str\n";

		    $body='';

		    //Antwort lesen
		    while (!feof($fp))
		    $body.= fgets($fp, 4096);

		    //Soket schliessen
		    fclose($fp);
		}
		
		$filecams = ARRAY();
		//********** Eine Reihe von Bildern machen im Abstand von $pause Msec  *********
		for ( $i=0;$i<$anz_bilder;$i++)
		{
			//Datum und Uhrzeit festlegen	
			$time = date("d.").date("m.").date("Y")."_".date("H-i-s");
			$datum = date("Y.").date("m.").date("d")."\\";

			//Bildpfad erstellen
			$directoryPath = $bildpfad.$datum; 
			if (!file_exists($directoryPath)) 
			{
				mkdir($directoryPath);
			}

			$file = $directoryPath.$name_cam."_".$time.".jpg"; 
					
			//Bilder machen und im Bildverzeichnis ablegen
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
			curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; da; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11');
			
			$fp = fopen($file, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$filecams[$i]=$file;
			IPS_SLEEP($pause);

			//Logging Switch Off / On
			If ($log == true)
			{
				//Meldung im IPS Logger
				IPSUtils_Include ("IPSLogger.inc.php", "IPSLibrary::app::core::IPSLogger");
				IPSLogger_Not($titel, $text); 
			}
		}
		
		//Go to preset
		$xml_data = '<PTZPreset version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">              
		</PTZPreset>'."\r\n";

		//Socket öffnen
		$fp = @fsockopen("tcp://".$IP, 80, $errno, $errstr, 10);
		if (!$fp)
		{
		    die($errstr.':'.$errno);
		}
		else
		{
		    $header  = "PUT $ISAPI_Start HTTP/1.1\r\n";
		    $header .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
		    $header .= "User-Agent: php-script\r\n";
		    $header .= "Host: $IP\r\n";
		    $header .= "Accept: */*\r\n";
		    $header .= "Content-Length: ".strlen($xml_data)."\r\n\r\n";

		    //senden von Daten
		    fwrite($fp, $header.$xml_data);

		    $headers='';

		    //Header lesen
		    while ($str = trim(fgets($fp, 4096)))
		    $headers .= "$str\n";

		    $body='';

		    //Antwort lesen
		    while (!feof($fp))
		    $body.= fgets($fp, 4096);

		    //Soket schliessen
		    fclose($fp);
		}

	}
	
	public function Position()
    	{
		//IP-Adress
		$IP = $this->ReadPropertyString('IPAdress');
		
		//User
		$user = $this->ReadPropertyString('UserName');

		//Password
		$pass = $this->ReadPropertyString('UserPassword');

		//URL Snapshot
		$url = "http://$IP/Streaming/channels/1/picture";

		//ISAPI Target
		$ZielPos = GetValue($this->GetIDForIdent('ZielPos'));
		$ISAPI_Target = "/ISAPI/PTZCtrl/channels/1/presets/$ZielPos/goto";
				
		//Go to preset
		$xml_data = '<PTZPreset version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">              
		</PTZPreset>'."\r\n";

		//Socket öffnen
		$fp = @fsockopen("tcp://".$IP, 80, $errno, $errstr, 10);
		if (!$fp)
		{
		    die($errstr.':'.$errno);
		}
		else
		{
		    $header  = "PUT $ISAPI_Target HTTP/1.1\r\n";
		    $header .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
		    $header .= "User-Agent: php-script\r\n";
		    $header .= "Host: $IP\r\n";
		    $header .= "Accept: */*\r\n";
		    $header .= "Content-Length: ".strlen($xml_data)."\r\n\r\n";

		    //senden von Daten
		    fwrite($fp, $header.$xml_data);

		    $headers='';

		    //Header lesen
		    while ($str = trim(fgets($fp, 4096)))
		    $headers .= "$str\n";

		    $body='';

		    //Antwort lesen
		    while (!feof($fp))
		    $body.= fgets($fp, 4096);

		    //Soket schliessen
		    fclose($fp);
		}
	}
	
	public function Action()
    	{
		$this->RegisterAction("Aktion", "EigeneAktion", 0, '', 0,"HKVC_Aktion(\$_IPS['TARGET']);");
	}
	
	private function RegisterAction($Name, $Ident, $Typ, $Parent)
	{
		IPS_SetVariableCustomAction($this->GetIDForIdent('ZielPos'), 0);
	}
	
	private function RegisterTrigger($Name, $Ident, $Typ, $Parent, $Position, $Skript)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
			$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
			IPS_DeleteEvent($eid);
			$eid = 0;
		}
		
		//we need to create one
		if ($eid == 0) {
		    $EventID = IPS_CreateEvent($Typ);
			IPS_SetEventTrigger($EventID, 4, $this->ReadPropertyInteger('TriggerID'));// bei Bestimmten Wert True nur auslösen
			IPS_SetEventTriggerValue($EventID, true); //Nur auf TRUE Werte auslösen
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventScript($EventID, $Skript); 
			IPS_SetEventActive($EventID, true);  
		}
	}
	
	private function RegisterTriggerZiel($Name, $Ident, $Typ, $Parent, $Position, $Skript)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
			$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
			IPS_DeleteEvent($eid);
			$eid = 0;
		}
		
		//we need to create one
		if ($eid == 0) {
		    $EventID = IPS_CreateEvent($Typ);
			IPS_SetEventTrigger($EventID, 1, $this->GetIDForIdent('ZielPos'));
			//IPS_SetEventTrigger($EventID, 1,$this->ReadPropertyInteger('ZielPos'));
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventScript($EventID, $Skript); 
			IPS_SetEventActive($EventID, true); 
		}
	}
	
	private function RegisterMedia($Name, $Ident, $Typ, $Parent)
	{
	    	//IP-Adress
		$IP = $this->ReadPropertyString('IPAdress');
		
		//User
		$user = $this->ReadPropertyString('UserName');

		//Password
		$pass = $this->ReadPropertyString('UserPassword');
		
		$EventID = IPS_CreateMedia(3);
		IPS_SetMediaFile($EventID, "rtsp://$user:$pass@$IP:554/ch1/main/av_stream", true);   // Image im MedienPool mit Image-Datei verbinden
		IPS_SetName($EventID, "IP Cam"); // Medienobjekt benennen
	}
}
