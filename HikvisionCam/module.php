<?php
	class HikvisionCam extends IPSModule {

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			// Camera Parameter
        		$this->RegisterPropertyString('IP', '');
        		$this->RegisterPropertyString('Name', '');
        		$this->RegisterPropertyString('User', '');
        		$this->RegisterPropertyString('Password', '');
        		$this->RegisterPropertyString('ISAPI', '');
        		$this->RegisterPropertyString('Preset', '');
			
			// Snapshot Parameter
        		$this->RegisterPropertyInteger('No_Picture', 0);
        		$this->RegisterPropertyString('URL', '');
        		$this->RegisterPropertyInteger('Break', 0);
        		$this->RegisterPropertyString('Picture_Path', '');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

	}
