<?php
	class HikvisionCam extends IPSModule {

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			// Camera Parameter
        		$this->RegisterPropertyString('IP', 0);
        		$this->RegisterPropertyString('Name', 0);
        		$this->RegisterPropertyString('User', 0);
        		$this->RegisterPropertyString('Password', 0);
        		$this->RegisterPropertyString('ISAPI', 0);
        		$this->RegisterPropertyString('Preset', 0);
			
			// Snapshot Parameter
        		$this->RegisterPropertyInteger('No_Picture', 0);
        		$this->RegisterPropertyString('URL', 0);
        		$this->RegisterPropertyInteger('Break', 0);
        		$this->RegisterPropertyString('Picture_Path', 0);
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
