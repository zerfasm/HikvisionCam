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
		$this->RegisterPropertyString('Name', "Hikvision Cam");
		$this->RegisterPropertyString('UserName', "admin");
		$this->RegisterPropertyString('UserPassword', "");
		$this->RegisterPropertyString('ISAPI', "/ISAPI/PTZCtrl/channels/1/presets/1/goto");
		$this->RegisterPropertyString('ISAPI_Start', "/ISAPI/PTZCtrl/channels/1/presets/1/got");
		
		// Snapshot Parameter
		$this->RegisterPropertyInteger('No_Picture', 6);
		$this->RegisterPropertyString('URLSnapshot', "http://ip/Streaming/channels/1/picture");
		$this->RegisterPropertyInteger('Break', 100);
		$this->RegisterPropertyString('Picture_Path', "");

		// Alarm Parameter
		$this->RegisterPropertyInteger('Alarm', 0);
		
		//Logging
		$this->RegisterPropertyBoolean('Logging', false);
		
		// Messenger Parameter
		$this->RegisterPropertyInteger('Messenger_ID', 0);
		$this->RegisterPropertyBoolean('Messenger_Switch', false); 
		$this->RegisterPropertyString('Messenger_Title', "");
		$this->RegisterPropertyString('Messenger_Text', "");
	}

	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();  	
	}
	
	public function Update()
    	{
		//User
		$user = $this->ReadPropertyString('UserName');

		//Password
		$pass = $this->ReadPropertyString('UserPassword');

		//URL Snapshot
		$url = $this->ReadPropertyString('URLSnapshot');
	
		//URL SLiveview
		$urllive = $this->ReadPropertyString('URLLiveview');

		//ISAPI Target
		$ISAPI = $this->ReadPropertyString('ISAPI');
		
		//ISAPI Start
		$ISAPI_Start = $this->ReadPropertyString('ISAPI_Start');

		//IP-Adress
		$IP = $this->ReadPropertyString('IPAdress');
		
		//Anzahl Bilder
		$anz_bilder = $this->ReadPropertyInteger('No_Picture');
		
		//Bildverzeichnis
		$name_cam = $this->ReadPropertyString('Name');
		
		//Bildpfad
		$bildpfad = $this->ReadPropertyString('Picture_Path');
		
		//Break
		$pause = $this->ReadPropertyInteger('Break');
		
		//Alarm
		$alarm = $this->ReadPropertyInteger('Alarm');
		
		//Logging
		$logg = $this->ReadPropertyBoolean('Logging'); 
		
		//Messagetexte und Titel
		$text 	= $this->ReadPropertyString('Messenger_Text').date("d.m.y - H:i:s");
		$titel	= $this->ReadPropertyString('Messenger_Title');
		
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
		    $header  = "PUT $ISAPI HTTP/1.1\r\n";
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

			if ($alarm != 0) 
			{
			    $alarm = GetValue($alarm);
			} 
			else 
			{
			    $this->SendDebug('UPDATE', 'Alarm Contact not set!');
			    $state = false;
			}
			
			If ($logg = true)
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
}
