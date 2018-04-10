<?php

/**
* PAIR CLASS
*/
class Pair
{
    public $infos;
    public $symbol;
    public $last_update;
    public $first_update;
    public $currency;
    public $index;
    public $exchange;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM pair WHERE pair_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"       => $infos['pair_id'],
                "curr_a"   => $infos['pair_curr_a'],
                "curr_b"   => $infos['pair_curr_b'],
                "exchange" => $infos['pair_exchange_id'],
                "api_url"  => $infos['pair_api_url']
            );
            $this->currency = new Currency($this->infos['curr_a']);
            $this->index    = new Currency($this->infos['curr_b']);
            $this->exchange = new Exchange($this->infos['exchange']);
            $this->FullName();
            $this->LastCandlestick();
            $this->FirstCandlestick();
        }
    }

    // EDIT PAIR
    public function Edit(array $infos)
    {
        // Check empty
        if (empty($infos['pair_api_url'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (!self::CheckDuplicate($infos['pair_curr_a'], $infos['pair_curr_b'], $infos['pair_exchange_id'], $this->infos['id'])) {
            return array(false, 'paire déjà existante');
        }
        // Update
        $upd_pair = App::$db->prepare("UPDATE pair SET pair_curr_a = :curr_a, pair_curr_b = :curr_b, pair_exchange_id = :exchange, pair_api_url = :api_url WHERE pair_id = :id");
        $upd_pair->execute(array(
            "curr_a"   => $infos['pair_curr_a'],
            "curr_b"   => $infos['pair_curr_b'],
            "exchange" => $infos['pair_exchange_id'],
            "api_url"  => $infos['pair_api_url'],
            "id"       => $this->infos['id']
        ));
        $this->__construct($this->infos['id']);
        return $upd_pair ? array(true) : array(false, 'erreur');
    }

    // DELETE EXCHANGE
    public function Delete($confirm)
    {
        if (!$confirm) {
            return array(false, 'veuillez confirmer la suppression');
        }
        // Delete OHLC
        $del_ohlc = App::$db->prepare("DELETE FROM ohlc WHERE ohlc_pair_id = :pair_id");
        $del_ohlc->execute(array(
            "pair_id" => $this->infos['id']
        ));
        if (!$del_ohlc) {
            return array(false, 'impossible de supprimer les données de la paire ' . $this->symbol);
        }
        // Delete pair
        $del_pair = App::$db->prepare("DELETE FROM pair WHERE pair_id = :id");
        $del_pair->execute(array(
            "id" => $this->infos['id']
        ));
        return $del_pair ? array(true) : array(false, 'impossible de supprimer la paire ' . $this->symbol);
    }

    // GET THE FULL PAIR NAME SYMBOL
    private function FullName()
    {
        $currency     = $this->currency->infos['symbol'];
        $index        = $this->index->infos['symbol'];
        $exchange     = strtoupper($this->exchange->infos['name']);
        $this->symbol = $currency . '/' . $index . ':' . $exchange;
    }

    // GET LAST CANDLESTICK TIMESTAMP
    private function LastCandlestick()
    {
        $last_candlestick = App::$db->prepare("SELECT ohlc_timestamp FROM ohlc WHERE ohlc_pair_id = :pair ORDER BY ohlc_timestamp DESC LIMIT 1");
        $last_candlestick->execute(array(
            "pair" => $this->infos['id']
        ));
        if ($last_candlestick->rowCount() == 0) {
            $this->last_update = false;
        } else {
            $this->last_update = $last_candlestick->fetch(PDO::FETCH_ASSOC)['ohlc_timestamp'];
        }
    }

    // GET FIRST CANDLESTICK TIMESTAMP
    private function FirstCandlestick()
    {
        if (OHLC_FETCH_PREVIOUS === false) {
            $this->first_update = false;
        } else {
            $first_candlestick = App::$db->prepare("SELECT ohlc_timestamp FROM ohlc WHERE ohlc_pair_id = :pair ORDER BY ohlc_timestamp ASC LIMIT 1");
            $first_candlestick->execute(array(
                "pair" => $this->infos['id']
            ));
            if ($first_candlestick->rowCount() == 0) {
                $this->first_update = false;
            } else {
                $this->first_update = $first_candlestick->fetch(PDO::FETCH_ASSOC)['ohlc_timestamp'];
            }
        }
    }

    // GET LAST OHLC DATA
    public function GetChartData()
    {
        $candlesticks = App::$db->prepare("SELECT * FROM ohlc WHERE ohlc_pair_id = :pair_id ORDER BY ohlc_timestamp DESC LIMIT 0, 300");
        $candlesticks->execute(array(
            "pair_id" => $this->infos['id']
        ));
        return $candlesticks->fetchAll(PDO::FETCH_ASSOC);
    }

    // CREATE EXCHANGE
    static public function Create(array $infos)
    {
        // Check empty
        if (empty($infos['pair_api_url'])) {
            return array(false, 'champ(s) vide(s)');
        }
        // Check if already exists
        if (!self::CheckDuplicate($infos['pair_curr_a'], $infos['pair_curr_b'], $infos['pair_exchange_id'])) {
            return array(false, 'paire déjà existante');
        }
        // Save
        $new_pair = App::$db->prepare("INSERT INTO pair (pair_curr_a, pair_curr_b, pair_exchange_id, pair_api_url) VALUES (:curr_a, :curr_b, :exchange, :api_url)");
        $new_pair->execute(array(
            "curr_a"   => $infos['pair_curr_a'],
            "curr_b"   => $infos['pair_curr_b'],
            "exchange" => $infos['pair_exchange_id'],
            "api_url"  => $infos['pair_api_url']
        ));
        return $new_pair ? array(true) : array(false, 'erreur');
    }

    // CHECK IF EXCHANGE ALREADY EXISTS
    static private function CheckDuplicate($currency, $index, $exchange, $upd = false)
    {
        if ($upd) {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM pair WHERE pair_curr_a = :curr_a AND pair_curr_b = :curr_b AND pair_exchange_id = :exchange AND pair_id != :id");
            $duplicates->execute(array(
                "curr_a"   => $currency,
                "curr_b"   => $index,
                "exchange" => $exchange,
                "id"       => $upd
            ));
        } else {
            $duplicates = App::$db->prepare("SELECT COUNT(*) FROM pair WHERE pair_curr_a = :curr_a AND pair_curr_b = :curr_b AND pair_exchange_id = :exchange");
            $duplicates->execute(array(
                "curr_a"   => $currency,
                "curr_b"   => $index,
                "exchange" => $exchange
            ));
        }
        return $duplicates->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0;
    }

    // SEARCH AMONG AGENTS
    static public function Search($keywords)
    {
        $keywords = explode(' ', $keywords);
        $query = "SELECT pair_id FROM pair LEFT JOIN currency AS curr_a ON pair.pair_curr_a = curr_a.curr_id LEFT JOIN currency AS curr_b ON pair.pair_curr_b = curr_b.curr_id LEFT JOIN exchange ON pair.pair_exchange_id = exchange.exchange_id WHERE";
        $query_array = array();
        for ($i = 0; $i < count($keywords); $i += 1) {
            $query .= " ( pair.pair_api_url LIKE :search_" . $i;
            $query .= " OR curr_a.curr_name LIKE :search_" . $i;
            $query .= " OR curr_a.curr_symbol LIKE :search_" . $i;
            $query .= " OR curr_b.curr_name LIKE :search_" . $i;
            $query .= " OR curr_b.curr_symbol LIKE :search_" . $i;
            $query .= " OR exchange.exchange_name LIKE :search_" . $i;
            if ($i != (count($keywords) - 1)) {
                $query .= " ) AND ";
            } else {
                $query .= " ) ";
            }
            $query_array['search_' . $i] = "%" . $keywords[$i] . "%";
        }
        $query .= "ORDER BY curr_b.curr_symbol";
        $results = App::$db->prepare($query);
        $results->execute($query_array);
        return self::ReturnObjectsArray($results);
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $pairs)
    {
        $pairs = $pairs->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($pairs as $pr) {
            $pair = new Pair($pr['pair_id']);
            array_push($o, $pair);
        }
        return $o;
    }

    // GET ALL PAIRS
    static public function FullList()
    {
        $pair_list = App::$db->query("SELECT pair_id FROM pair ORDER BY pair_api_url");
        return self::ReturnObjectsArray($pair_list);
    }

    // GET PAIRS OF A CURRENCY
    static public function FindByCurrency(Currency $currency, $position = 'both')
    {
        switch ($position) {
            case 'both':
            $pairs = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_curr_a = :curr OR pair_curr_b = :curr ORDER BY pair_api_url");
            break;

            case 'currency':
            $pairs = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_curr_a = :curr ORDER BY pair_api_url");
            break;

            case 'index':
            $pairs = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_curr_b = :curr ORDER BY pair_api_url");
            break;

            default:
            throw new \Exception("position must be currency or index", 1);
            break;
        }
        $pairs->execute(array(
            "curr" => $currency->infos['id']
        ));
        return self::ReturnObjectsArray($pairs);
    }

    // GET PAIRS OF AN EXCHANGE
    static public function FindByExchange(Exchange $exchange)
    {
        $pairs = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_exchange_id = :exchange ORDER BY pair_api_url");
        $pairs->execute(array(
            "exchange" => $exchange->infos['id']
        ));
        return self::ReturnObjectsArray($pairs);
    }
}


?>