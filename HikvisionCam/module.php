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

		// MWindow Parameter
		$this->RegisterPropertyInteger('Window_1', 0);
		$this->RegisterPropertyInteger('Window_2', 0);
		$this->RegisterPropertyInteger('Window_3', 0);
		
		// Messenger Parameter
		$this->RegisterPropertyInteger('ID_Messenger', 0);
		$this->RegisterPropertyBoolean('Switch_Messenger', false);   
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
	}
}
