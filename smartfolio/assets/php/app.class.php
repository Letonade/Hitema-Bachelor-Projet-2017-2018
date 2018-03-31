<?php

/**
* APPLICATION
*/
class App
{
    static public $response;
    static public $db;

    static public function SetDB($db)
    {
        self::$db = $db;
    }

    static public function Debug($var, $dump = false)
    {
        echo $dump ? var_dump($var) : '<pre>' . print_r($var, true) . '</pre>';
    }

    // RESPOND TO AN ACTION
    static public function Respond($action, $error = false)
    {
        if (!isset($action) || empty($action)) {
            throw new Exception("Error with __METHOD__ : missing argument", 1);
        } else {
            self::$response .= '<p class="' . ($error ? 'error' : 'success') . '"><i class="' . ($error ? 'fas fa-exclamation-circle' : 'far fa-check-circle') . '"></i> ';
            self::$response .= $action . ' : ' . ($error ?: 'ok !');
            self::$response .= '</p>';
        }
    }

    // NEUTRAL MESSAGE
    static public function Message($message)
    {
        self::$response .= empty($message) ? '' : '<p class="info"><i class="fas fa-info-circle"></i> ' . $message . '</p>';
    }

    // DISPLAY MESSAGES
    static public function DisplayMessages()
    {
        echo empty(self::$response) ? '' : '<div id="response">' . self::$response . '</div>';
    }

    // RETURNS A SLUG
    static public function Slug($string)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $string)));
    }

    // SENDS A TEXT MAIL
    public function TextMail($address, $subject, $content)
    {
        $header = "From : " . NO_REPLY_ADDR . " Smartfolio \r\n";
        $header .= "reply-To : " . NO_REPLY_ADDR . " \r\n";
        $header .= "MIME-version: 1.0 \r\n";
        $header .= "content-type : text/html; charset=utf-8 \r\n";
        $header .= "X-mailer: PHP/" . phpversion();

        return mail($address, $subject, $content, $header);
    }
}


?>
