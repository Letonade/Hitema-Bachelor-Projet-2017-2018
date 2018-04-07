<?php

/**
* CUSTOMER CLASS
*/
class Customer
{
    public $infos;

    function __construct($id)
    {
        $infos = App::$db->prepare("SELECT * FROM customer WHERE cust_id = :id");
        $infos->execute(array(
            "id" => $id
        ));
        if ($infos->rowCount() == 0) {
            throw new \Exception("introuvable", 1);

        } else {
            $infos = $infos->fetch(PDO::FETCH_ASSOC);
            $this->infos = array(
                "id"        => $infos['cust_id'],
                "name"      => $infos['cust_name'],
                "company"   => $infos['cust_company'],
                "email"     => $infos['cust_email']
            );
        }
    }

    // EDIT CUSTOMER
    public function Edit($infos)
    {
        // Data validation
        $valid_email = User::ValidateEmail($infos['cust_email']);
        if (!$valid_email[0]) {
            return $valid_email;
        }
        $valid_email = self::AvailableEmail($infos['cust_email'], $this->infos['id']);
        if (!$valid_email) {
            return array(false, 'adresse email indisponible');
        }
        if (strlen(trim($infos['cust_name'])) < 5) {
            return array(false, 'la longueur du nom doit être de 5 caractères minimum');
        }

        // Save customer
        $save_cust = App::$db->prepare("UPDATE customer SET cust_name = :name, cust_company = :company, cust_email = :email WHERE cust_id = :id");
        $save_cust->execute(array(
            "name"      => $infos['cust_name'],
            "company"   => empty($infos['cust_company']) ? null : $infos['cust_company'],
            "email"     => $infos['cust_email'],
            "id"        => $this->infos['id']
        ));
        $this->__construct($this->infos['id']);
        return $save_cust ? array(true) : array(false, 'erreur');
    }

    // DELETE CUSTOMER
    public function Delete()
    {
        $nb_folios = $this->CountTotalFolios();
        if ($nb_folios != 0) {
            return array(false, $nb_folios . ' portfolio(s) existe(nt) toujours pour ce client');
        }
        $del_cust = App::$db->prepare("DELETE FROM customer WHERE cust_id = :id");
        $del_cust->execute(array(
            "id" => $this->infos['id']
        ));
        return array(true);
    }

    // GET NUMBER OF CUSTOMER PORTFOLIO
    public function CountTotalFolios()
    {
        $nb_folios = App::$db->prepare("SELECT COUNT(*) FROM portfolio WHERE port_cust_id = :id");
        $nb_folios->execute(array(
            "id" => $this->infos['id']
        ));
        return $nb_folios->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
    }

    // GET NUMBER OF CUSTOMER ACTIVE PORTFOLIO
    public function CountActiveFolios()
    {
        $nb_folios = App::$db->prepare("SELECT COUNT(*) FROM portfolio WHERE port_cust_id = :id AND port_status = 'open'");
        $nb_folios->execute(array(
            "id" => $this->infos['id']
        ));
        return $nb_folios->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
    }

    // RETURN SQL ROWS AS OBJECTS ARRAY
    static private function ReturnObjectsArray(PDOStatement $customers)
    {
        $customers = $customers->fetchAll(PDO::FETCH_ASSOC);
        $o = array();
        foreach ($customers as $cust) {
            $customer = new Customer($cust['cust_id']);
            array_push($o, $customer);
        }
        return $o;
    }

    // GET FULL CUSTOMER LIST
    static public function FullList()
    {
        $customers = App::$db->query("SELECT cust_id FROM customer ORDER BY cust_name");
        return self::ReturnObjectsArray($customers);
    }

    // GET ACTUAL CUSTOMER LIST
    static public function ActiveList()
    {
        $customers = App::$db->query("SELECT cust_id FROM customer RIGHT JOIN portfolio ON customer.cust_id = portfolio.port_cust_id WHERE portfolio.port_status = 'open' ORDER BY cust_name");
        return self::ReturnObjectsArray($customers);
    }

    // SEARCH AMONG CUSTOMERS
    static public function Search($keywords)
    {
        $keywords = explode(' ', $keywords);
        $query = "SELECT cust_id FROM customer WHERE";
        $query_array = array();
        for ($i = 0; $i < count($keywords); $i += 1) {
            $query .= " ( cust_name LIKE :search_" . $i;
            $query .= " OR cust_company LIKE :search_" . $i;
            $query .= " OR cust_email LIKE :search_" . $i;
            if ($i != (count($keywords) - 1)) {
                $query .= " ) AND ";
            } else {
                $query .= " ) ";
            }
            $query_array['search_' . $i] = "%" . $keywords[$i] . "%";
        }
        $query .= "ORDER BY cust_name";
        $results = App::$db->prepare($query);
        $results->execute($query_array);
        return self::ReturnObjectsArray($results);
    }

    // CREATE NEW CUSTOMER
    static public function Create($infos)
    {
        // Data validation
        $valid_email = User::ValidateEmail($infos['cust_email']);
        if (!$valid_email[0]) {
            return $valid_email;
        }
        $valid_email = self::AvailableEmail($infos['cust_email']);
        if (!$valid_email) {
            return array(false, 'adresse email indisponible');
        }
        if (strlen(trim($infos['cust_name'])) < 5) {
            return array(false, 'la longueur du nom doit être de 5 caractères minimum');
        }

        // Save customer
        $save_cust = App::$db->prepare("INSERT INTO customer (cust_name, cust_company, cust_email) VALUES (:name, :company, :email)");
        $save_cust->execute(array(
            "name"      => $infos['cust_name'],
            "company"   => empty($infos['cust_company']) ? null : $infos['cust_company'],
            "email"     => $infos['cust_email']
        ));
        return $save_cust ? array(true) : array(false, 'erreur');
    }

    // CHECK IF EMAIL IS ALREADY USED
    /**
    *   @param int $upd =cust_id
    */
    static private function AvailableEmail($email, $upd = false)
    {
        if ($upd != false) {
            $duplicate = App::$db->prepare("SELECT COUNT(*) FROM customer WHERE cust_email = :email AND cust_id != :id");
            $duplicate->execute(array(
                "email" => trim($email),
                "id"    => $upd
            ));
        } else {
            $duplicate = App::$db->prepare("SELECT COUNT(*) FROM customer WHERE cust_email = :email");
            $duplicate->execute(array(
                "email" => trim($email)
            ));
        }
        $duplicate = $duplicate->fetch(PDO::FETCH_ASSOC)['COUNT(*)'];
        return $duplicate == 0;
    }
}


?>