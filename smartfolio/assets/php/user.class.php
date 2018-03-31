<?php

/**
* CLASSE UTILISATEUR
*/

class User
{
    // LOGIN
    static public function Login($login, $password)
    {
        $user = App::$db->prepare("SELECT * FROM user WHERE user_name = :login OR user_email = :login");
        $user->execute(array(
            "login" => trim($login)
        ));
        // ERR: not found
        if ($user->rowCount() != 1) {
            return array(false, 'nom d\'utilisateur ou email inconnu.');
        }
        $user = $user->fetch(PDO::FETCH_ASSOC);
        // ERR: not activated
        if ($user['user_activated_account'] == false) {
            return array(false, 'compte non activé.');
        }
        // ERR: wrong pwd
        if (!password_verify($password, $user['user_password'])) {
            return array(false, 'mot de passe incorrect.');
        }
        $_SESSION['user'] = array(
            "id"            => $user['user_id'],
            "name"          => $user['user_name'],
            "email"         => $user['user_email'],
            "type"          => $user['user_type'],
            "token"         => $user['user_token'],
            "session_token" => self::GenerateToken(),
        );
        return array(true);
    }

    // GENERATE A PASSWORD
    static public function GeneratePassword($length = 25)
    {
        return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(random_bytes($length))),0,$length);
    }

    // GENERATE A TOKEN
    static public function GenerateToken()
    {
        $maj = range('A', 'Z');
        $min = range('a', 'z');
        $num = range('1', '25');
        $fusion = array_merge($maj, $min, $num);
        $token = null;
        shuffle($fusion);
        foreach ($fusion as $valeur) {
            $token .= $valeur;
        }
        return $token;
    }

    // VALIDATE TOKEN
    public function CheckToken($token)
    {
        return isset($_SESSION['user']['session_token'])
        ? $token == $_SESSION['user']['session_token']
        : false;
    }

    // CHECK IF USER IS MANAGER
    static public function AmIManager()
    {
        $user = App::$db->prepare("SELECT user_type FROM user WHERE user_id = :id");
        $user->execute(array(
            "id" => $_SESSION['user']['id']
        ));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        return $user['user_type'] == 'manager';
    }

    // VALIDATE EMAIL FORMAT
    static public function ValidateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
            return array(false, 'Adresse email incorrecte');
        }
        return array(true);
    }

    // CHECK IF EMAIL IS ALREADY USED
    /**
    *   @var $upd = user id
    */
    static private function AvailableEmail($email, $upd = false)
    {
        if ($upd != false) {
            $duplicate = App::$db->prepare("SELECT COUNT(*) FROM user WHERE user_email = :email AND user_id != :id");
            $duplicate->execute(array(
                "email" => trim($email),
                "id"    => $upd
            ));
        } else {
            $duplicate = App::$db->prepare("SELECT COUNT(*) FROM user WHERE user_email = :email");
            $duplicate->execute(array(
                "email" => trim($email)
            ));
        }
        $duplicate = $duplicate->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
        return $duplicate == 0;
    }

    // CREATE NEW AGENT
    static public function Create($infos)
    {
        // Data validation
        $valid_email = self::ValidateEmail($infos['user_email']);
        if (!$valid_email[0]) {
            return $valid_email;
        }
        $valid_email = self::AvailableEmail($infos['user_email']);
        if (!$valid_email) {
            return array(false, 'adresse email indisponible');
        }
        if (strlen(trim($infos['user_name'])) < 5) {
            return array(false, 'la longueur du nom doit être de 5 caractères minimum');
        }
        if ($infos['user_manager'] != 0 && $infos['user_type'] == 'manager') {
            return array(false, 'un manager ne peut avoir un manager');
        }
        if ($infos['user_manager'] == 0 && $infos['user_type'] == 'agent') {
            return array(false, 'un agent doit avoir un manager');
        }

        // Set password & token
        $user_pwd = self::GeneratePassword();
        $user_tkn = self::GenerateToken();

        // Save agent
        $save_agent = App::$db->prepare("INSERT INTO user (user_name, user_email, user_manager, user_password, user_type, user_token) VALUES (:name, :email, :manager, :password, :type, :token)");
        $save_agent->execute(array(
            "name"     => $infos['user_name'],
            "email"    => $infos['user_email'],
            "manager"  => $infos['user_manager'] == 0 ? null : $infos['user_manager'],
            "password" => password_hash($user_pwd, PASSWORD_DEFAULT),
            "type"     => $infos['user_type'],
            "token"    => password_hash($user_tkn, PASSWORD_DEFAULT)
        ));
        if (!$save_agent) {
            return array(false, 'erreur lors de l\'enregistrement');
        } else {
            return self::RegistrationMail($infos['user_email'], $user_pwd, $user_tkn);
        }
    }

    // SEND EMAIL WHEN USER HAS BEEN REGISTERED
    static private function RegistrationMail($email, $password, $token)
    {
        $activation_link = PROTOCOL . '://' . ROOT_DIR . '/index.php?valid=' . $email . '&token=' . $token;
        $content = '<h1>Bienvenue !</h1>';
        $content .= '<p>';
        $content .= 'Un compte vous a été attribué sur SmartFolio. Avant de vous connecter, validez votre inscription en suivant le lien suivant:<br>';
        $content .= '<a href="' . $activation_link . '">' . $activation_link . '</a><br><br>';
        $content .= 'Voici vos informations de connexion:<br>';
        $content .= '<b>Login:</b> ' . $email . '<br>';
        $content .= '<b>Mot de passe:</b> ' . $password . '<br>';
        $content .= '</p>';
        $content .= '<hr><h6>Ceci est un email automatique, merci de ne pas y répondre</h6>';
        return App::TextMail($email, 'subject', $content) ? array(true) : array(false, 'erreur lors de l\'envoi du mail d\'activation');
    }

    // EDIT USER
    static public function Edit($infos)
    {
        // Data validation
        $valid_email = self::ValidateEmail($infos['user_email']);
        if (!$valid_email[0]) {
            return $valid_email;
        }
        $valid_email = self::AvailableEmail($infos['user_email'], $this->infos['id']);
        if (!$valid_email) {
            return array(false, 'adresse email indisponible');
        }
        if (strlen(trim($infos['user_name'])) < 5) {
            return array(false, 'la longueur du nom doit être de 5 caractères minimum');
        }

        // Save user
        $save_user = App::$db->prepare("UPDATE user SET user_name = :name, user_email = :email WHERE user_id = :id");
        $save_user->execute(array(
            "name"      => $infos['user_name'],
            "email"     => $infos['user_email'],
            "id"        => $_SESSION['user']['id']
        ));
        return $save_user ? array(true) : array(false, 'erreur');
    }

    // // VALIDE UN MOT DE PASSE
    // public function ValideMdp($password, $confirm)
    // {
    //     if (iconv_strlen($password) < 8) {
    //         return array(false, 'Le mot de passe doit contenir au minimum 8 caractères.');
    //     }
    //     if ($password != $confirm) {
    //         return array(false, 'Les mots de passe ne sont pas identiques.');
    //     }
    //     return array(true);
    // }
    //
    // // VERIFIE LA DISPONIBILITE D'UN NOM D'UTILISATEUR
    // public function UsernameDispo($username, $change = false)
    // {
    //     $change = $change ? 'AND id != ' . $this->infos['id'] : '';
    //     $dispo = $this->bdd->prepare("SELECT * FROM utilisateur WHERE username = :username $change");
    //     $dispo->execute(array(
    //         "username" => $username
    //     ));
    //     return $dispo->rowCount() == 0;
    // }
    //
    // // VALIDE UN NOM D'UTILISATEUR
    // public function ValideUsername($username, $change = false)
    // {
    //     $dispo = $this->UsernameDispo($username, $change);
    //     $valide = preg_match('#^[a-zA-Z0-9._-]+$#', $username);
    //     if (!$dispo) {
    //         return array(false, 'nom d\'utilisateur indisponible');
    //     } elseif (!$valide) {
    //         return array(false, 'caractères non-autorisés. Utilisez uniquement des lettres, chiffres et les caractères suivants: ._-');
    //     }
    //     return array(true);
    // }
    //
    // // METTRE A JOUR 1 INFO
    // public function Set($info, $valeur)
    // {
    //     $update = $this->bdd->prepare("UPDATE utilisateur SET $info = :valeur WHERE id = :id");
    //     $update->execute(array(
    //         "valeur"    => $valeur,
    //         "id"        => $this->infos['id']
    //     ));
    //     return $update ? array(true) : array(false, 'erreur');
    // }
    //
    // // MODIFIER MOT DE PASSE
    // public function ChangeMdp($pwd, $confirm)
    // {
    //     $validation = $this->ValideMdp($pwd, $confirm);
    //     if (!$validation[0]) {
    //         return $validation;
    //     } else {
    //         return $this->Set('password', password_hash($pwd, PASSWORD_DEFAULT));
    //     }
    // }
}


?>
