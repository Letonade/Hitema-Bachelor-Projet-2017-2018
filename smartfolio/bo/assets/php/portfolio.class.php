<?php

/**
* PORTFOLIO CLASS
*/
class Portfolio
{
    public $infos;
    public $customer;
    public $agent;
    public $accumulators;
    public $investments = [];

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

    // GET ACCUMULATORS INVESTMENTS
    public function GetAccumulators()
    {
        $accumulators = array_filter(Investment::GetInvestments($this->infos['id']), function($inv) {
            return $inv->type == 'accumulator';
        });
        return $accumulators;
    }

    // GET CLASSIC INVESTMENTS
    public function GetInvestments()
    {
        $investments = array_filter(Investment::GetInvestments($this->infos['id']), function($inv) {
            return $inv->type == 'investment';
        });
        return $investments;
    }

    // ADD AN ACCUMULATOR
    public function AddAccumulator(int $acc_id)
    {
        try {
            $acc = new Currency($acc_id);

            // Prevent accumulator duplication
            $duplicate = App::$db->prepare("SELECT COUNT(*) FROM port_accumulator WHERE acc_port_id = :port_id AND acc_curr_id = :curr_id");
            $duplicate->execute(array(
                "port_id" => $this->infos['id'],
                "curr_id" => $acc->infos['id']
            ));
            if ($duplicate->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] > 0) {
                return array(false, 'accumulateur déjà existant');
            }

            // Save accumulator
            $new_acc = App::$db->prepare("INSERT INTO port_accumulator (acc_port_id, acc_curr_id) VALUES (:port_id, :curr_id)");
            $new_acc->execute(array(
                "port_id" => $this->infos['id'],
                "curr_id" => $acc->infos['id']
            ));
            return $new_acc ? array(true) : array(false, 'erreur');

        } catch (\Exception $e) {
            return array(false, 'Accumulateur - ' . $e->getMessage());
        }
    }

    // REMOVE AN ACCUMULATOR
    public function RemoveAccumulator(int $acc_id)
    {
        try {
            $acc = new Currency($acc_id);

            // Retrieve accumulator
            $actual = App::$db->prepare("SELECT COUNT(*) FROM port_accumulator WHERE acc_port_id = :port_id AND acc_curr_id = :curr_id");
            $actual->execute(array(
                "port_id" => $this->infos['id'],
                "curr_id" => $acc->infos['id']
            ));
            if ($actual->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0) {
                return array(false, 'accumulateur introuvable');
            }

            // Delete accumulator
            $del_acc = App::$db->prepare("DELETE FROM port_accumulator WHERE acc_port_id = :port_id AND acc_curr_id = :curr_id");
            $del_acc->execute(array(
                "port_id" => $this->infos['id'],
                "curr_id" => $acc->infos['id']
            ));
            return $del_acc ? array(true) : array(false, 'erreur');

        } catch (\Exception $e) {
            return array(false, 'Accumulateur - ' . $e->getMessage());
        }
    }

    // FIRST TRANSACTION OF PORTFOLIO/INVESTMENT
    public function FirstTx(array $infos)
    {
        if (!in_array($infos['tx_type'], array('deposit', 'buy'))) {
            return array(false, 'Mauvais type de transaction');
        }
        switch ($infos['tx_type']) {
            case 'deposit':
            preg_match_all("/\[([^\]]*)\]/", $infos['tx_transfer_curr_id'], $curr_symbol);
            $curr_infos = array(
                'name'   => trim(substr($infos['tx_transfer_curr_id'], (strpos($infos['tx_transfer_curr_id'], "] ") ?: -1) + 1)),
                'symbol' => $curr_symbol[1][0] ?? ''
            );
            $tx_curr = Currency::GetByTitle($curr_infos);
            $infos['tx_transfer_curr_id'] = $tx_curr->infos['id'] ?? 0;
            break;
        }
        return $this->NewTx($infos);
    }

    // ADD NEW TRANSACTION
    public function NewTx(array $infos)
    {
        switch ($infos['tx_type']) {
            // DEPOSIT
            case 'deposit':
            // Get matching data
            $tx_curr = Currency::GetById($infos['tx_transfer_curr_id']);
            $exchange_infos = array(
                'name' => trim($infos['tx_transfer_exchange_id_to'])
            );
            $tx_exchange_to = Exchange::GetByTitle($exchange_infos);
            $tx_datetime    = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp   = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation     = [
                "aucune monnaie sélectionnée"  => empty($infos['tx_transfer_curr_id']),
                "monnaie introuvable"          => $tx_curr === false,
                "aucun exchange sélectionné"   => empty($infos['tx_transfer_exchange_id_to']),
                "exchange introuvable"         => $tx_exchange_to === false,
                "montant incorrect"            => !is_numeric($infos['tx_amount']),
                "le montant doit être positif" => floatval($infos['tx_amount']) <= 0,
                "date/heure incorrectes"       => $tx_datetime === false,
                "date & heure futures"         => $tx_timestamp === false ? false : $tx_timestamp > (new DateTime())->getTimestamp()
            ];
            if (in_array(true, $validation)) {
                return [false, array_search(true, $validation)];
            }
            // Save
            $new_tx = App::$db->prepare("INSERT INTO transaction (tx_port_id, tx_type, tx_transfer_curr_id, tx_transfer_exchange_id_to, tx_amount, tx_timestamp) VALUES (:tx_port_id, :tx_type, :tx_transfer_curr_id, :tx_transfer_exchange_id_to, :tx_amount, :tx_timestamp)");
            $new_tx->execute(array(
                "tx_port_id"                 => $this->infos['id'],
                "tx_type"                    => 'deposit',
                "tx_transfer_curr_id"        => $tx_curr->infos['id'],
                "tx_transfer_exchange_id_to" => $tx_exchange_to->infos['id'],
                "tx_amount"                  => $infos['tx_amount'],
                "tx_timestamp"               => $tx_timestamp
            ));
            return $new_tx ? array(true) : array(false, 'erreur');
            break;

            // BUY / SELL
            case 'buy':
            case 'sell':
            // Get matching data
            $pair_parts = explode('/', $infos['tx_pair_id']);
            $pair_parts_2 = explode(':', $pair_parts[1] ?? '');
            $tx_pair = Pair::GetByTitle([
                'currency' => $pair_parts[0],
                'index'    => $pair_parts_2[0],
                'exchange' => ucfirst(strtolower($pair_parts_2[1] ?? ''))
            ]);
            $tx_datetime  = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation   = array(
                "aucune paire sélectionnée"                     => empty($infos['tx_pair_id']),
                "paire introuvable"                             => $tx_pair === false,
                "montant incorrect"                             => !is_numeric($infos['tx_amount']),
                "le montant doit être positif"                  => floatval($infos['tx_amount']) <= 0,
                "prix incorrect"                                => !is_numeric($infos['tx_price']),
                "le prix doit être positif"                     => floatval($infos['tx_price']) < 0,
                "montant des frais incorrect"                   => !is_numeric($infos['tx_fee_amount']),
                "le montant des frais ne doit pas être négatif" => floatval($infos['tx_fee_amount']) < 0,
                "type de frais inconnu"                         => !in_array($infos['tx_fee_type'], ['fixed_currency', 'fixed_index', 'percent_currency', 'percent_index']),
                "date/heure incorrectes"                        => $tx_datetime === false,
                "date & heure futures"                          => $tx_timestamp === false ? false : $tx_timestamp > (new DateTime())->getTimestamp()
            );
            if (in_array(true, $validation)) {
                return array(false, array_search(true, $validation));
            }
            // Save
            $new_tx = App::$db->prepare("INSERT INTO transaction (tx_port_id, tx_type, tx_pair_id, tx_price, tx_amount, tx_fee_amount, tx_fee_type, tx_timestamp) VALUES (:tx_port_id, :tx_type, :tx_pair_id, :tx_price, :tx_amount, :tx_fee_amount, :tx_fee_type, :tx_timestamp)");
            $new_tx->execute(array(
                "tx_port_id"    => $this->infos['id'],
                "tx_type"       => $infos['tx_type'],
                "tx_pair_id"    => $tx_pair->infos['id'],
                "tx_price"      => $infos['tx_price'],
                "tx_amount"     => $infos['tx_amount'],
                "tx_fee_amount" => $infos['tx_fee_amount'],
                "tx_fee_type"   => $infos['tx_fee_type'],
                "tx_timestamp"  => DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour'])->getTimestamp()
            ));
            return $new_tx ? array(true) : array(false, 'erreur');
            break;

            // TRANSFER
            case 'transfer':
            // Get matching data
            $tx_curr = Currency::GetById($infos['tx_transfer_curr_id']);
            $exchange_from_infos = array(
                'name' => trim($infos['tx_transfer_exchange_id_from'])
            );
            $tx_exchange_from = Exchange::GetByTitle($exchange_from_infos);
            $exchange_to_infos = array(
                'name' => trim($infos['tx_transfer_exchange_id_to'])
            );
            $tx_exchange_to = Exchange::GetByTitle($exchange_to_infos);
            $tx_datetime    = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp   = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation     = array(
                "aucune monnaie sélectionnée"         => empty($infos['tx_transfer_curr_id']),
                "monnaie introuvable"                 => $tx_curr === false,
                "sélectionnez 2 exchanges"            => empty($infos['tx_transfer_exchange_id_from']) || empty($infos['tx_transfer_exchange_id_to']),
                "sélectionnez 2 exchanges différents" => $infos['tx_transfer_exchange_id_from'] == $infos['tx_transfer_exchange_id_to'],
                "exchange à débiter introuvable"      => $tx_exchange_from === false,
                "exchange à créditer introuvable"     => $tx_exchange_to === false,
                "montant incorrect"                   => !is_numeric($infos['tx_amount']),
                "le montant doit être positif"        => floatval($infos['tx_amount']) <= 0,
                "montant des frais incorrect"         => !is_numeric($infos['tx_fee_amount']),
                "montant des frais négatif"           => floatval($infos['tx_fee_amount']) < 0,
                "type de frais incorrect"             => !in_array($infos['tx_fee_type'], ['fixed_currency', 'percent_currency']),
                "date/heure incorrectes"              => $tx_datetime === false,
                "date & heure futures"                => $tx_timestamp === false ? false : $tx_timestamp > (new DateTime())->getTimestamp()
            );
            if (in_array(true, $validation)) {
                return array(false, array_search(true, $validation));
            }
            // Save
            $new_tx = App::$db->prepare("INSERT INTO transaction (tx_port_id, tx_type, tx_transfer_curr_id, tx_transfer_exchange_id_from, tx_transfer_exchange_id_to, tx_amount, tx_fee_amount, tx_fee_type, tx_timestamp) VALUES (:tx_port_id, :tx_type, :tx_transfer_curr_id, :tx_transfer_exchange_id_from, :tx_transfer_exchange_id_to, :tx_amount, :tx_fee_amount, :tx_fee_type, :tx_timestamp)");
            $new_tx->execute(array(
                "tx_port_id"                   => $this->infos['id'],
                "tx_type"                      => 'transfer',
                "tx_transfer_curr_id"          => $tx_curr->infos['id'],
                "tx_transfer_exchange_id_from" => $tx_exchange_from->infos['id'],
                "tx_transfer_exchange_id_to"   => $tx_exchange_to->infos['id'],
                "tx_amount"                    => $infos['tx_amount'],
                "tx_fee_amount"                => $infos['tx_fee_amount'],
                "tx_fee_type"                  => $infos['tx_fee_type'],
                "tx_timestamp"                 => $tx_timestamp
            ));
            return $new_tx ? array(true) : array(false, 'erreur');
            break;

            // WITHDRAW
            case 'withdraw':
            // Get matching data
            $tx_curr = Currency::GetById($infos['tx_transfer_curr_id']);
            $exchange_infos = array(
                'name' => trim($infos['tx_transfer_exchange_id_from'])
            );
            $tx_exchange_from = Exchange::GetByTitle($exchange_infos);
            $tx_datetime    = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp   = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation     = array(
                "aucune monnaie sélectionnée"  => empty($infos['tx_transfer_curr_id']),
                "monnaie introuvable"          => $tx_curr === false,
                "aucun exchange sélectionné"   => empty($infos['tx_transfer_exchange_id_from']),
                "exchange introuvable"         => $tx_exchange_from === false,
                "montant incorrect"            => !is_numeric($infos['tx_amount']),
                "le montant doit être positif" => floatval($infos['tx_amount']) <= 0,
                "montant des frais incorrect"  => !is_numeric($infos['tx_fee_amount']),
                "montant des frais négatif"    => floatval($infos['tx_fee_amount']) < 0,
                "type de frais incorrect"      => !in_array($infos['tx_fee_type'], ['fixed_currency', 'percent_currency']),
                "date/heure incorrectes"       => $tx_datetime === false,
                "date & heure futures"         => $tx_timestamp === false ? false : $tx_timestamp > (new DateTime())->getTimestamp()
            );
            if (in_array(true, $validation)) {
                return array(false, array_search(true, $validation));
            }
            // Save
            $new_tx = App::$db->prepare("INSERT INTO transaction (tx_port_id, tx_type, tx_transfer_curr_id, tx_transfer_exchange_id_from, tx_amount, tx_fee_amount, tx_fee_type, tx_timestamp) VALUES (:tx_port_id, :tx_type, :tx_transfer_curr_id, :tx_transfer_exchange_id_from, :tx_amount, :tx_fee_amount, :tx_fee_type, :tx_timestamp)");
            $new_tx->execute(array(
                "tx_port_id"                   => $this->infos['id'],
                "tx_type"                      => 'deposit',
                "tx_transfer_curr_id"          => $tx_curr->infos['id'],
                "tx_transfer_exchange_id_from" => $tx_exchange_from->infos['id'],
                "tx_amount"                    => $infos['tx_amount'],
                "tx_fee_amount"                => $infos['tx_fee_amount'],
                "tx_fee_type"                  => $infos['tx_fee_type'],
                "tx_timestamp"                 => $tx_timestamp
            ));
            return $new_tx ? array(true) : array(false, 'erreur');
            break;

            // ERROR
            default:
            return array(false, 'type de transaction inconnu');
            break;
        }
    }

    // GET 1 INVESTMENT
    public function FetchOneInvestment(int $curr_id)
    {
        $tx_list = App::$db->prepare("SELECT * FROM transaction LEFT JOIN pair ON transaction.tx_pair_id = pair.pair_id WHERE transaction.tx_port_id = :port_id AND ((transaction.tx_type = 'deposit' AND transaction.tx_transfer_curr_id = :curr_id) OR (transaction.tx_type = 'buy' AND (pair.pair_curr_a = :curr_id OR pair.pair_curr_b = :curr_id))) ORDER BY transaction.tx_timestamp");
        $tx_list->execute(array(
            "port_id" => $this->infos['id'],
            "curr_id" => $curr_id
        ));
        $tx_history = array();
        foreach ($tx_list->fetchAll(PDO::FETCH_ASSOC) as $tx) {
            switch ($tx['tx_type']) {
                case 'deposit':
                $tx_history[] = array(
                    'type'     => 'deposit',
                    'amount'   => floatval($tx['tx_amount']),
                    'currency' => new Currency($tx['tx_transfer_curr_id']),
                    'exchange' => new Exchange($tx['tx_transfer_exchange_id_to']),
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i')
                );
                break;

                case 'buy':
                $pair = new Pair($tx['tx_pair_id']);
                $tx_history[] = array(
                    'type'     => $pair->currency->infos['id'] == $curr_id ? 'buy' : 'sell',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => floatval($tx['tx_price']),
                    'currency' => $pair->currency,
                    'index'    => $pair->index,
                    'exchange' => $pair->exchange,
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i')
                );
                break;
            }
        }
        return $tx_history;
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