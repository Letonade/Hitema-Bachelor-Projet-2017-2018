<?php

/**
* EXCHANGE CLASS
*/
class Exchange
{
    public $infos;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM exchange WHERE exchange_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"     => $infos['exchange_id'],
                "name"   => $infos['exchange_name']
            );
        }
    }

    // EDIT EXCHANGE
    public function Edit(array $infos)
    {
        // Check empty
        if (empty($infos['exchange_name'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (self::CheckDuplicate($infos['exchange_name'], $this->infos['id'])) {
            return array(false, 'échange déjà existant');
        }
        // Update
        $upd_exchange = App::$db->prepare("UPDATE exchange SET exchange_name = :name WHERE exchange_id = :id");
        $upd_exchange->execute(array(
            "name" => trim($infos['name']),
            "id"   => $this->infos['id']
        ));
        $this->__construct($this->infos['id']);
        return $upd_exchange ? array(true) : array(false, 'erreur');
    }

    // DELETE EXCHANGE
    public function Delete($confirm)
    {
        if (!$confirm) {
            return array(false, 'veuillez confirmer la suppression');
        }
        $pair_list = Pair::FindByExchange($this);
        foreach ($pair_list as $pair) {
            $del_pair = $pair->Delete($confirm);
            if (!$del_pair[0]) {
                return $del_pair;
            }
        }
        $del_exchange = App::$db->prepare("DELETE FROM exchange WHERE exchange_id = :id");
        $del_exchange->execute(array(
            "id" => $this->infos['id']
        ));
        return $del_exchange ? array(true) : array(false, 'impossible de supprimer l\'échange ' . $this->infos['name']);
    }

    // CREATE EXCHANGE
    static public function Create(array $infos)
    {
        // Check empty
        if (empty($infos['exchange_name'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (self::CheckDuplicate($infos['exchange_name'])) {
            return array(false, 'échange déjà existant');
        }
        // Save
        $new_exchange = App::$db->prepare("INSERT INTO exchange (exchange_name) VALUES (:name)");
        $new_exchange->execute(array(
            "name"   => trim($infos['name'])
        ));
        return $new_exchange ? array(true) : array(false, 'erreur');
    }

    // CHECK IF EXCHANGE ALREADY EXISTS
    static private function CheckDuplicate($name, $upd = false)
    {
        if ($upd) {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM exchange WHERE exchange_name = :name AND exchange_id != :id");
            $duplicates->execute(array(
                "name"   => $name,
                "id"     => $upd
            ));
        } else {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM exchange WHERE exchange_name = :name");
            $duplicates->execute(array(
                "name"   => $name
            ));
        }
        return $duplicates->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0;
    }

    // SEARCH AMONG EXCHANGES
    static public function Search($keywords)
    {
        $keywords = explode(' ', $keywords);
        $query = "SELECT exchange_id FROM exchange WHERE";
        $query_array = array();
        for ($i = 0; $i < count($keywords); $i += 1) {
            $query .= " ( exchange_name LIKE :search_" . $i;
            if ($i != (count($keywords) - 1)) {
                $query .= " ) AND ";
            } else {
                $query .= " ) ";
            }
            $query_array['search_' . $i] = "%" . $keywords[$i] . "%";
        }
        $query .= "ORDER BY exchange_name";
        $results = App::$db->prepare($query);
        $results->execute($query_array);
        return self::ReturnObjectsArray($results);
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $exchanges)
    {
        $exchanges = $exchanges->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($exchanges as $exch) {
            $exchange = new Exchange($exch['exchange_id']);
            array_push($o, $exchange);
        }
        return $o;
    }

    static public function FullList()
    {
        $exchange = App::$db->query("SELECT exchange_id FROM exchange ORDER BY exchange_name");
        return self::ReturnObjectsArray($exchange);
    }
}


?>