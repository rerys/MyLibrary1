<?php

require_once 'controller/controllerbis.php';
require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'useful/ToolsBis.php';

class ControllerMain extends ControllerBis {

    //gestion de la connexion d'un utilisateur
    public function login() {
        $username = '';
        $password = '';
        $errors = [];
        if (ToolsBis::check_fields(['username', 'password'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $errors = User::validate_login($username, $password);
            if (empty($errors)) {
                $this->log_user(User::get_user_by_username($username));
            }
        }
        (new View("login"))->show(array("username" => $username, "password" => $password, "errors" => $errors));
    }

    public function signup() {
        $username = '';
        $password = '';
        $confirm_password = '';
        $fullname = '';
        $email = '';
        $birthdate = '';
        $errors = [];
        if (ToolsBis::check_fields(['username', 'password', 'password_confirm', 'fullname', 'email', 'birthdate'])) {
            $username = trim($_POST['username']);
            $fullname = trim($_POST['fullname']);
            $password = trim($_POST['password']);
            $password_confirm = trim($_POST['password_confirm']);
            $email = trim($_POST['email']);
            $birthdate = trim($_POST['birthdate']);
            $errors = (new User($fullname, $username, $password, $email, null, $birthdate, null))->validate_user();
            if ($password !== $password_confirm) {
                $errors[] = "You have to enter twice the same password.";
            }
            if (count($errors) == 0) {
                User::add_user($username, $password, $fullname, $email, $birthdate);
                $this->log_user(User::get_user_by_username($username));
            }
        }
        (new View("signup"))->show(array("username" => $username, "password" => $password,
            "password_confirm" => $confirm_password, "fullname" => $fullname, "email" => $email,
            "birthdate" => $birthdate, "errors" => $errors));
    }

    //services

    public function user_available_service() {
        $res = "true";
        if (isset($_POST["username"]) && $_POST["username"] !== "") {
            $user = User::get_user_by_username($_POST["username"]);
            if ($user) {
                $res = "false";
            }
        }
        echo $res;
    }

    public function email_available_service() {
        $res = "true";
        if (isset($_POST["email"]) && $_POST["email"] !== "") {
            $user = User::get_user_by_email($_POST["email"]);
            if ($user) {
                $res = "false";
            }
        }
        echo $res;
    }


}
