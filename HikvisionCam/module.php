<?php

    class HikvisionCam extends IPSModule {

    public function Create() {
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
	    
	// Messenger Parameter
	$this->RegisterPropertyInteger('ID_Messenger', 0);
	$this->RegisterPropertyInteger('ID_Messenger', 0);   
    }
		
    public function ApplyChanges()
    {
	//Never delete this line!
        parent::ApplyChanges();    
    }
}
