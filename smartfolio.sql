-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 15 mai 2018 à 14:41
-- Version du serveur :  10.1.30-MariaDB
-- Version de PHP :  7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `smartfolio`
--
CREATE DATABASE IF NOT EXISTS `smartfolio` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `smartfolio`;

-- --------------------------------------------------------

--
-- Structure de la table `currency`
--

CREATE TABLE `currency` (
  `curr_id` int(8) NOT NULL,
  `curr_name` varchar(255) NOT NULL,
  `curr_symbol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `customer`
--

CREATE TABLE `customer` (
  `cust_id` int(8) NOT NULL,
  `cust_name` varchar(100) NOT NULL,
  `cust_company` varchar(255) DEFAULT NULL,
  `cust_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `exchange`
--

CREATE TABLE `exchange` (
  `exchange_id` int(4) NOT NULL,
  `exchange_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ohlc`
--

CREATE TABLE `ohlc` (
  `ohlc_id` int(50) NOT NULL,
  `ohlc_pair_id` int(8) NOT NULL,
  `ohlc_timestamp` bigint(20) NOT NULL,
  `ohlc_open` decimal(36,18) NOT NULL,
  `ohlc_high` decimal(36,18) NOT NULL,
  `ohlc_low` decimal(36,18) NOT NULL,
  `ohlc_close` decimal(36,18) NOT NULL,
  `ohlc_volume` decimal(36,18) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `pair`
--

CREATE TABLE `pair` (
  `pair_id` int(8) NOT NULL,
  `pair_curr_a` int(8) NOT NULL,
  `pair_curr_b` int(8) NOT NULL,
  `pair_exchange_id` int(4) NOT NULL,
  `pair_api_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `portfolio`
--

CREATE TABLE `portfolio` (
  `port_id` int(8) NOT NULL,
  `port_cust_id` int(8) NOT NULL,
  `port_agent_id` int(4) DEFAULT NULL,
  `port_status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `port_accumulator`
--

CREATE TABLE `port_accumulator` (
  `acc_id` int(8) NOT NULL,
  `acc_port_id` int(8) NOT NULL,
  `acc_curr_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

CREATE TABLE `transaction` (
  `tx_id` int(24) NOT NULL,
  `tx_port_id` int(8) NOT NULL,
  `tx_type` varchar(25) NOT NULL,
  `tx_pair_id` int(8) DEFAULT NULL,
  `tx_transfer_curr_id` int(8) DEFAULT NULL,
  `tx_transfer_exchange_id_from` int(4) DEFAULT NULL,
  `tx_transfer_exchange_id_to` int(4) DEFAULT NULL,
  `tx_price` decimal(36,18) DEFAULT NULL,
  `tx_amount` decimal(36,18) NOT NULL,
  `tx_fee_amount` decimal(36,18) DEFAULT NULL,
  `tx_fee_type` varchar(50) DEFAULT NULL,
  `tx_timestamp` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `user_id` int(4) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_manager` int(4) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `user_token` varchar(255) NOT NULL,
  `user_activated_account` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`curr_id`);

--
-- Index pour la table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cust_id`);

--
-- Index pour la table `exchange`
--
ALTER TABLE `exchange`
  ADD PRIMARY KEY (`exchange_id`);

--
-- Index pour la table `ohlc`
--
ALTER TABLE `ohlc`
  ADD PRIMARY KEY (`ohlc_id`);

--
-- Index pour la table `pair`
--
ALTER TABLE `pair`
  ADD PRIMARY KEY (`pair_id`);

--
-- Index pour la table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`port_id`);

--
-- Index pour la table `port_accumulator`
--
ALTER TABLE `port_accumulator`
  ADD PRIMARY KEY (`acc_id`);

--
-- Index pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`tx_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `currency`
--
ALTER TABLE `currency`
  MODIFY `curr_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `customer`
--
ALTER TABLE `customer`
  MODIFY `cust_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `exchange`
--
ALTER TABLE `exchange`
  MODIFY `exchange_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ohlc`
--
ALTER TABLE `ohlc`
  MODIFY `ohlc_id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=602;

--
-- AUTO_INCREMENT pour la table `pair`
--
ALTER TABLE `pair`
  MODIFY `pair_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `port_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `port_accumulator`
--
ALTER TABLE `port_accumulator`
  MODIFY `acc_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `tx_id` int(24) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
