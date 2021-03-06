<?php

/* Database Configuration. Add your details below */

$dbOptions = array(
    'db_host' => 'localhost',
    'db_user' => 'aitec',
    'db_pass' => 'dachs',
    'db_name' => 'chat'
);

/* Database Config End */

//report everything except notice
error_reporting(E_ALL ^ E_NOTICE);

require "classes/DB.class.php";
require "classes/Chat.class.php";
require "classes/ChatBase.class.php";
require "classes/ChatLine.class.php";
require "classes/ChatUser.class.php";

session_name('webchat');
session_start();

try {

    // Connecting to the database
    DB::init($dbOptions);

    $response = array();

    // Handling the supported actions:

    switch ($_GET['action']) {

        case 'login':
            //$username = checkInput($_Post['name']);
            //$email = checkInput($_Post['email']);

            //$response = Chat::login($username, $email);

            $response = Chat::login($_POST['name'], $_POST['email']);
            break;

        case 'register':
            //$username = checkInput($_Post['name']);
            //$email = checkInput($_Post['email']);

            //$response = Chat::register($username, $email);

            $response = Chat::register($_POST['name'], $_POST['email']);

            break;

        case 'checkLogged':
            $response = Chat::checkLogged();
            break;

        case 'logout':
            $response = Chat::logout();
            break;

        case 'submitChat':
            $r_chatText = checkInput($_POST['chatText']);

            $response = Chat::submitChat($r_chatText);
            break;

        case 'getUsers':
            $response = Chat::getUsers();
            break;

        case 'getChats':
            $response = Chat::getChats($_GET['lastID']);
            break;

        case 'admin':
            //$username = checkInput($_Post['name']);
            //$email = checkInput($_Post['email']);

            //$response = Chat::loginAdmin($username, $email);

            $response = Chat::loginAdmin($_POST['name'], $_POST['email']);
            break;

        case 'administer':
            $response = Chat::adminGetUser();
            break;

        case 'deleteUser':
            $response = Chat::deleteUser($_POST['uid']);
            break;

        case 'saveUser':
            $response = Chat::saveUser($_POST['uID'], $_POST['status']);
            break;

        default:
            throw new Exception('Wrong action');
    }

    echo json_encode($response);
} catch (Exception $e) {
    die(json_encode(array('error' => $e->getMessage())));
}

function checkInput($chImput)
{
    $trim_input = trim($chImput); //Entfernt Whitespaces am Anfang und Ende eines Stringes
    $trim_input = htmlspecialchars($trim_input, ENT_QUOTES); //Wanderlt Sonderzeichen in HTML-Codes um
    return $trim_input;
}

?>
