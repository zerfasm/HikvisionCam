<?php
	class HikvisionCam extends IPSModule {

/**
     * public properties
     */
    public $ch = '';
    public $user = '';
    public $password = '';
    public $site = 'default';
    public $baseurl = 'https://127.0.0.1:8443';
    public $version = '5.4.16';

    /**
     * private properties
     */
    private $debug = false;
    private $is_loggedin = false;
    private $cookies = '';
    private $request_type = 'POST';
    private $last_results_raw;
    private $last_error_message;

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
	$this->RegisterPropertyInteger('No_Picture', 9);
	$this->RegisterPropertyString('URL', "http://192.168.2.62/Streaming/channels/1/picture");
	$this->RegisterPropertyInteger('Break', 100);
	$this->RegisterPropertyString('Picture_Path', "D:");
    }
}
