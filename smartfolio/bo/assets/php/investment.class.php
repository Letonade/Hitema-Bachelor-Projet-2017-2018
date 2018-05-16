<?php

/**
* INVESTMENT CLASS
*/
class Investment
{
    static private $investments = [];
    public $portfolio;
    public $currency;
    public $type;
    private $tx_history = [];
    private $balance    = 0;
    private $last_pair  = false;

    private function __construct(int $portfolio, int $currency, string $type)
    {
        // Check investment type
        if (!in_array($type, ['investment', 'accumulator'])) {
            throw new \Exception('type d\'investissement incorrect [' . $type . ']', 1);
        }
        // Save currency
        try {
            $this->portfolio = new Portfolio($portfolio);
            $this->currency  = new Currency($currency);
            $this->type = $type;
        } catch (\Exception $e) {
            App::Respond('Investissements', 'Monnaies [' . $currency . '] - ' . $e->getMessage());
        }
    }

    // GET THE TRANSACTION HISTORY
    public function GetTxHistory()
    {
        if (empty($this->tx_history)) {
            $tx_list = App::$db->prepare(
                'SELECT
                    transaction.*,
                    pair.*,
                    COALESCE(pair.pair_curr_a, pair.pair_curr_b, transaction.tx_transfer_curr_id) investment_currency
                FROM transaction
                    LEFT JOIN pair
                        ON transaction.tx_pair_id = pair.pair_id
                WHERE
                    transaction.tx_port_id = :port_id
                    AND :curr_id IN(pair.pair_curr_a, pair.pair_curr_b, transaction.tx_transfer_curr_id)
                ORDER BY transaction.tx_timestamp
                ');
                $tx_list->execute([
                    'port_id' => $this->portfolio->infos['id'],
                    'curr_id' => $this->currency->infos['id']
                ]);
                foreach ($tx_list->fetchAll(PDO::FETCH_ASSOC) as $tx) {
                    $this->AddTx($tx);
                }
            }
            return $this->tx_history;
        }

        // ADD NEW TRANSACTION AND UPDATE THE BALANCE
        private function AddTx($tx)
        {
            switch ($tx['tx_type']) {
                case 'deposit':
                $exchange = new Exchange($tx['tx_transfer_exchange_id_to']);
                $this->tx_history[] = array(
                    'type'     => 'deposit',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => null,
                    'pair'     => null,
                    'cost'     => null,
                    'fees'     => null,
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i'),
                    'sumup'    => floatval($tx['tx_amount']) . ' ' . $this->currency->infos['symbol'] . ' @' . $exchange->infos['name']
                );
                $this->balance += $tx['tx_amount'];
                break;

                case 'buy':
                $costs = $this->CalculateCosts($tx);
                $this->tx_history[] = array(
                    'type'     => $costs['pair']->currency->infos['id'] == $this->currency->infos['id'] ? 'buy' : 'sell',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => floatval($tx['tx_price']) . ' ' . $costs['pair']->index->infos['symbol'],
                    'pair'     => $costs['pair']->symbol,
                    'cost'     => $costs['cost'] . ' ' . $costs['pair']->index->infos['symbol'],
                    'fees'     => $costs['fees'],
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i'),
                    'sumup'    => floatval($tx['tx_amount']) . ' ' . $costs['pair']->currency->infos['symbol'] . ' @' . $costs['pair']->exchange->infos['name']
                );
                if ($costs['pair']->currency->infos['id'] == $this->currency->infos['id']) {
                    $this->balance += $costs['amount'];
                } else {
                    $this->balance -= $costs['cost'];
                }
                $this->last_pair = $costs['pair']->infos['id'];
                break;

                case 'transfer':
                $costs              = $this->CalculateCosts($tx);
                $exchange_from      = new Exchange($tx['tx_transfer_exchange_id_from']);
                $exchange_to        = new Exchange($tx['tx_transfer_exchange_id_to']);
                $this->tx_history[] = array(
                    'type'     => 'transfer',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => null,
                    'pair'     => null,
                    'cost'     => null,
                    'fees'     => $costs['fees'],
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i'),
                    'sumup'    => floatval($tx['tx_amount']) . ' ' . $costs['currency']->infos['symbol'] . ' @' . $exchange_from->infos['name'] . ' -> ' . $exchange_to->infos['name']
                );
                if ($costs['pair']->currency->infos['id'] == $this->currency->infos['id']) {
                    $this->balance -= $costs['amount'];
                }
                break;

                case 'sell':
                $costs = $this->CalculateCosts($tx);
                $this->tx_history[] = array(
                    'type'     => $costs['pair']->currency->infos['id'] == $this->currency->infos['id'] ? 'sell' : 'buy',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => floatval($tx['tx_price']) . ' ' . $costs['pair']->index->infos['symbol'],
                    'pair'     => $costs['pair']->symbol,
                    'cost'     => $costs['cost'] . ' ' . $costs['pair']->index->infos['symbol'],
                    'fees'     => $costs['fees'],
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i'),
                    'sumup'    => floatval($tx['tx_amount']) . ' ' . $costs['pair']->currency->infos['symbol'] . ' @' . $costs['pair']->exchange->infos['name']
                );
                if ($costs['pair']->currency->infos['id'] == $this->currency->infos['id']) {
                    $this->balance -= $costs['amount'];
                } else {
                    if (in_array($tx['tx_fee_type'], ['fixed_index', 'percent_index'])) {
                        $this->balance += floatval($costs['cost'] - $costs['fees_amount']);
                    } else {
                        $this->balance += $costs['cost'];
                    }
                }
                $this->last_pair = $costs['pair']->infos['id'];
                break;

                case 'withdraw':
                $costs              = $this->CalculateCosts($tx);
                $exchange_from      = new Exchange($tx['tx_transfer_exchange_id_from']);
                $this->tx_history[] = array(
                    'type'     => 'withdraw',
                    'amount'   => floatval($tx['tx_amount']),
                    'price'    => null,
                    'pair'     => null,
                    'cost'     => null,
                    'fees'     => $costs['fees'],
                    'datetime' => (new DateTime())->setTimestamp($tx['tx_timestamp'])->format('d/m/Y H:i'),
                    'sumup'    => floatval($tx['tx_amount']) . ' ' . $costs['currency']->infos['symbol'] . ' @' . $exchange_from->infos['name']
                );
                if ($costs['pair']->currency->infos['id'] == $this->currency->infos['id']) {
                    $this->balance -= $costs['amount'];
                }
                break;
            }
        }

        // CALCULATE REAL COSTS, AMOUNT AND FEES
        private function CalculateCosts($tx)
        {
            switch ($tx['tx_type']) {
                case 'buy':
                $pair = new Pair($tx['tx_pair_id']);
                $cost = floatval($tx['tx_amount'] * $tx['tx_price']);
                $real_amount = $tx['tx_amount'];
                switch ($tx['tx_fee_type']) {
                    case 'fixed_currency':
                    $real_amount -= $tx['tx_fee_amount'];
                    $fees_amount = $tx['tx_fee_amount'];
                    $fees        = $tx['tx_fee_amount'] . ' ' . $pair->currency->infos['symbol'];
                    break;

                    case 'fixed_index':
                    $cost += $tx['tx_fee_amount'];
                    $fees_amount = $tx['tx_fee_amount'];
                    $fees        = $tx['tx_fee_amount'] . ' ' . $pair->index->infos['symbol'];
                    break;

                    case 'percent_currency':
                    $real_amount -= floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']);
                    $fees_amount = floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']);
                    $fees        = floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']) . ' ' . $pair->currency->infos['symbol'];
                    break;

                    case 'percent_index':
                    $cost += floatval(floatval($cost / 100) * $tx['tx_fee_amount']);
                    $fees_amount = floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']);
                    $fees        = floatval(floatval($cost / 100) * $tx['tx_fee_amount']) . ' ' . $pair->index->infos['symbol'];
                    break;
                }
                return [
                    'pair'        => $pair,
                    'cost'        => $cost,
                    'fees_amount' => $fees_amount,
                    'fees'        => $fees,
                    'amount'      => $real_amount
                ];
                break;

                case 'transfer':
                case 'withdraw':
                $curr = new Currency($tx['tx_transfer_curr_id']);
                $real_amount = $tx['tx_amount'];
                switch ($tx['tx_fee_type']) {
                    case 'fixed_currency':
                    $real_amount -= $tx['tx_fee_amount'];
                    $fees = $tx['tx_fee_amount'] . ' ' . $curr->infos['symbol'];
                    break;

                    case 'percent_currency':
                    $real_amount -= floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']);
                    $fees        = floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']) . ' ' . $curr->infos['symbol'];
                    break;
                }
                return [
                    'currency' => $curr,
                    'fees'     => $fees,
                    'amount'   => $real_amount
                ];
                break;

                case 'sell':
                $pair = new Pair($tx['tx_pair_id']);
                $cost = floatval($tx['tx_amount'] * $tx['tx_price']);
                $real_amount = $tx['tx_amount'];
                switch ($tx['tx_fee_type']) {
                    case 'fixed_currency':
                    $real_amount -= $tx['tx_fee_amount'];
                    $fees = $tx['tx_fee_amount'] . ' ' . $pair->currency->infos['symbol'];
                    break;

                    case 'fixed_index':
                    $cost += $tx['tx_fee_amount'];
                    $fees = $tx['tx_fee_amount'] . ' ' . $pair->index->infos['symbol'];
                    break;

                    case 'percent_currency':
                    $real_amount -= floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']);
                    $fees = floatval(floatval($real_amount / 100) * $tx['tx_fee_amount']) . ' ' . $pair->currency->infos['symbol'];
                    break;

                    case 'percent_index':
                    $cost += floatval(floatval($cost / 100) * $tx['tx_fee_amount']);
                    $fees = floatval(floatval($cost / 100) * $tx['tx_fee_amount']) . ' ' . $pair->index->infos['symbol'];
                    break;
                }
                return [
                    'pair'   => $pair,
                    'cost'   => $cost,
                    'fees'   => $fees,
                    'amount' => $real_amount
                ];
                break;
            }
        }

        // GET INVESTMENT BALANCE
        public function GetBalance()
        {
            if (empty($this->tx_history)) {
                $this->GetTxHistory();
            }
            return $this->balance;
        }

        // SET A DEFAULT PAIR FOR CHART
        public function DefaultPair()
        {
            if ($this->last_pair === false) {
                $default_pair = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_curr_a = :curr_id LIMIT 0, 1");
                $default_pair->execute([
                    'curr_id' => $this->currency->infos['id']
                ]);
                if ($default_pair->rowCount() == 0) {
                    $default_pair = App::$db->prepare("SELECT pair_id FROM pair WHERE pair_curr_b = :curr_id LIMIT 0, 1");
                    $default_pair->execute([
                        'curr_id' => $this->currency->infos['id']
                    ]);
                }
                $this->last_pair = $default_pair->fetch(PDO::FETCH_ASSOC)['pair_id'];
            }
            return $this->last_pair;
        }

        // GET THE CURRENCIES & THEIR TYPE OF A PORTFOLIO
        static public function GetInvestments(int $portfolio_id)
        {
            if (empty(self::$investments)) {
                // Get accumulators with no transaction
                $empty_accumulators = App::$db->prepare(
                    'SELECT
                    acc_curr_id AS investment_currency,
                    "accumulator" AS investment_type
                    FROM port_accumulator
                    WHERE
                    acc_port_id = :port_id
                    AND (acc_curr_id, acc_curr_id, acc_curr_id) NOT IN
                    (
                        SELECT
                        pair.pair_curr_a, pair.pair_curr_b, currency.curr_id
                        FROM transaction
                        LEFT JOIN currency
                        ON transaction.tx_transfer_curr_id = currency.curr_id
                        LEFT JOIN pair
                        ON transaction.tx_pair_id = pair.pair_id
                        WHERE tx_port_id = :port_id
                    )
                    ');
                    $empty_accumulators->execute([
                        'port_id' => $portfolio_id
                    ]);

                    // Get by transactions
                    $inv_list = App::$db->prepare(
                        'SELECT
                        IF(port_accumulator.acc_id IS NULL, "investment", "accumulator") AS investment_type,
                        COALESCE(IF(transaction.tx_type = "buy", pair.pair_curr_a, IF(transaction.tx_type = "sell", pair.pair_curr_b, NULL)), currency.curr_id) AS investment_currency
                        FROM transaction
                        LEFT JOIN currency
                        ON transaction.tx_transfer_curr_id = currency.curr_id
                        LEFT JOIN pair
                        ON transaction.tx_pair_id = pair.pair_id
                        LEFT JOIN port_accumulator
                        ON port_accumulator.acc_port_id = :port_id
                        AND port_accumulator.acc_curr_id = COALESCE(IF(transaction.tx_type = "buy", pair.pair_curr_a, IF(transaction.tx_type = "sell", pair.pair_curr_b, NULL)), currency.curr_id)
                        WHERE tx_port_id = :port_id
                        GROUP BY investment_currency
                        ORDER BY investment_type = "accumulator"
                        ');
                        $inv_list->execute([
                            'port_id' => $portfolio_id
                        ]);
                        foreach (array_merge($inv_list->fetchAll(PDO::FETCH_ASSOC), $empty_accumulators->fetchAll(PDO::FETCH_ASSOC)) as $inv) {
                            try {
                                $inv = new self($portfolio_id, $inv['investment_currency'], $inv['investment_type']);
                                self::$investments[] = $inv;
                            } catch (\Exception $e) {
                                App::Respond('Investissements', $e->getMessage());
                            }
                        }
                    }
                    return self::$investments;
                }

                static public function Investment(int $portfolio_id, int $currency_id)
                {
                    $is_accumulator = App::$db->prepare("SELECT COUNT(*) FROM port_accumulator WHERE acc_port_id = :port_id AND acc_curr_id = :curr_id");
                    $is_accumulator->execute([
                        'port_id' => $portfolio_id,
                        'curr_id' => $currency_id
                    ]);
                    $investment_type = $is_accumulator->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0 ? 'investment' : 'accumulator';
                    return new self($portfolio_id, $currency_id, $investment_type);
                }
            }


            ?>