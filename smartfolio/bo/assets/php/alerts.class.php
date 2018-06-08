<?php

/**
* (TX) TRANSACTION CLASS
*/
class Alert
{
    // Traits
    use HTML_Alert;
    // Font Awesome 5 icons
    public const ICONS = [
        'fixe'     => 'upload',
        'marge'    => 'exchange-alt'
    ];
    public $infos;

    function __construct(array $infos)
    {
        $this->infos = $infos;
    }

    // BUILD HTML SUM UP
    public function SumUp()
    {
        $sumup = "";
        $sumup .= '<div class="alerts ' . $this->infos['alerts_type'] . '">';
        $sumup .= '<p><i class="fas fa-' . self::ICONS[$this->infos['alerts_type']] . '"></i> ';
        $sumup .= 'Evènement d\'alerte</p>';
        $sumup .= $this->HTML_SumUp_Trigger();
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