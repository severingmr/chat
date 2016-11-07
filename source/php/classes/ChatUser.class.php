<?php

class ChatUser extends ChatBase{
	
	protected $name = '', $gravatar = '', $email='', $status='';

    public function admin() {
        DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->status)."'
		)");

        return DB::getMySQLiObject();

    }
	
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
			INSERT INTO user (name, email, status)
			VALUES (
				'".DB::esc($this->name)."',
				'".DB::esc($this->email)."',
				'".DB::esc($this->status)."'
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

    public function getStatus(){

        //funktioniert noch nicht--> noch checken
        DB::query("
			SELECT count (*)
			FROM USER 
			WHERE email = '".DB::esc($this->email)."' AND name = '".DB::esc($this->name)."' AND status = 'ok'
		  ");
		  return DB::getMySQLiObject();
    }
}

?>