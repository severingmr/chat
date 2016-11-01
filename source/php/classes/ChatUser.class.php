<?php

class ChatUser extends ChatBase{
	
	protected $name = '', $gravatar = '', $email='', $status='';
	
	public function save(){

    DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->gravatar)."'
		)");

    return DB::getMySQLiObject();
}

	public function registriern(){

    DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->name)."'
		)");

    return DB::getMySQLiObject();
}

	
	public function update(){
		DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->gravatar)."'
			) ON DUPLICATE KEY UPDATE last_activity = NOW()");
	}
}

?>