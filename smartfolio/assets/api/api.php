<<?php

//TODO requière connection BDD


function get_info_portfeuille($id) {
  $sql = SELECT * FROM portfolio WHERE port_agent_id = $id;
  return $sql;
}


function set_id_portefeuille(){
  $possible_url = array("id_portfolio");
  $value = "Une erreur est survenue";

  if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url)) {
  switch ($_GET["action"]) {
  case "id_portfolio": if (isset($_GET["id"])) $value = get_info_portfeuille($_GET["id"], $pdo);

  else $value = "Argument manquant"; break; }

  exit(json_encode($value));
  }
}

 ?>
