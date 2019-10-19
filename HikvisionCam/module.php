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
		$this->RegisterPropertyString('IPAdress', "192.168.2.62");
		$this->RegisterPropertyString('Name', "Hikvision Cam");
		$this->RegisterPropertyString('UserName', "admin");
		$this->RegisterPropertyString('UserPassword', "");
		$this->RegisterPropertyString('ISAPI', "/ISAPI/PTZCtrl/channels/1/presets/2/goto");
		$this->RegisterPropertyString('Preset', "");
		// Snapshot Parameter
		$this->RegisterPropertyInteger('No_Picture', 10);
		$this->RegisterPropertyString('URL', "http://192.168.2.62/Streaming/channels/1/picture");
		$this->RegisterPropertyInteger('Break', 100);
		$this->RegisterPropertyString('Picture_Path', "D:");

		// Alarm Parameter
		$this->RegisterPropertyInteger('Alarm', 0);
		
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
		$result = 'Ergebnis konnte nicht ermittelt werden!';
		// Daten lesen
		$state = true;
		
		$filecams = ARRAY();
		//********** Eine Reihe von Bildern machen im Abstand von $pause Msec  *********
		
		$anz_bilder = $this->ReadPropertyInteger('No_Picture');
		
		for ( $i=0;$i<$anz_bilder;$i++)
		{
			//Datum und Uhrzeit festlegen
			$time = date("d.").date("m.").date("Y")."_".date("H-i-s");
			$datum = date("Y.").date("m.").date("d")."\\";

			$bildpfad = $this->ReadPropertyString('Picture_Path');
			$directoryPath = $bildpfad.$datum; 
			if (!file_exists($directoryPath)) 
			{
				mkdir($directoryPath);
			}

			//Bildverzeichnis
			$name_cam = $this->ReadPropertyString('Name');
			$file = $directoryPath.$name_cam."_".$time.".jpg"; 

			//Bilder machen und im Bildverzeichnis ablegen
			$user = $this->ReadPropertyString('UserName');
			$pass = $this->ReadPropertyString('UserPassword');
			$url = $this->ReadPropertyString('URL');
			
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

				$pause = $this->ReadPropertyInteger('Break');
				IPS_SLEEP($pause);
		}

	}
}
