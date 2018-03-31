<?php

/**
* PORTFOLIO CLASS
*/
class Portfolio
{

    // function __construct(argument)
    // {
    //     # code...
    // }

    static public function UserOpenList()
    {
        switch ($_SESSION['user']['type']) {
            case 'manager':
            $folios = App::$db->query("SELECT * FROM portfolio WHERE port_status = 'open'");
            $folios = $folios->fetchAll(PDO::FETCH_ASSOC);
            return $folios;
            break;

            case 'agent':
            $folios = App::$db->prepare("SELECT * FROM portfolio WHERE port_agent_id = :agent AND port_status = 'open'");
            $folios->execute(array(
                "agent" => $_SESSION['user']['id']
            ));
            $folios = $folios->fetchAll(PDO::FETCH_ASSOC);
            return $folios;
            break;

            default:
            return null;
            break;
        }
    }
}


?>