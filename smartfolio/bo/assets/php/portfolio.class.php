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
    public $investments = array();

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
            $this->GetInvestments();
            $this->GetAccumulators();
        }
    }

    // GET PORTFOLIO ACCUMULATORS
    private function GetAccumulators()
    {
        $this->accumulators = Currency::GetAccumulators($this);
        foreach ($this->accumulators as $acc) {
            $this->investments[$acc->infos['id']]['type'] = 'accumulator';
        }
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
            // Verification
            $old_acc_list = count($this->accumulators);
            $this->GetAccumulators();
            return $old_acc_list < count($this->accumulators) ? array(true) : array(false, 'erreur');

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
            // Verification
            $old_acc_list = count($this->accumulators);
            $this->GetAccumulators();
            return $old_acc_list > count($this->accumulators) ? array(true) : array(false, 'erreur');

        } catch (\Exception $e) {
            return array(false, 'Accumulateur - ' . $e->getMessage());
        }
    }

    // GET ALL INVESTMENTS
    private function GetInvestments()
    {
        $tx_list = App::$db->prepare("SELECT * FROM transaction WHERE tx_port_id = :port_id ORDER BY tx_timestamp");
        $tx_list->execute(array(
            "port_id" => $this->infos['id']
        ));
        foreach ($tx_list->fetchAll(PDO::FETCH_ASSOC) as $tx) {
            switch ($tx['tx_type']) {
                // DEPOSIT
                case 'deposit':
                if (!isset($this->investments[$tx['tx_transfer_curr_id']])) {
                    $this->investments[$tx['tx_transfer_curr_id']] = array(
                        'type'     => 'investment',
                        'currency' => new Currency($tx['tx_transfer_curr_id']),
                        'balance'  => 0
                    );
                }
                // Add deposit
                $this->investments[$tx['tx_transfer_curr_id']]['balance'] += $tx['tx_amount'];
                break;

                // BUY
                case 'buy':
                $pair = new Pair($tx['tx_pair_id']);
                if (!isset($this->investments[$pair->currency->infos['id']])) {
                    $this->investments[$pair->currency->infos['id']] = array(
                        'type'     => 'investment',
                        'currency' => new Currency($pair->currency->infos['id']),
                        'balance'  => 0
                    );
                }
                // Calculate total order cost w/ fees
                $cost = floatval($tx['tx_amount'] * $tx['tx_price']);
                $amount = $tx['tx_amount'];
                switch ($tx['tx_fee_type']) {
                    case 'fixed_currency':
                    $amount -= $tx['tx_fee_amount'];
                    break;

                    case 'fixed_index':
                    $cost += $tx['tx_fee_amount'];
                    break;

                    case 'percent_currency':
                    $amount -= floatval(floatval($amount / 100) * $tx['tx_fee_amount']);
                    break;

                    case 'percent_index':
                    $cost += floatval(floatval($cost / 100) * $tx['tx_fee_amount']);
                    break;

                    default:
                    $fee_cost = 0;
                    break;
                }
                // Add order
                $this->investments[$pair->currency->infos['id']]['balance'] += $amount;
                // If not holding leave balance to zero
                if (!isset($this->investments[$pair->index->infos['id']])) {
                    $this->investments[$pair->index->infos['id']] = array(
                        'type'     => 'investment',
                        'currency' => new Currency($pair->index->infos['id']),
                        'balance'  => 0
                    );
                } elseif (floatval($this->investments[$pair->index->infos['id']]['balance']) >= floatval($total_cost)) { // Remove index holdings if enough balance
                    $this->investments[$pair->index->infos['id']]['balance'] -= $cost;
                }
                break;
            }
        }
    }

    // FIRST TRANSACTION OF PORTFOLIO/INVESTMENT
    public function FirstTx(array $infos)
    {
        if (!in_array($infos['tx_type'], array('deposit', 'buy'))) {
            return array(false, 'Mauvais type de transaction');
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
            preg_match_all("/\[([^\]]*)\]/", $infos['tx_transfer_curr_id'], $curr_symbol);
            $curr_infos = array(
                'name'   => trim(substr($infos['tx_transfer_curr_id'], (strpos($infos['tx_transfer_curr_id'], "] ") ?: -1) + 1)),
                'symbol' => $curr_symbol[1][0] ?? ''
            );
            $tx_curr        = Currency::GetByTitle($curr_infos);
            $exchange_infos = array(
                'name' => trim($infos['tx_transfer_exchange_id_to'])
            );
            $tx_exchange_to = Exchange::GetByTitle($exchange_infos);
            $tx_datetime    = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp   = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation     = array(
                "aucune monnaie sélectionnée"  => empty($infos['tx_transfer_curr_id']),
                "monnaie introuvable"          => $tx_curr === false,
                "aucun exchange sélectionné"   => empty($infos['tx_transfer_exchange_id_to']),
                "exchange introuvable"         => $tx_exchange_to === false,
                "montant incorrect"            => !is_numeric($infos['tx_amount']),
                "le montant doit être positif" => floatval($infos['tx_amount']) <= 0,
                "date/heure incorrectes"       => $tx_datetime === false,
                "date & heure futures"         => $tx_timestamp === false ? false : $tx_timestamp > (new DateTime())->getTimestamp()
            );
            if (in_array(true, $validation)) {
                return array(false, array_search(true, $validation));
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

            // BUY
            case 'buy':
            // Get matching data
            $pair_parts = explode('/', $infos['tx_pair_id']);
            $pair_parts_2 = explode(':', $pair_parts[1] ?? '');
            $pair_infos = array(
                'currency' => $pair_parts[0],
                'index'    => $pair_parts_2[0],
                'exchange' => ucfirst(strtolower($pair_parts_2[1])) ?? ''
            );
            $tx_pair      = Pair::GetByTitle($pair_infos);
            $tx_datetime  = DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour']);
            $tx_timestamp = $tx_datetime === false ? false : $tx_datetime->getTimestamp();
            // Data validation
            $validation   = array(
                "aucune paire sélectionnée"                     => empty($infos['tx_pair_id']),
                "paire introuvable"                             => $tx_curr === false,
                "montant incorrect"                             => !is_numeric($infos['tx_amount']),
                "le montant doit être positif"                  => floatval($infos['tx_amount']) <= 0,
                "prix incorrect"                                => !is_numeric($infos['tx_price']),
                "le prix doit être positif"                     => floatval($infos['tx_price']) < 0,
                "montant des frais incorrect"                   => !is_numeric($infos['tx_fee_amount']),
                "le montant des frais ne doit pas être négatif" => floatval($infos['tx_fee_amount']) < 0,
                "type de frais inconnu"                         => !in_array($infos['tx_fee_type'], array('fixed_currency', 'fixed_index', 'percent_currency', 'percent_index')),
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
                "tx_type"       => 'buy',
                "tx_pair_id"    => $tx_pair->infos['id'],
                "tx_price"      => $infos['tx_price'],
                "tx_amount"     => $infos['tx_amount'],
                "tx_fee_amount" => $infos['tx_fee_amount'],
                "tx_fee_type"   => $infos['tx_fee_type'],
                "tx_timestamp"  => DateTime::createFromFormat('Y-m-d H:i', $infos['tx_date'] . ' ' . $infos['tx_hour'])->getTimestamp()
            ));
            return $new_tx ? array(true) : array(false, 'erreur');
            break;

            // ERROR
            default:
            return array(false, 'type de transaction inconnu');
            break;
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