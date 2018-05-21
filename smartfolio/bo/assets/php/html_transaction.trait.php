<?php

/**
* (TX) TRANSACTION HTML TRAIT
*/
trait HTML_Transaction
{

    // BASIC .tx_part BLOCK
    private function HTML_TxPart(string $h4, string $p, bool $delta = null) : string
    {
        $sumup = '<div class="tx_part">';
        $sumup .= ' <h4>' . $h4 . ':</h4>';
        $sumup .= ' <p class="' . (is_null($delta) ? '' : ($delta ? 'delta_up' : 'delta_down')) . '">' . $p . '</p>';
        $sumup .= '</div>';
        return $sumup;
    }

    // EXCHANGE FROM
    private function HTML_SumUp_ExchangeFrom() : string
    {
        return $this->HTML_TxPart(
            'Depuis l\'exchange',
            $this->ExchangeFrom()->infos['name']
        );
    }

    // EXCHANGE TO
    private function HTML_SumUp_ExchangeTo() : string
    {
        return $this->HTML_TxPart(
            'Vers l\'exchange',
            $this->ExchangeTo()->infos['name']
        );
    }

    // AMOUNT
    private function HTML_SumUp_Amount() : string
    {
        return $this->HTML_TxPart(
            'Montant',
            self::AutoFloatFormat($this->infos['tx_amount']) . ' ' . $this->Currency()->infos['symbol']
        );
    }

    // BUY / SELL : COST
    private function HTML_SumUp_Cost() : string
    {
        return $this->HTML_TxPart(
            ($this->infos['tx_type'] == 'buy' ? 'Coût' : 'Gain'),
            self::AutoFloatFormat($this->Cost()['amount']) . ' ' . (new Currency($this->Cost()['currency']))->infos['symbol']
        );
    }

    // BUY / SELL : PRICE
    private function HTML_SumUp_Price() : string
    {
        return $this->HTML_TxPart(
            'Prix ' . ($this->infos['tx_type'] == 'buy' ? 'd\'achat' : 'de vente'),
            self::AutoFloatFormat($this->infos['tx_price']) . ' ' . $this->Index()->infos['symbol']
        );
    }

    // PAIR
    private function HTML_SumUp_Pair() : string
    {
        return $this->HTML_TxPart(
            'Paire',
            $this->Pair()->symbol
        );
    }

    // FEES
    private function HTML_SumUp_Fees() : string
    {
        return $this->HTML_TxPart(
            'Frais',
            self::AutoFloatFormat($this->Fees()['amount']) . ' ' . (new Currency($this->Fees()['currency']))->infos['symbol']
        );
    }

    // BUY : VALUE
    private function HTML_SumUp_Value() : string
    {
        return $this->HTML_TxPart(
            'Valeur',
            self::AutoFloatFormat($this->GetValue()) . ' ' . $this->Index()->infos['symbol']
        );
    }

    // BUY : ROI
    private function HTML_SumUp_ROI() : string
    {
        return $this->HTML_TxPart(
            'ROI',
            number_format($this->GetDelta(), 2, '.', ' ') . ' %',
            ($this->GetDelta() >= 0)
        );
    }
}


?>