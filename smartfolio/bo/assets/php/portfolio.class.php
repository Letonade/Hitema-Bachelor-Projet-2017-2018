<?php

/**
* PORTFOLIO CLASS
*/
class Portfolio
{
    public $infos;
    public $customer;
    public $agent;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM portfolio WHERE port_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"     => $infos['port_id'],
                "status" => $infos['port_status']
            );
            $this->customer = new Customer($infos['port_cust_id']);
            $this->agent    = new Agent($infos['port_agent_id']);
        }
    }

    // CREATE NEW PORTFOLIO
    static public function Create(array $infos)
    {
        if (!isset($infos['port_cust_id']) || !isset($infos['port_agent_id'])) {
            return array(false, 'champ(s) manquant(s)');
        }
        // save
        $new_portfolio = App::$db->prepare("INSERT INTO portfolio (port_cust_id, port_agent_id, port_status) VALUES (:port_cust_id, :port_agent_id, 'open')");
        $new_portfolio->execute(array(
            "port_cust_id" => $infos['port_cust_id'],
            "port_agent_id" => $infos['port_agent_id']
        ));
        return $new_portfolio ? array(true) : array(false, 'erreur');
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $portfolios)
    {
        $portfolios = $portfolios->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($portfolios as $port) {
            $portfolio = new Portfolio($port['port_id']);
            array_push($o, $portfolio);
        }
        return $o;
    }

    static public function UserOpenList()
    {
        switch ($_SESSION['user']['type']) {
            case 'manager':
            $folios = App::$db->query("SELECT * FROM portfolio WHERE port_status = 'open'");
            break;

            case 'agent':
            $folios = App::$db->prepare("SELECT * FROM portfolio WHERE port_agent_id = :agent AND port_status = 'open'");
            $folios->execute(array(
                "agent" => $_SESSION['user']['id']
            ));
            break;

            default:
            return null;
            break;
        }
        return self::ReturnObjectsArray($folios);
    }
}


?>