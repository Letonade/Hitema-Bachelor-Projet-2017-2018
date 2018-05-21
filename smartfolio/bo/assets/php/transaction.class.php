<?php

/**
* (TX) TRANSACTION CLASS
*/
class Transaction
{
    // Traits
    use HTML_Transaction;
    // Font Awesome 5 icons
    public const ICONS = [
        'deposit'  => 'upload',
        'buy'      => 'plus',
        'transfer' => 'exchange-alt',
        'sell'     => 'minus',
        'withdraw' => 'download'
    ];
    public $infos;
    private $pair          = false;
    private $fees          = false;
    private $cost          = false;
    private $exchange_from = false;
    private $exchange_to   = false;
    private $currency      = false;
    private $index         = false;
    private $value         = false;
    private $delta         = false;

    function __construct(array $infos)
    {
        $this->infos = $infos;
    }

    // BUY / SELL : PAIR
    public function Pair() : Pair
    {
        if ($this->pair === false) {
            $this->pair = new Pair($this->infos['tx_pair_id']);
        }
        return $this->pair;
    }

    // DEPOSIT / TRANSFER : EXCHANGE FROM
    private function ExchangeFrom() : Exchange
    {
        if ($this->exchange_from === false) {
            $this->exchange_from = new Exchange($this->infos['tx_transfer_exchange_id_from']);
        }
        return $this->exchange_from;
    }

    // DEPOSIT / TRANSFER : EXCHANGE TO
    private function ExchangeTo() : Exchange
    {
        if ($this->exchange_to === false) {
            $this->exchange_to = new Exchange($this->infos['tx_transfer_exchange_id_to']);
        }
        return $this->exchange_to;
    }

    // ALL : CURRENCY
    private function Currency() : Currency
    {
        if ($this->currency === false) {
            if (in_array($this->infos['tx_type'], ['buy', 'sell'])) {
                $this->currency = $this->Pair()->currency;
            } else {
                $this->currency = new Currency($this->infos['tx_transfer_curr_id']);
            }
        }
        return $this->currency;
    }

    // BUY / SELL : INDEX
    private function Index() : Currency
    {
        if ($this->index === false) {
            $this->index = $this->Pair()->index;
        }
        return $this->index;
    }

    // CALCULATE COST OF TRANSACTION
    private function Cost() : array
    {
        if ($this->cost === false) {
            switch ($this->infos['tx_type']) {
                case 'buy':
                case 'sell':
                $this->cost = [
                    'currency' => $this->Pair()->index->infos['id'],
                    'amount'   => floatval($this->infos['tx_amount'] * $this->infos['tx_price'])
                ];
                break;

                default:
                $this->cost = [
                    'currency' => $this->Currency()->infos['id'],
                    'amount'   => 0.0
                ];
                break;
            }
        }
        return $this->cost;
    }

    // CALCULATE FEES OF TRANSACTION
    private function Fees() : array
    {
        if ($this->fees === false) {
            switch ($this->infos['tx_type']) {
                // BUY / SELL
                case 'buy':
                case 'sell':
                switch ($this->infos['tx_fee_type']) {
                    case 'fixed_currency':
                    $this->fees = [
                        'currency' => $this->Pair()->currency->infos['id'],
                        'amount'   => $this->infos['tx_fee_amount']
                    ];
                    break;

                    case 'fixed_index':
                    $this->fees = [
                        'currency' => $this->Pair()->index->infos['id'],
                        'amount'   => $this->infos['tx_fee_amount']
                    ];
                    break;

                    case 'percent_currency':
                    $this->fees = [
                        'currency' => $this->Pair()->currency->infos['id'],
                        'amount'   => floatval(floatval($this->infos['tx_amount'] / 100) * $this->infos['tx_fee_amount'])
                    ];
                    break;

                    case 'percent_index':
                    $this->fees = [
                        'currency' => $this->Pair()->index->infos['id'],
                        'amount'   => floatval(floatval($this->Cost()['amount'] / 100) * $this->infos['tx_fee_amount'])
                    ];
                    break;

                }
                break;


                case 'transfer':
                case 'withdraw':
                switch ($this->infos['tx_fee_type']) {
                    case 'fixed_currency':
                    $this->fees = [
                        'currency' => $this->Currency()->infos['id'],
                        'amount'   => $this->infos['tx_fee_amount']
                    ];
                    break;

                    case 'percent_currency':
                    $this->fees = [
                        'currency' => $this->Currency()->infos['id'],
                        'amount'   => floatval(($this->infos['tx_amount'] / 100) * $this->infos['tx_fee_amount'])
                    ];
                    break;

                }
                break;

                // NO FEES
                default:
                $this->fees = [
                    'currency' => $this->Currency()->infos['id'],
                    'amount'   => 0
                ];
                break;
            }
        }
        return $this->fees;
    }

    // CALCULATE ACTUAL VALUE
    public function GetValue()
    {
        if ($this->value === false) {
            $req = json_decode(file_get_contents($this->Pair()->infos['api_url'] . '?periods=60&after' . (time() - 120)), true);
            $price = end($req['result'][60])[4];
            $this->value = floatval($price * $this->infos['tx_amount']);
        }
        return $this->value;
    }

    // CALCULATE ROI
    public function GetDelta()
    {
        if ($this->delta === false) {
            $this->delta = floatval((($this->GetValue() - $this->Cost()['amount']) / $this->Cost()['amount']) * 100);
        }
        return $this->delta;
    }

    // INVESTMENT BALANCE UPDATE
    public function UpdateBalance(Currency $curr, float $balance) : float
    {
        switch ($this->infos['tx_type']) {
            // DEPOSIT
            case 'deposit':
            return $this->infos['tx_amount'];
            break;

            // BUY
            case 'buy':
            // is the currency
            if ($this->Pair()->currency->infos['id'] == $curr->infos['id']) {
                if ($this->Fees()['currency'] == $curr->infos['id']) {
                    return floatval($this->infos['tx_amount'] - $this->Fees()['amount']);
                } else {
                    return $this->infos['tx_amount'];
                }
                // is the index
            } else {
                $cost = floatval($this->Cost()['amount'] * -1);
                return abs($cost) > $balance ? 0 : $cost;
            }
            break;

            // TRANSFER
            case 'transfer':
            return floatval($this->Fees()['amount'] * -1);
            break;

            // SELL
            case 'sell':
            // is the currency
            if ($this->Pair()->currency->infos['id'] == $curr->infos['id']) {
                $cost = floatval($this->infos['tx_amount'] * -1);
                return abs($cost) > $balance ? 0 : $cost;
                // is the index
            } else {
                if ($this->Fees()['currency'] == $curr->infos['id']) {
                    return floatval($this->Cost()['amount'] - $this->Fees()['amount']);
                } else {
                    return $this->Cost()['amount'];
                }
            }
            break;

            // TRANSFER
            case 'withdraw':
            return floatval(($this->infos['tx_amount'] - $this->Fees()['amount']) * -1);
            break;
        }
        return 0;
    }

    // BUILD HTML SUM UP
    public function SumUp()
    {
        $sumup = '<div class="tx ' . $this->infos['tx_type'] . '">';
        $sumup .= '<p><i class="fas fa-' . self::ICONS[$this->infos['tx_type']] . '"></i> ';
        switch ($this->infos['tx_type']) {
            case 'deposit':
            $sumup .= 'Dépôt</p>';
            $sumup .= $this->HTML_SumUp_ExchangeTo();
            $sumup .= $this->HTML_SumUp_Amount();
            break;

            case 'buy':
            $sumup .= 'Achat</p>';
            $sumup .= $this->HTML_SumUp_Pair();
            $sumup .= $this->HTML_SumUp_Amount();
            $sumup .= $this->HTML_SumUp_Price();
            $sumup .= $this->HTML_SumUp_Cost();
            $sumup .= $this->HTML_SumUp_Fees();
            $sumup .= $this->HTML_SumUp_Value();
            $sumup .= $this->HTML_SumUp_ROI();
            break;

            case 'transfer':
            $sumup .= 'Transfert</p>';
            $sumup .= $this->HTML_SumUp_ExchangeFrom();
            $sumup .= $this->HTML_SumUp_Amount();
            $sumup .= $this->HTML_SumUp_ExchangeTo();
            $sumup .= $this->HTML_SumUp_Fees();
            break;

            case 'sell':
            $sumup .= 'Vente</p>';
            $sumup .= $this->HTML_SumUp_Pair();
            $sumup .= $this->HTML_SumUp_Amount();
            $sumup .= $this->HTML_SumUp_Price();
            $sumup .= $this->HTML_SumUp_Cost();
            $sumup .= $this->HTML_SumUp_Fees();
            break;

            case 'withdraw':
            $sumup .= 'Retrait</p>';
            $sumup .= $this->HTML_SumUp_ExchangeFrom();
            $sumup .= $this->HTML_SumUp_Amount();
            $sumup .= $this->HTML_SumUp_Fees();
            break;
        }
        $sumup .= '<h6>' . (new DateTime())->setTimestamp($this->infos['tx_timestamp'])->format('d/m/Y H:i') . '</h6>';
        $sumup .= '</div>';
        return $sumup;
    }

    // AUTO FORMAT DECIMAL NUMBER
    static public function AutoFloatFormat(float $number)
    {
        if (strpos($number, '.') !== false) {
            $fpn = strlen(substr($number, strpos($number, ".") + 1));
            return number_format($number, $fpn, '.', ' ');
        }
        return number_format($number, 0, '.', ' ');
    }
}


?>