<?php

/**
* OHLC DATA CLASS
*
* 1522033200
* 1522033200
*
*/
class OHLC_API
{
    static private $API_allowance = 0;
    static private $logs_url = false;
    static private $logs = false;
    static private $insert_count = 0;

    // UPDATE OHLC DATA
    static public function APIUpdateOHLC()
    {
        $ch = curl_init();
        self::$insert_count  = 0;
        foreach (Pair::FullList() as $pair) {
            if (self::APIParameters($pair) !== false) {
                curl_setopt_array($ch, array(
                    CURLOPT_URL            => $pair->infos['api_url'] . '?periods=1800' . self::APIParameters($pair),
                    CURLOPT_RETURNTRANSFER => true
                ));
                $req = curl_exec($ch);

                // No more API data allowance = exit
                if (self::CheckRequestStatus($ch)[1] == true) {
                    self::$API_allowance = 0;
                    break;

                    // Save data
                } elseif (self::CheckRequestStatus($ch)[0]) {
                    $API_resp            = json_decode($req, true);
                    $pair_insert_count   = 0;
                    self::$API_allowance = $API_resp['allowance']['remaining'];

                    if (isset($API_resp['result'][1800]) && !empty($API_resp['result'][1800])) {
                        // foreach ($API_resp['result'][1800] as $candlestick) {
                        for ($i = 0; $i < (count($API_resp['result'][1800]) - 1); $i += 1) {
                            $candlestick = $API_resp['result'][1800][$i];

                            // Don't insert duplicate data
                            $check_dup = App::$db->prepare("SELECT COUNT(*) FROM ohlc WHERE ohlc_pair_id = :pair_id AND ohlc_timestamp = :stamp");
                            $check_dup->execute(array(
                                "pair_id" => $pair->infos['id'],
                                "stamp"   => $candlestick[0]
                            ));

                            if ($check_dup->fetch(PDO::FETCH_ASSOC)['COUNT(*)'] == 0) {
                                $new_candlestick = App::$db->prepare("INSERT INTO ohlc (ohlc_pair_id, ohlc_timestamp, ohlc_open, ohlc_high, ohlc_low, ohlc_close, ohlc_volume) VALUES (:pair, :stamp, :open, :high, :low, :close, :volume)");
                                $new_candlestick->execute(array(
                                    "pair"   => $pair->infos['id'],
                                    "stamp"  => $candlestick[0],
                                    "open"   => $candlestick[1],
                                    "high"   => $candlestick[2],
                                    "low"    => $candlestick[3],
                                    "close"  => $candlestick[4],
                                    "volume" => $candlestick[5]
                                ));
                                $pair_insert_count = $new_candlestick ? $pair_insert_count + 1 : $pair_insert_count;
                                self::$insert_count = $new_candlestick ? self::$insert_count + 1 : self::$insert_count;
                            }
                        }
                    }
                    if (self::$insert_count > 0) {
                        self::AddLog($pair->symbol . ' - ' . $pair_insert_count . ' chandelles enregistrées');
                    }
                }
            }
        }
        curl_close($ch);

        // Recursive call
        if (OHLC_FETCH_RECURSIVE === true && self::$API_allowance > 0) {
            if (self::$insert_count == 0) {
                self::AddLog('Aucune donnée insérée');
                self::AddLog('---------- Màj terminée');
                self::SaveLogs();
            } else {
                self::AddLog('----- Appel récursif');
                self::APIUpdateOHLC();
            }
        } else {
            self::AddLog('---------- Màj terminée');
            self::SaveLogs();
        }
    }

    // SET THE LOGS FILE URL
    static public function DefineLogsUrl()
    {
        if (OHLC_LOGS) {
            if (OHLC_LOGS_DAILY) {
                $filename = 'ohlc_logs_' . (new DateTime())->format('Y_m_d') . '.txt';
            } else {
                $filename = 'ohlc_logs_' . (new DateTime())->format('Y_m') . '.txt';
            }
            self::$logs_url = __DIR__ . '/../doc/' . $filename;
            self::AddLog("\n" . '---------- ' . (new DateTime())->format('Y-m-d H:i:s') . "\n");
        }
    }

    // ADD A LINE IN THE LOGS
    static private function AddLog($log)
    {
        if (OHLC_LOGS) {
            self::$logs .= $log . "\r\n";
        }
    }

    // SAVE LOGS
    static private function SaveLogs()
    {
        if (OHLC_LOGS && self::$logs_url !== false) {
            file_put_contents(self::$logs_url, self::$logs, FILE_APPEND);
        }
    }

    // GET PAIR PARAMETERS FOR API
    static private function APIParameters(Pair $pair)
    {
        // No data -> get recent data
        if ($pair->last_update == false) {
            return '&before=' . time();
        }

        // Up to date
        if ($pair->last_update > (time() - (60 * 60))) {
            // false -> Nothing
            if (OHLC_FETCH_PREVIOUS === false) {
                self::AddLog($pair->symbol . ' déjà à jour');
                return false;
            }

            // true -> get previous unfetched data
            return '&before=' . $pair->first_update;
        }

        // -> Update data
        return '&after=' . $pair->last_update;
    }

    // CHECK API CALL STATUS
    /**
    *   @return array(save_data, exit_loop)
    */
    static private function CheckRequestStatus($curl)
    {
        // cURL error
        if (curl_errno($curl) != 0) {
            self::AddLog('cURL - erreur n°' . curl_errno($curl));
            return array(false, false);
        }

        // API allowance == 0
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 429) {
            self::AddLog('API - Forfait de requêtes épuisé');
            return array(false, true);
        }

        return array(true, false);
    }
}


?>