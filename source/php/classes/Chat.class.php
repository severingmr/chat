<?php

/* The Chat class exploses public static methods, used by ajax.php */

class Chat
{

    public static function login($name, $email)
    {
        if (!$name || !$email) {
            throw new Exception('Fill in all the required fields.');
        }

        if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Your email is invalid.');
        }

        if (!self::isAllowed($name, $email)) {

            return array(
                'error' => 'kein Login'
            );
        }


        // Preparing the gravatar hash:
        $gravatar = md5(strtolower(trim($email)));
        $escaped_name = htmlspecialchars($name);

        $user = new ChatUser(array(
            'name' => $escaped_name,
            'gravatar' => $gravatar
        ));

        // The save method returns a MySQLi object
        if ($user->save()->affected_rows != 1) {
            throw new Exception('This nick is in use.');
        }

        $_SESSION['user'] = array(
            'name' => $escaped_name,
            'gravatar' => $gravatar
        );

        return array(
            'status' => 1,
            'name' => $escaped_name,
            'gravatar' => Chat::gravatarFromHash($gravatar)
        );
    }


    public static function register($name, $email)
    {
        if (!$name || !$email) {
            throw new Exception('Fill in all the required fields.');
        }

        if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Your email is invalid.');
        }


        // Preparing the gravatar hash:
        $gravatar = md5(strtolower(trim($email)));
        $escaped_name = htmlspecialchars($name);
        $escaped_email = htmlspecialchars($email);
        $escaped_status = 'register';

        $user = new ChatUser(array(
            'name' => $escaped_name,
            'email' => $escaped_email,
            'status' => $escaped_status,
        ));

        // The save method returns a MySQLi object
        if ($user->registriern()->affected_rows != 1) {
            throw new Exception('This nick is in use.');
        }


        return array(
            'status' => 'registered'
        );
    }


    public static function checkLogged()
    {
        $response = array('logged' => false);

        if ($_SESSION['user']['name']) {
            $response['logged'] = true;
            $response['loggedAs'] = array(
                'name' => $_SESSION['user']['name'],
                'gravatar' => Chat::gravatarFromHash($_SESSION['user']['gravatar'])
            );
        }

        return $response;
    }

    public static function loginAdmin($name, $email)
    {
        if (!$name || !$email) {
            throw new Exception('Fill in all the required fields.');
        }

        if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Your email is invalid.');
        }

        if (!self::isAdministrator($name, $email)){
            return false;
        } else {

            session_start();
            $_SESSION['admin'] = $name;

            return true;
        }




        // Preparing the gravatar hash:
        //$gravatar = md5(strtolower(trim($email)));
        //$escaped_name = htmlspecialchars($name);


    }

    public static function logout()
    {
        DB::query("DELETE FROM webchat_users WHERE name = '" . DB::esc($_SESSION['user']['name']) . "'");

        $_SESSION = array();
        unset($_SESSION);

        return array('status' => 1);
    }

    public static function submitChat($chatText)
    {
        if (!$_SESSION['user']) {
            throw new Exception('You are not logged in');
        }

        if (!$chatText) {
            throw new Exception('You haven\' entered a chat message.');
        }
        $vcl = htmlspecialchars($chatText);

        $chat = new ChatLine(array(
            'author' => $_SESSION['user']['name'],
            'gravatar' => $_SESSION['user']['gravatar'],
            'text' => $vcl
        ));

        // The save method returns a MySQLi object
        $insertID = $chat->save()->insert_id;

        return array(
            'status' => 1,
            'insertID' => $insertID
        );
    }

    public static function getUsers()
    {
        if ($_SESSION['user']['name']) {
            $user = new ChatUser(array('name' => $_SESSION['user']['name']));
            $user->update();
        }

        // Deleting chats older than 5 minutes and users inactive for 30 seconds

        DB::query("DELETE FROM webchat_lines WHERE ts < SUBTIME(NOW(),'0:5:0')");
        DB::query("DELETE FROM webchat_users WHERE last_activity < SUBTIME(NOW(),'0:0:30')");

        $result = DB::query('SELECT * FROM webchat_users ORDER BY name ASC LIMIT 18');

        $users = array();
        while ($user = $result->fetch_object()) {
            $user->gravatar = Chat::gravatarFromHash($user->gravatar, 30);
            $users[] = $user;
        }

        return array(
            'users' => $users,
            'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users')->fetch_object()->cnt
        );
    }

    public static function getChats($lastID)
    {
        $lastID = (int)$lastID;

        $result = DB::query('SELECT * FROM webchat_lines WHERE id > ' . $lastID . ' ORDER BY id ASC');

        $chats = array();
        while ($chat = $result->fetch_object()) {

            // Returning the GMT (UTC) time of the chat creation:

            $chat->time = array(
                'hours' => gmdate('H', strtotime($chat->ts)),
                'minutes' => gmdate('i', strtotime($chat->ts))
            );

            $chat->gravatar = Chat::gravatarFromHash($chat->gravatar);

            $chats[] = $chat;
        }

        return array('chats' => $chats);
    }

    public static function gravatarFromHash($hash, $size = 23)
    {
        return 'http://www.gravatar.com/avatar/' . $hash . '?size=' . $size . '&amp;default=' .
        urlencode('http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?size=' . $size);
    }


    public static function adminGetUser()
    {

        if ($_SESSION['admin']) {
            $result = DB::query('SELECT * FROM user ORDER BY status');
            $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
            return $users;
        } else {
            return array(
                'error' => 'no session'
            );
        }

    }

    public static function deleteUser($data_uid)
    {

        $esc_ID = DB::esc($data_uid);
        $result = DB::query("DELETE FROM user WHERE id = '" . $esc_ID . "'");

        return $result;

    }

    public static function saveUser($data_uid, $dstatus)
    {
        $esc_data_id = DB::esc($data_uid);
        $esc_dstatus = DB::esc($dstatus);

        $result = DB::query("UPDATE user SET status = '" . $esc_dstatus . "' WHERE id = '" . $esc_data_id . "'");
        return $result;
    }


    public static function isAllowed($name, $email)
    {
        return false;
        $esc_name = DB::esc($name);
        $esc_email = DB::esc($email);
        $stmt = DB::query("SELECT COUNT(*) AS cnt FROM user WHERE email='" . $esc_email . "' AND name='" . $esc_name . "' AND status='ok'");
        $count = $stmt->fetch_object()->cnt;
        return ($count > 0);
    }

    public static function isAdministrator($name, $email)
    {

        $n = DB::esc($name);
        $e = DB::esc($email);

        $stmt = DB::query("SELECT COUNT(*) AS cnt FROM user WHERE email='" . $e . "' AND name='" . $n . "' AND status='admin'");
        $count = $stmt->fetch_object()->cnt;
        return ($count > 0);


        //$res = DB::query("SELECT COUNT(*) FROM user WHERE email='" . $e . "' AND name='" . $n . "'AND status='admin'")->fetch_object();
        //return ($res > 0);
    }
}


?>