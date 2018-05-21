<?php

/**
* INVESTMENT CLASS
*/
class Investment
{
    use HTML_Investment;
    private static $investments = [];
    public $portfolio;
    public $currency;
    public $type;
    private $tx_history   = [];
    private $balance      = 0;
    private $last_pair    = false;
    private $avg_delta    = false;
    private $delta        = [];
    private $temp_balance = [];
    private $delta_arr    = [];

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
                ORDER BY transaction.tx_timestamp'
            );
            $tx_list->execute([
                'port_id' => $this->portfolio->infos['id'],
                'curr_id' => $this->currency->infos['id']
            ]);
            foreach ($tx_list->fetchAll(PDO::FETCH_ASSOC) as $tx) {
                $tx = new Transaction($tx);
                $this->tx_history[] = $tx;
                $this->balance += $tx->UpdateBalance($this->currency, $this->balance);
            }
        }
        return $this->tx_history;
    }

    // GET INVESTMENT BALANCE
    public function GetBalance()
    {
        if (empty($this->tx_history)) {
            $this->GetTxHistory();
        }
        return $this->balance;
    }

    /**
    *   DEDUCT FROM TEMPORARY BALANCE & SAVE DELTA
    *   Recursive function
    */
    public function DeductForDelta(Currency $accumulator, Transaction $sell, float $deducted = 0) : void
    {
        $acc = $accumulator->infos['id'];
        if (isset($this->temp_balance[$acc][0])) {
            $buy = $this->temp_balance[$acc][0];
            $deduct = floatval($sell->infos['tx_amount'] - $deducted);
            // Get price in index currency
            $index = $sell->Pair()->index;
            if ($index->infos['id'] == $acc) {
                $price = $sell->infos['tx_price'];
            } else {
                $url = 'https://min-api.cryptocompare.com/data/pricehistorical';
                $url .= '?fsym=' . $index->infos['symbol'];
                $url .= '&tsyms=' . $accumulator->infos['symbol'];
                $url .= '&ts=' . $sell->infos['tx_timestamp'];
                $unit_price = json_decode(file_get_contents($url), true)[$index->infos['symbol']][$accumulator->infos['symbol']];
                $price = $unit_price * $sell->infos['tx_price'];
            }

            if ($deduct >= $buy['amount']) {
                $this->delta_arr[$acc][] = [
                    'amount' => $buy['amount'],
                    'delta'  => floatval((($price / $buy['price']) * 100) - 100)
                ];
                $this->temp_balance[$acc] = array_shift($this->temp_balance[$acc]);
                $upd_deducted = $deducted + $buy['amount'];
                if (($sell->infos['tx_amount'] - $upd_deducted) > 0) {
                    $this->DeductForDelta($sell, $upd_deducted);
                }
            } else {
                $this->temp_balance[$acc][0] = [
                    'amount'    => $sell->infos['tx_amount'],
                    'price'     => $this->temp_balance[$acc][0]['price'],
                    'index'     => $this->temp_balance[$acc][0]['index'],
                    'timestamp' => $this->temp_balance[$acc][0]['timestamp'],
                ];
                $this->delta_arr[$acc][] = [
                    'amount' => $sell->infos['tx_amount'],
                    'delta'  => floatval((($price / $buy['price']) * 100) - 100)
                ];
            }
        }
    }

    // GET AVERAGE DELTA
    public function GetDelta(Currency $accumulator)
    {
        $acc = $accumulator->infos['id'];
        if (!isset($this->delta[$acc])) {
            // Calculate deltas history
            $this->temp_balance[$acc] = [];
            $this->delta_arr[$acc]    = [];
            foreach ($this->GetTxHistory() as $tx) {
                // Add to the temporary balance
                if ($tx->infos['tx_type'] == 'buy' && $tx->Pair()->currency->infos['id'] == $this->currency->infos['id']) {
                    // Get price in index currency
                    $index = $tx->Pair()->index;
                    if ($index->infos['id'] == $acc) {
                        $price = $tx->infos['tx_price'];
                    } else {
                        $url = 'https://min-api.cryptocompare.com/data/pricehistorical';
                        $url .= '?fsym=' . $index->infos['symbol'];
                        $url .= '&tsyms=' . $accumulator->infos['symbol'];
                        $url .= '&ts=' . $tx->infos['tx_timestamp'];
                        $unit_price = json_decode(file_get_contents($url), true)[$index->infos['symbol']][$accumulator->infos['symbol']];
                        $price = $unit_price * $tx->infos['tx_price'];
                    }
                    $this->temp_balance[$acc][] = [
                        'amount'        => $tx->infos['tx_amount'],
                        'price'         => $price
                    ];

                    // Deduct from balance and save delta
                } elseif (false && $tx->infos['tx_type'] == 'sell' && $tx->Pair()->currency->infos['id'] == $this->currency->infos['id']) {
                    $this->DeductForDelta($accumulator, $tx);
                }
            }

            // Calculate unsold delta
            foreach ($this->temp_balance[$acc] as $b) {
                $url = 'https://min-api.cryptocompare.com/data/price';
                $url .= '?fsym=' . $this->currency->infos['symbol'] . '&tsyms=' . $accumulator->infos['symbol'];
                $unit_price = json_decode(file_get_contents($url), true)[$accumulator->infos['symbol']];
                $delta      = floatval((($unit_price / $b['price']) * 100) - 100);

                $this->delta_arr[$acc][] = [
                    'amount' => $b['amount'],
                    'delta'  => $delta
                ];
            }

            // Calculate average delta
            $points = 0.0;
            $coef   = 0.0;
            foreach ($this->delta_arr[$acc] as $d) {
                $points += floatval($d['amount'] * $d['delta']);
                $coef += floatval($d['amount']);
            }
            $this->delta[$accumulator->infos['id']] = $coef == 0 ? '--' : number_format(floatval($points / $coef), 2, '.', ' ');
        }
        return $this->delta[$accumulator->infos['id']];
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

    // AVERAGE ROI
    public function AverageDelta()
    {
        if ($this->avg_delta === false) {
            $balance = $this->GetBalance();
            $active_tx = [];
            foreach (array_reverse($this->GetTxHistory()) as $tx) {
                if ($tx->infos['tx_type'] == 'buy' && $tx->Pair()->currency->infos['id'] == $this->currency->infos['id']) {
                    $active_tx[] = $tx;
                    $balance -= $tx->infos['tx_amount'];

                    if ($balance < 0) {
                        break 1;
                    }
                }
            }

            if (count($active_tx) == 0) {
                $this->avg_delta = 0;
            }

            $count = 0;
            $total = 0;
            foreach ($active_tx as $tx) {
                $count += $tx->infos['tx_amount'];
                $total += floatval($tx->infos['tx_amount'] * $tx->GetDelta());
            }
            $this->avg_delta = number_format(floatval($total / $count), 2, '.', ' ');
        }
        return $this->avg_delta;
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
                )'
            );
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
                ORDER BY investment_type = "accumulator"'
            );
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