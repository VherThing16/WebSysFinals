<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    public $isSMTPCalled = false;
    public $SMTPAuth, $Host, $Username, $Password, $Port, $SMTPSecure;
    public $Subject, $Body;

    public function isSMTP() { $this->isSMTPCalled = true; }
    public function setFrom($email, $name = '') {}
    public function addAddress($email) {}
    public function send() { return true; }
}
