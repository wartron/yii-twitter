<?php

class YiiTwitter extends CApplicationComponent
{

	//The Twitter Apps key, set in config.
	public $consumer_key = '';
	
	//The Twitter Apps secret key, set in config.	
	public $consumer_secret = '';
	
	//The call back url for twitter
	public $callback = '';	
	
	//Can be set in config to not load OAuth.php
	public $load_oauth = true;	
	
	//Have we loaded our dependencies
	private static $registeredScripts = false;

	public function init() {
		$this->registerScripts();
		parent::init();	
	}	
	
	/**
	* Returns the callback url set in config
	*/
	public function getCallback() {
		return $this->callback;
	}
	
	
	/**
	* Use this one for when we need to authicate oursevles with twitter
	*/
	public function getTwitter() {
		return new TwitterOAuth($this->consumer_key,$this->consumer_secret);			
	}

	/**
	* Use this for after we have a token and a secret for the use.
	*	(you must save these in order for them to be usefull
	*/
	public function getTwitterTokened($token,$secret) {
		return new TwitterOAuth($this->consumer_key,$this->consumer_secret,$token,$secret);	
	}
		
    /**
    * Registers twitteroauth.php & OAuth.php
    */
    public function registerScripts() {
    	if (self::$registeredScripts) return;
    	self::$registeredScripts = true;
		if($this->load_oauth)
			require dirname(__FILE__).'/OAuth.php';
		require dirname(__FILE__).'/twitteroauth.php';
	}	
		
}




