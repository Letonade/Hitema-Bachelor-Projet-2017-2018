<?php

/**
* CURRENCY CLASS
*/
class Currency
{
    public $infos;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM currency WHERE curr_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"     => $infos['curr_id'],
                "name"   => $infos['curr_name'],
                "symbol" => $infos['curr_symbol']
            );
        }
    }

    // EDIT CURRENCY
    public function Edit(array $infos)
    {
        // Check empty
        if (empty($infos['curr_name']) || empty($infos['curr_symbol'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (!self::CheckDuplicate($infos['curr_name'], $infos['curr_symbol'], $this->infos['id'])) {
            return array(false, 'monnaie déjà existante');
        }
        // Update
        $upd_currency = App::$db->prepare("UPDATE currency SET curr_name = :name, curr_symbol = :symbol WHERE curr_id = :id");
        $upd_currency->execute(array(
            "name"   => trim($infos['curr_name']),
            "symbol" => strtoupper(trim($infos['curr_symbol'])),
            "id"     => $this->infos['id']
        ));
        $this->__construct($this->infos['id']);
        return $upd_currency ? array(true) : array(false, 'erreur');
    }

    // DELETE CURRENCY
    public function Delete($confirm)
    {
        if (!$confirm) {
            return array(false, 'veuillez confirmer la suppression');
        }
        $pair_list = Pair::FindByCurrency($this);
        foreach ($pair_list as $pair) {
            $del_pair = $pair->Delete($confirm);
            if (!$del_pair[0]) {
                return $del_pair;
            }
        }
        $del_currency = App::$db->prepare("DELETE FROM currency WHERE curr_id = :id");
        $del_currency->execute(array(
            "id" => $this->infos['id']
        ));
        return $del_currency ? array(true) : array(false, 'impossible de supprimer la monnaie [' . $this->Infos['symbol'] . '] ' . $this->infos['name']);
    }

    // CREATE CURRENCY
    static public function Create(array $infos)
    {
        // Check empty
        if (empty($infos['curr_name']) || empty($infos['curr_symbol'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (!self::CheckDuplicate($infos['curr_name'], $infos['curr_symbol'])) {
            return array(false, 'monnaie déjà existante');
        }
        // Save
        $new_currency = App::$db->prepare("INSERT INTO currency (curr_name, curr_symbol) VALUES (:name, :symbol)");
        $new_currency->execute(array(
            "name"   => trim($infos['curr_name']),
            "symbol" => strtoupper(trim($infos['curr_symbol']))
        ));
        return $new_currency ? array(true) : array(false, 'erreur');
    }

    // CHECK IF CURRENCY ALREADY EXISTS
    static private function CheckDuplicate($name, $symbol, $upd = false)
    {
        if ($upd) {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM currency WHERE curr_name = :name AND curr_symbol = :symbol AND curr_id != :id");
            $duplicates->execute(array(
                "name"   => $name,
                "symbol" => $symbol,
                "id"     => $upd
            ));
        } else {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM currency WHERE curr_name = :name AND curr_symbol = :symbol");
            $duplicates->execute(array(
                "name"   => $name,
                "symbol" => $symbol
            ));
        }
        return $duplicates->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0;
    }

    // SEARCH AMONG CURRENCIES
    static public function Search($keywords)
    {
        $keywords = explode(' ', $keywords);
        $query = "SELECT curr_id FROM currency WHERE";
        $query_array = array();
        for ($i = 0; $i < count($keywords); $i += 1) {
            $query .= " ( curr_name LIKE :search_" . $i;
            $query .= " OR curr_symbol LIKE :search_" . $i;
            if ($i != (count($keywords) - 1)) {
                $query .= " ) AND ";
            } else {
                $query .= " ) ";
            }
            $query_array['search_' . $i] = "%" . $keywords[$i] . "%";
        }
        $query .= "ORDER BY curr_symbol";
        $results = App::$db->prepare($query);
        $results->execute($query_array);
        return self::ReturnObjectsArray($results);
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $currencies)
    {
        $currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($currencies as $curr) {
            $currency = new Currency($curr['curr_id']);
            array_push($o, $currency);
        }
        return $o;
    }

    static public function FullList()
    {
        $currencies_list = App::$db->query("SELECT curr_id FROM currency ORDER BY curr_symbol");
        return self::ReturnObjectsArray($currencies_list);
    }

    // PORTFOLIO: GET ACCUMULATORS LIST
    static public function GetAccumulators(Portfolio $port)
    {
        $accumulators = App::$db->prepare("SELECT acc_curr_id AS curr_id FROM port_accumulator WHERE acc_port_id = :id");
        $accumulators->execute(array(
            "id" => $port->infos['id']
        ));
        return Currency::ReturnObjectsArray($accumulators);
    }

    // TRANSACTION: GET WITH STRINGS
    static public function GetByTitle(array $infos)
    {
        $curr_id = App::$db->prepare("SELECT curr_id FROM currency WHERE curr_name = :name AND curr_symbol = :symbol");
        $curr_id->execute(array(
            "name"   => $infos['name'],
            "symbol" => $infos['symbol']
        ));
        $curr_id = $curr_id->fetch(PDO::FETCH_ASSOC);
        try {
            $currency = new Currency($curr_id['curr_id']);
            return $currency;
        } catch (\Exception $e) {
            return false;
        }
    }

    // TRANSACTION: GET WITH ID
    static public function GetById($curr_id)
    {
        try {
            $currency = new Currency($curr_id);
            return $currency;
        } catch (\Exception $e) {
            return false;
        }
    }
}


?>