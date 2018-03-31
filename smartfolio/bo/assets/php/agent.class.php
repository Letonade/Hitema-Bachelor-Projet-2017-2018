<?php

/**
* AGENT CLASS
*/
class Agent
{
    public $infos;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM user WHERE user_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"        => $infos['user_id'],
                "name"      => $infos['user_name'],
                "email"     => $infos['user_email'],
                "manager"   => $infos['user_manager'],
                "type"      => $infos['user_type'],
                "activated" => $infos['user_activated_account']
            );
        }
    }

    // EDIT AGENT
    static public function Edit($infos)
    {
        // Data validation
        if ($infos['user_manager'] != 0 && $infos['user_type'] == 'manager') {
            return array(false, 'un manager ne peut avoir un manager');
        }
        if ($infos['user_manager'] == 0 && $infos['user_type'] == 'agent') {
            return array(false, 'un agent doit avoir un manager');
        }

        // Save user
        $save_user = App::$db->prepare("UPDATE user SET user_name = :name, user_email = :email WHERE user_id = :id");
        $save_user->execute(array(
            "name"      => $infos['user_name'],
            "email"     => $infos['user_email'],
            "id"        => $infos['user_id']
        ));
        return $save_user ? array(true) : array(false, 'erreur');
    }

    // DELETE AGENT
    public function Delete()
    {
        $nb_folios = $this->CountTotalFolios();
        $nb_agents = $this->CountAgents();
        if ($nb_folios != 0) {
            return array(false, $nb_folios . ' portfolio(s) existe(nt) toujours pour cet agent');
        } elseif ($nb_agents != 0) {
            return array(false, $nb_agents . ' agent(s) existe(nt) toujours pour ce manager');
        }
        $del_user = App::$db->prepare("DELETE FROM user WHERE user_id = :id");
        $del_user->execute(array(
            "id" => $this->infos['id']
        ));
        return array(true);
    }

    // GET NUMBER OF AGENT PORTFOLIO
    public function CountTotalFolios()
    {
        $nb_folios = App::$db->prepare("SELECT COUNT(*) FROM portfolio WHERE port_agent_id = :id");
        $nb_folios->execute(array(
            "id" => $this->infos['id']
        ));
        return $nb_folios->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
    }

    // GET NUMBER OF AGENT ACTIVE PORTFOLIO
    public function CountActiveFolios()
    {
        $nb_folios = App::$db->prepare("SELECT COUNT(*) FROM portfolio WHERE port_agent_id = :id AND port_status = 'open'");
        $nb_folios->execute(array(
            "id" => $this->infos['id']
        ));
        return $nb_folios->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
    }

    // GET NUMBER OF AGENTS IN A MANAGER TEAM
    public function CountAgents()
    {
        if ($this->infos['type'] != 'manager') {
            return 0;
        }
        $nb_agents = App::$db->prepare("SELECT COUNT(*) FROM user WHERE user_manager = :id");
        $nb_agents->execute(array(
            "id" => $this->infos['id']
        ));
        return $nb_agents->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $agents)
    {
        $agents = $agents->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($agents as $ag) {
            $agent = new Agent($ag['user_id']);
            array_push($o, $agent);
        }
        return $o;
    }

    // GET FULL AGENTS LIST
    static public function FullList()
    {
        $agents = App::$db->query("SELECT user_id FROM user ORDER BY user_name");
        return self::ReturnObjectsArray($agents);
    }

    // GET MANAGER TEAM
    static public function MyTeam()
    {
        $agents = App::$db->prepare("SELECT user_id FROM user WHERE user_manager = :manager ORDER BY user_name");
        $agents->execute(array(
            "manager" => $_SESSION['user']['id']
        ));
        return self::ReturnObjectsArray($agents);
    }

    // GET MANAGER LIST
    static public function ManagerList()
    {
        $managers = App::$db->query("SELECT user_id FROM user WHERE user_type = 'manager' ORDER BY user_name");
        return self::ReturnObjectsArray($managers);
    }

    // SEARCH AMONG AGENTS
    static public function Search($keywords)
    {
        $keywords = explode(' ', $keywords);
        $query = "SELECT user_id FROM user WHERE";
        $query_array = array();
        for ($i = 0; $i < count($keywords); $i += 1) {
            $query .= " ( user_name LIKE :search_" . $i;
            $query .= " OR user_email LIKE :search_" . $i;
            $query .= " OR (SELECT a.user_name AS manager FROM user AS a WHERE a.user_id = user.user_manager) LIKE :search_" . $i;
            $query .= " OR user_type = :search_" . $i;
            if ($i != (count($keywords) - 1)) {
                $query .= " ) AND ";
            } else {
                $query .= " ) ";
            }
            $query_array['search_' . $i] = "%" . $keywords[$i] . "%";
        }
        $query .= "ORDER BY user_name";
        $results = App::$db->prepare($query);
        $results->execute($query_array);
        return self::ReturnObjectsArray($results);
    }
}


?>