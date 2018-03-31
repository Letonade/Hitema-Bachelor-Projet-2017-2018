# Base de données #
>Idées pour la base de donnée du projet

## TABLE: user ##
 - user_id
 - user_name
 - user_email
 - user_password
 - user_type [manager / trader]
 - user_token
 - user_activated_account

## TABLE: customer ##
 - cust_id
 - cust_name
 - cust_company
 - cust_email

## TABLE: portfolio ##
 - portf_id
 - portf_cust_id
 - portf_trader_id
 - portf_exchange [plateforme de trading] ***?***

## TABLE: order [achat/vente] ##
 - order_id
 - order_portf_id
 - order_type [buy / sell]
 - order_currency
 - order_pair
 - order_currency_price
 - order_amount
 - order_timestamp
 - order_currency_fees
 - order_type_fees
 - order_amount_fees

## TABLE: index_currency [monnaies de fonds] ##
 - index_id
 - index_portf_id
 - index_currency

## TABLE: currency ##
***?***
Possibilité de récupérer la liste 'live' via API ?

## TABLE: exchange ##
***?***
Possibilité de récupérer la liste 'live' via API ?

## TABLE: OHLC ##
***?***
OHLC = Open High Low Close
Données pour les graphiques en chandelle.

**Proposition:** visionnage des données dispo via API pour le graphique d'investissement
Pour le graphique des 'rapports', enregistrer les données tous les jours (voire planification auto d'une tache régulière ?)