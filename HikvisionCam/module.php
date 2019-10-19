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
		
		//Anzahl Bilder
		$anz_bilder = $this->ReadPropertyInteger('No_Picture');
		if ($anz_bilder != 0) 
		{
		    $anz_bilder = GetValue($anz_bilder);
		} 
		else 
		{
		    $this->SendDebug('UPDATE', 'Number of pictures not set!');
		    $state = false;
		}
		
		for ( $i=0;$i<$anz_bilder;$i++)
		{
			//Bildpfad
			$bildpfad = $this->ReadPropertyString('Picture_Path');
			if ($bildpfad != 0) 
			{
			    $bildpfad = GetValue($bildpfad);
			} 
			else 
			{
			    $this->SendDebug('UPDATE', 'Path of pictures not set!');
			    $state = false;
			}

			//Datum und Uhrzeit festlegen	
			$time = date("d.").date("m.").date("Y")."_".date("H-i-s");
			$datum = date("Y.").date("m.").date("d")."\\";

			//Bildpfad erstellen
			$directoryPath = $bildpfad.$datum; 
			if (!file_exists($directoryPath)) 
			{
				mkdir($directoryPath);
			}

			//Bildverzeichnis
			$name_cam = $this->ReadPropertyString('Name');
			$file = $directoryPath.$name_cam."_".$time.".jpg"; 

			//User
			$user = $this->ReadPropertyString('UserName');
			if ($user != 0) 
			{
			    $user = GetValue($user);
			} 
			else 
			{
			    $this->SendDebug('UPDATE', 'User not set!');
			    $state = false;
			}
			
			//Password
			$pass = $this->ReadPropertyString('UserPassword');
			if ($pass != 0) 
			{
			    $pass = GetValue($pass);
			} 
			else 
			{
			    $this->SendDebug('UPDATE', 'Password not set!');
			    $state = false;
			}			
			
			//URL
			$url = $this->ReadPropertyString('URL');
			if ($url != 0) 
			{
			    $url = GetValue($url);
			} 
			else 
			{
			    $this->SendDebug('UPDATE', 'Password not set!');
			    $state = false;
			}
			
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
				
				//Break
				$pause = $this->ReadPropertyInteger('Break');
				if ($pause != 0) 
				{
				    $pause = GetValue($pause);
				} 
				else 
				{
				    $this->SendDebug('UPDATE', 'Break between pictures not set!');
				    $state = false;
				}
				IPS_SLEEP($pause);
			
				//Alarm
				$alarm = $this->ReadPropertyInteger('Alarm');
			
				if ($alarm != 0) 
				{
				    $alarm = GetValue($alarm);
				} 
				else 
				{
				    $this->SendDebug('UPDATE', 'Alarm Contact not set!');
				    $state = false;
				}

				If ($alarm == true)
				{
					//Messagetexte und Titel
					$text 	= $this->ReadPropertyInteger('Messenger_Text').date("d.m.y - H:i:s");
					if ($text != 0) 
					{
					    $text = GetValue($text);
					} 
					else 
					{
					    $this->SendDebug('UPDATE', 'Messenger text not set!');
					    $state = false;
					}
					$titel	= $this->ReadPropertyInteger('Messenger_Title');
					if ($titel != 0) 
					{
					    $titel = GetValue($titel);
					} 
					else 
					{
					    $this->SendDebug('UPDATE', 'Messenger title not set!');
					    $state = false;
					}
					
					//Message verschicken
					switch ($_IPS['SENDER'])
					{
						//0 = Aus; 1 = Notification Text; 2 = Notification Audio, 3 = Pushover; 4 = Telegramm; 5 = Noti + Push
						case $message_kamera == 0: //Aus

						case $message_kamera == 4: //Telegramm
							Telegram_SendImage($tele_ID, $text, $file, $tele_user); 
						break;
					}

					//Meldung im IPS Logger
					IPSUtils_Include ("IPSLogger.inc.php", "IPSLibrary::app::core::IPSLogger");
					IPSLogger_Not($titel, $text); 
				}

		}

	}
}
