<?php

/**
* (TX) TRANSACTION HTML TRAIT
*/
trait HTML_Alert
{
    // BASIC .alerts_part BLOCK
    private function HTML_alertPart(string $h4, string $p, bool $delta = null) : string
    {   
        $sumup = '<div>';
        $sumup .= ' <h4>' . $h4 . ':</h4>';
        $sumup .= ' <p>' . $p . '</p>';
        $sumup .= ' <form action="investment.php?'.$_SERVER['QUERY_STRING'].'" method="post">';
        $sumup .= ' <input type="hidden" name="token" value="'.$_SESSION['user']['session_token'].'">';
        $sumup .= ' <input type="hidden" name="alert_id_suppr" value="'.$this->infos['alerts_id'].'">';
        $sumup .= ' <input type="submit" name="delete_alerts" value="Supprimer">';
        $sumup .= '</form>';
        $sumup .= '</div>';
        return $sumup;
    }

    // description de l'alert
    private function HTML_SumUp_Trigger() : string
    {
        if ($this->infos['alerts_type'] == 'fixe') {
            return $this->HTML_alertPart(
                'Alerte n°'.$this->infos['alerts_id'], "Actif si la courbe est ".
                $this->infos['alerts_compare']." ".
                number_format($this->infos['alerts_value'], 2, '.', ' ')
            );
        }else if ($this->infos['alerts_type'] == 'marge') {
            return $this->HTML_alertPart(
                'Alerte n°'.$this->infos['alerts_id'], "Actif si la marge est ".
                $this->infos['alerts_compare']." ".
                number_format($this->infos['alerts_value'], 1, '.', ' ')."%"
            );
        }else{throw new Exception("type d'alerte incorrect", 1);
        }
    }
}


?>