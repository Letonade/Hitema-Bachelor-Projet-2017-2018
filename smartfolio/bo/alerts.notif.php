
<?php
include 'assets/inc/init.php';
// GET PORTFOLIO
try {
    $portfolio = new Portfolio($_GET['port']);
} catch (\Exception $e) {
    App::Respond('Portefeuille', $e->getMessage(), true);
}

$alertslist = $portfolio->GetAlerts($portfolio->infos['id']);
foreach ($alertslist as $key => $value) {
echo "<"; var_dump($key); echo ">";
    foreach ($value as $keya => $valuea) {
        echo "<".$keya.">";
        if (is_array($valuea)) {foreach ($valuea as $keyb => $valueb) {echo "<".$keyb.">".$valueb."</".$keyb.">";}}
        else echo ("<".$valuea.">");
        echo "</".$keya.">";
    }
}
?>
