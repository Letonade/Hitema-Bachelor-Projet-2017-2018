<?php

/**
* INVESTMENT HTML TRAIT
*/
trait HTML_Investment
{

    // INFOS - DELTA FOR EACH ACCUMULATORS
    public function HTML_AccumulatorDelta(Portfolio $portfolio) : string
    {
        $roi = '';
        foreach ($portfolio->GetAccumulators() as $acc) {
            if ($acc->currency->infos['id'] != $this->currency->infos['id']) {
                $delta = $this->GetDelta($acc->currency);
                $class = $delta == '--' ? '' : ($delta < 0 ? 'delta_down' : 'delta_up');
                $roi .= '<p>' . $acc->currency->infos['symbol'] . ':</p><p class="' . $class . '">' . $this->GetDelta($acc->currency) . '%</p>';
            }
        }
        return $roi;
    }

    // INFOS - GLOBAL
    public function HTML_GlobalData(Portfolio $portfolio) : string
    {
        $global = '';
        $api_url = 'https://min-api.cryptocompare.com/data/pricemultifull?fsyms=' . $this->currency->infos['symbol'] . '&tsyms=';
        foreach ($portfolio->GetAccumulators() as $acc) {
            $api_url .= $acc->currency->infos['symbol'] . ',';
        }
        $infos = json_decode(file_get_contents($api_url), true);
        // echo '<pre>' . print_r($infos, true) . '</pre>';

        foreach ($portfolio->GetAccumulators() as $acc) {
            if ($acc->currency->infos['id'] != $this->currency->infos['id']) {
                $marketcap = $infos['RAW'][$this->currency->infos['symbol']][$acc->currency->infos['symbol']]['MKTCAP'];
                $change = $infos['RAW'][$this->currency->infos['symbol']][$acc->currency->infos['symbol']]['CHANGEPCTDAY'];
                $chg_cls = $change >= 0 ? 'delta_up' : 'delta_down';

                $global .= '<p>MarketCap:</p>';
                $global .= '<p>' . number_format(intval($marketcap), 0, '.', ' ');
                $global .= ' ' . $acc->currency->infos['symbol'] . '</p>';
                $global .= '<p>24H change:</p>';
                $global .= '<p class="' . $chg_cls . '">' . number_format($change, 2, '.', ' ') . ' %</p>';
            }
        }
        return $global;
    }
}


?>