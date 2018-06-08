-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  jeu. 07 juin 2018 à 11:38
-- Version du serveur :  10.1.31-MariaDB
-- Version de PHP :  7.2.3

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
CREATE DATABASE IF NOT EXISTS `smartfolio` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `smartfolio`;

-- --------------------------------------------------------

--
-- Structure de la table `alerts`
--

CREATE TABLE `alerts` (
  `alerts_id` int(8) NOT NULL,
  `user_id` int(4) NOT NULL,
  `acc_port_id` int(8) NOT NULL,
  `acc_curr_id` int(8) NOT NULL,
  `alerts_value` decimal(36,18) NOT NULL,
  `alerts_compare` text NOT NULL,
  `alerts_type` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table pour les alertes.';

--
-- Déchargement des données de la table `alerts`
--

INSERT INTO `alerts` (`alerts_id`, `user_id`, `acc_port_id`, `acc_curr_id`, `alerts_value`, `alerts_compare`, `alerts_type`) VALUES
(13, 3, 2, 18, '115.000000000000000000', '>=', 'fixe');

-- --------------------------------------------------------

--
-- Structure de la table `currency`
--

CREATE TABLE `currency` (
  `curr_id` int(8) NOT NULL,
  `curr_name` varchar(255) NOT NULL,
  `curr_symbol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `currency`
--

INSERT INTO `currency` (`curr_id`, `curr_name`, `curr_symbol`) VALUES
(1, 'Bitcoin', 'BTC'),
(2, 'USD Tether', 'USDT'),
(3, 'Ethereum', 'ETH'),
(5, 'Litecoin', 'LTC'),
(6, 'Monero', 'XMR'),
(7, 'Ripple', 'XRP'),
(8, 'Bitcoin Cash', 'BCH'),
(9, 'EOS', 'EOS'),
(10, 'Cardano', 'ADA'),
(11, 'Stellar', 'XLM'),
(12, 'NEO', 'NEO'),
(13, 'IOTA', 'IOTA'),
(14, 'Dash', 'DASH'),
(15, 'TRON', 'TRX'),
(16, 'NEM', 'XEM'),
(17, 'Binance Coin', 'BNB'),
(18, 'Ethereum Classic', 'ETC'),
(19, 'VeChain', 'VEN'),
(20, 'Qtum', 'QTUM'),
(21, 'Verge', 'XVG'),
(22, 'OmiseGo', 'OMG'),
(23, 'Lisk', 'LSK'),
(24, 'Ontology', 'ONT'),
(25, 'ICON', 'ICX'),
(26, 'Bitcoin Gold', 'BTG'),
(27, 'US Dollar', 'USD'),
(28, 'Livre Sterling', 'GBP');

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

--
-- Déchargement des données de la table `customer`
--

INSERT INTO `customer` (`cust_id`, `cust_name`, `cust_company`, `cust_email`) VALUES
(3, 'Toto Test', 'Test &Co', 'test@test.fr'),
(4, 'Adrien Neto Ferreira', 'Adrien Corp', 'iamnautilusjungle@gmail.com'),
(5, 'maximax', 'asse', 'max@test.fr'),
(6, 'Aymeric Mayeux', 'AymDev', 'aymericmayeux@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `exchange`
--

CREATE TABLE `exchange` (
  `exchange_id` int(4) NOT NULL,
  `exchange_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `exchange`
--

INSERT INTO `exchange` (`exchange_id`, `exchange_name`) VALUES
(1, 'Bittrex'),
(2, 'Bitfinex'),
(3, 'Binance'),
(4, 'OKEx'),
(5, 'KuCoin'),
(6, 'Kraken'),
(7, 'Poloniex');

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

--
-- Déchargement des données de la table `pair`
--

INSERT INTO `pair` (`pair_id`, `pair_curr_a`, `pair_curr_b`, `pair_exchange_id`, `pair_api_url`) VALUES
(1, 1, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/btcusdt/ohlc'),
(2, 3, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/ethbtc/ohlc'),
(3, 3, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/ethusdt/ohlc'),
(4, 5, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/ltcusdt/ohlc'),
(5, 6, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/xmrusdt/ohlc'),
(6, 5, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/ltcbtc/ohlc'),
(7, 6, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/xmrbtc/ohlc'),
(8, 10, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/adabtc/ohlc'),
(9, 10, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/adausdt/ohlc'),
(10, 26, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/btgbtc/ohlc'),
(11, 26, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/btgusdt/ohlc'),
(12, 14, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/dashbtc/ohlc'),
(13, 14, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/dashusdt/ohlc'),
(14, 18, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/etcbtc/ohlc'),
(15, 18, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/etcusdt/ohlc'),
(16, 23, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/lskbtc/ohlc'),
(17, 12, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/neobtc/ohlc'),
(18, 12, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/neousdt/ohlc'),
(19, 22, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/omgbtc/ohlc'),
(20, 22, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/omgusdt/ohlc'),
(21, 20, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/qtumbtc/ohlc'),
(22, 15, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/trxbtc/ohlc'),
(23, 16, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/xembtc/ohlc'),
(24, 11, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/xlmbtc/ohlc'),
(25, 7, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/xrpbtc/ohlc'),
(26, 7, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/xrpusdt/ohlc'),
(27, 21, 1, 1, 'https://api.cryptowat.ch/markets/bittrex/xvgbtc/ohlc'),
(28, 21, 2, 1, 'https://api.cryptowat.ch/markets/bittrex/xvgusdt/ohlc'),
(29, 1, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/btcusd/ohlc'),
(30, 3, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/ethusd/ohlc'),
(31, 3, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/ethbtc/ohlc'),
(32, 9, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/eosbtc/ohlc'),
(33, 9, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/eoseth/ohlc'),
(34, 5, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/ltcusd/ohlc'),
(35, 5, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/ltcbtc/ohlc'),
(36, 7, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/xrpusd/ohlc'),
(37, 7, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/xrpbtc/ohlc'),
(38, 8, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/bchusd/ohlc'),
(39, 8, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/bchbtc/ohlc'),
(40, 8, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/bcheth/ohlc'),
(41, 13, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/iotusd/ohlc'),
(42, 13, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/iotbtc/ohlc'),
(43, 13, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/ioteth/ohlc'),
(44, 12, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/neousd/ohlc'),
(45, 12, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/neobtc/ohlc'),
(46, 12, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/neoeth/ohlc'),
(47, 18, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/etcusd/ohlc'),
(48, 18, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/etcbtc/ohlc'),
(49, 22, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/omgusd/ohlc'),
(50, 22, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/omgbtc/ohlc'),
(51, 22, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/omgeth/ohlc'),
(52, 6, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/xmrusd/ohlc'),
(53, 6, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/xmrbtc/ohlc'),
(54, 14, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/dashusd/ohlc'),
(55, 14, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/dashbtc/ohlc'),
(56, 15, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/trxusd/ohlc'),
(57, 15, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/trxbtc/ohlc'),
(58, 15, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/trxeth/ohlc'),
(59, 26, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/btgusd/ohlc'),
(60, 26, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/btgbtc/ohlc'),
(61, 20, 27, 2, 'https://api.cryptowat.ch/markets/bitfinex/qtumusd/ohlc'),
(62, 20, 1, 2, 'https://api.cryptowat.ch/markets/bitfinex/qtumbtc/ohlc'),
(63, 20, 3, 2, 'https://api.cryptowat.ch/markets/bitfinex/qtumeth/ohlc'),
(64, 3, 1, 3, 'https://api.cryptowat.ch/markets/binance/ethbtc/ohlc'),
(65, 12, 1, 3, 'https://api.cryptowat.ch/markets/binance/neobtc/ohlc'),
(66, 21, 1, 3, 'https://api.cryptowat.ch/markets/binance/xvgbtc/ohlc'),
(67, 15, 1, 3, 'https://api.cryptowat.ch/markets/binance/trxbtc/ohlc'),
(68, 9, 1, 3, 'https://api.cryptowat.ch/markets/binance/eosbtc/ohlc'),
(69, 25, 1, 3, 'https://api.cryptowat.ch/markets/binance/icxbtc/ohlc'),
(70, 7, 1, 3, 'https://api.cryptowat.ch/markets/binance/xrpbtc/ohlc'),
(71, 10, 1, 3, 'https://api.cryptowat.ch/markets/binance/adabtc/ohlc'),
(72, 11, 1, 3, 'https://api.cryptowat.ch/markets/binance/xlmbtc/ohlc'),
(73, 20, 1, 3, 'https://api.cryptowat.ch/markets/binance/qtumbtc/ohlc'),
(74, 5, 1, 3, 'https://api.cryptowat.ch/markets/binance/ltcbtc/ohlc'),
(75, 14, 1, 3, 'https://api.cryptowat.ch/markets/binance/dashbtc/ohlc'),
(76, 22, 1, 3, 'https://api.cryptowat.ch/markets/binance/omgbtc/ohlc'),
(77, 18, 1, 3, 'https://api.cryptowat.ch/markets/binance/etcbtc/ohlc'),
(78, 23, 1, 3, 'https://api.cryptowat.ch/markets/binance/lskbtc/ohlc'),
(79, 6, 1, 3, 'https://api.cryptowat.ch/markets/binance/xmrbtc/ohlc'),
(80, 26, 1, 3, 'https://api.cryptowat.ch/markets/binance/btgbtc/ohlc'),
(81, 16, 1, 3, 'https://api.cryptowat.ch/markets/binance/xembtc/ohlc'),
(82, 9, 3, 3, 'https://api.cryptowat.ch/markets/binance/eoseth/ohlc'),
(83, 12, 3, 3, 'https://api.cryptowat.ch/markets/binance/neoeth/ohlc'),
(84, 15, 3, 3, 'https://api.cryptowat.ch/markets/binance/trxeth/ohlc'),
(85, 10, 3, 3, 'https://api.cryptowat.ch/markets/binance/adaeth/ohlc'),
(86, 25, 3, 3, 'https://api.cryptowat.ch/markets/binance/icxeth/ohlc'),
(87, 7, 3, 3, 'https://api.cryptowat.ch/markets/binance/xrpeth/ohlc'),
(88, 11, 3, 3, 'https://api.cryptowat.ch/markets/binance/xlmeth/ohlc'),
(89, 5, 3, 3, 'https://api.cryptowat.ch/markets/binance/ltceth/ohlc'),
(90, 20, 3, 3, 'https://api.cryptowat.ch/markets/binance/qtumeth/ohlc'),
(91, 22, 3, 3, 'https://api.cryptowat.ch/markets/binance/omgeth/ohlc'),
(92, 23, 3, 3, 'https://api.cryptowat.ch/markets/binance/lsketh/ohlc'),
(93, 18, 3, 3, 'https://api.cryptowat.ch/markets/binance/etceth/ohlc'),
(94, 6, 3, 3, 'https://api.cryptowat.ch/markets/binance/xmreth/ohlc'),
(95, 14, 3, 3, 'https://api.cryptowat.ch/markets/binance/dasheth/ohlc'),
(96, 16, 3, 3, 'https://api.cryptowat.ch/markets/binance/xemeth/ohlc'),
(97, 26, 3, 3, 'https://api.cryptowat.ch/markets/binance/btgeth/ohlc'),
(98, 1, 2, 3, 'https://api.cryptowat.ch/markets/binance/btcusdt/ohlc'),
(99, 3, 2, 3, 'https://api.cryptowat.ch/markets/binance/ethusdt/ohlc'),
(100, 12, 2, 3, 'https://api.cryptowat.ch/markets/binance/neousdt/ohlc'),
(101, 5, 2, 3, 'https://api.cryptowat.ch/markets/binance/ltcusdt/ohlc'),
(102, 8, 27, 6, 'https://api.cryptowat.ch/markets/kraken/bchusd/ohlc'),
(103, 14, 27, 6, 'https://api.cryptowat.ch/markets/kraken/dashusd/ohlc'),
(104, 9, 3, 6, 'https://api.cryptowat.ch/markets/kraken/eoseth/ohlc'),
(105, 9, 27, 6, 'https://api.cryptowat.ch/markets/kraken/eosusd/ohlc'),
(106, 18, 3, 6, 'https://api.cryptowat.ch/markets/kraken/etceth/ohlc'),
(107, 18, 27, 6, 'https://api.cryptowat.ch/markets/kraken/etcusd/ohlc'),
(108, 3, 27, 6, 'https://api.cryptowat.ch/markets/kraken/ethusd/ohlc'),
(109, 5, 27, 6, 'https://api.cryptowat.ch/markets/kraken/ltcusd/ohlc'),
(110, 2, 27, 6, 'https://api.cryptowat.ch/markets/kraken/usdtusd/ohlc'),
(111, 6, 27, 6, 'https://api.cryptowat.ch/markets/kraken/xmrusd/ohlc'),
(112, 7, 27, 6, 'https://api.cryptowat.ch/markets/kraken/xrpusd/ohlc'),
(113, 3, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/ethbtc/ohlc'),
(114, 7, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/xrpbtc/ohlc'),
(115, 5, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/ltcbtc/ohlc'),
(116, 14, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/dashbtc/ohlc'),
(117, 8, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/bchbtc/ohlc'),
(118, 16, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/xembtc/ohlc'),
(119, 6, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/xmrbtc/ohlc'),
(120, 18, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/etcbtc/ohlc'),
(121, 23, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/lskbtc/ohlc'),
(122, 22, 1, 7, 'https://api.cryptowat.ch/markets/poloniex/omgbtc/ohlc'),
(123, 18, 3, 7, 'https://api.cryptowat.ch/markets/poloniex/etceth/ohlc'),
(124, 8, 3, 7, 'https://api.cryptowat.ch/markets/poloniex/bcheth/ohlc'),
(125, 22, 3, 7, 'https://api.cryptowat.ch/markets/poloniex/omgeth/ohlc'),
(126, 23, 3, 7, 'https://api.cryptowat.ch/markets/poloniex/lsketh/ohlc'),
(127, 5, 6, 7, 'https://api.cryptowat.ch/markets/poloniex/ltcxmr/ohlc'),
(128, 14, 6, 7, 'https://api.cryptowat.ch/markets/poloniex/dashxmr/ohlc'),
(129, 1, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/btcusdt/ohlc'),
(130, 3, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/ethusdt/ohlc'),
(131, 7, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/xrpusdt/ohlc'),
(132, 5, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/ltcusdt/ohlc'),
(133, 8, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/bchusdt/ohlc'),
(134, 18, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/etcusdt/ohlc'),
(135, 6, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/xmrusdt/ohlc'),
(136, 14, 2, 7, 'https://api.cryptowat.ch/markets/poloniex/dashusdt/ohlc'),
(137, 21, 3, 3, 'https://api.cryptowat.ch/markets/binance/xvgeth/ohlc');

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

--
-- Déchargement des données de la table `portfolio`
--

INSERT INTO `portfolio` (`port_id`, `port_cust_id`, `port_agent_id`, `port_status`) VALUES
(1, 4, 3, 'open'),
(2, 6, 3, 'open'),
(3, 3, 5, 'open'),
(4, 5, 9, 'open');

-- --------------------------------------------------------

--
-- Structure de la table `port_accumulator`
--

CREATE TABLE `port_accumulator` (
  `acc_id` int(8) NOT NULL,
  `acc_port_id` int(8) NOT NULL,
  `acc_curr_id` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `port_accumulator`
--

INSERT INTO `port_accumulator` (`acc_id`, `acc_port_id`, `acc_curr_id`) VALUES
(1, 1, 1),
(2, 1, 27),
(4, 2, 3),
(5, 2, 27),
(6, 2, 1);

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

--
-- Déchargement des données de la table `transaction`
--

INSERT INTO `transaction` (`tx_id`, `tx_port_id`, `tx_type`, `tx_pair_id`, `tx_transfer_curr_id`, `tx_transfer_exchange_id_from`, `tx_transfer_exchange_id_to`, `tx_price`, `tx_amount`, `tx_fee_amount`, `tx_fee_type`, `tx_timestamp`) VALUES
(1, 1, 'buy', 27, NULL, NULL, NULL, '0.000011800000000000', '829.403497880000000000', '0.250000000000000000', 'percent_index', 1514580180),
(2, 2, 'buy', 27, NULL, NULL, NULL, '0.000011800000000000', '829.400000000000000000', '0.250000000000000000', 'percent_index', 1514583780),
(3, 2, 'buy', 28, NULL, NULL, NULL, '0.139950000000000000', '325.250000000000000000', '0.250000000000000000', 'percent_index', 1514657880),
(4, 2, 'buy', 137, NULL, NULL, NULL, '0.000204480000000000', '700.000000000000000000', '0.000000000000000000', 'percent_index', 1514679360),
(5, 2, 'buy', 61, NULL, NULL, NULL, '50.000000000000000000', '4.284900000000000000', '0.000000000000000000', 'percent_index', 1515963660),
(6, 2, 'buy', 47, NULL, NULL, NULL, '33.680000000000000000', '1.719000000000000000', '0.000000000000000000', 'percent_index', 1516367400),
(7, 2, 'deposit', NULL, 1, NULL, 5, NULL, '0.001343340000000000', NULL, NULL, 1523618100),
(8, 2, 'deposit', NULL, 3, NULL, 5, NULL, '0.000266360000000000', NULL, NULL, 1523618160),
(9, 3, 'buy', 75, NULL, NULL, NULL, '150.000000000000000000', '15.000000000000000000', '20.000000000000000000', 'fixed_index', 1527843840),
(10, 3, 'buy', 75, NULL, NULL, NULL, '700.000000000000000000', '40.000000000000000000', '20.000000000000000000', 'fixed_currency', 1527843900),
(11, 3, 'deposit', NULL, 14, NULL, 2, NULL, '20.000000000000000000', NULL, NULL, 1527843960),
(12, 3, 'sell', 75, NULL, NULL, NULL, '40.000000000000000000', '1.000000000000000000', '5.000000000000000000', 'fixed_index', 1527844020),
(13, 1, 'deposit', NULL, 1, NULL, 2, NULL, '100.000000000000000000', NULL, NULL, 1522597500),
(14, 2, 'buy', 93, NULL, NULL, NULL, '15.000000000000000000', '1.000000000000000000', '1.000000000000000000', 'fixed_currency', 1528362960),
(15, 2, 'buy', 93, NULL, NULL, NULL, '15.000000000000000000', '1.000000000000000000', '1.000000000000000000', 'fixed_currency', 1528362960),
(16, 2, 'buy', 93, NULL, NULL, NULL, '15.000000000000000000', '1.000000000000000000', '1.000000000000000000', 'fixed_currency', 1528362960),
(17, 2, 'buy', 93, NULL, NULL, NULL, '15.000000000000000000', '1.000000000000000000', '1.000000000000000000', 'fixed_currency', 1528362960);

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
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_email`, `user_manager`, `user_password`, `user_type`, `user_token`, `user_activated_account`) VALUES
(3, 'aymdev', 'aymericmayeux@gmail.com', NULL, '$2y$10$TUhjBZPqcpKoe0fKEFHXHudqf/bW1vmxB6gUNN7KhaPbiXGh0OIiO', 'manager', '$2y$10$tpDZiEhweHXUrVQZXvKpVuNpKk5jB3c8BJNXTQkPmvgZ1SKNJ7Vja', 1),
(4, 'maxdev42', 'maxence.faure.pro@gmail.com', NULL, '$2y$10$x7P9AuV6FuQv7Mu/8O42g.xpIjrCCSFkH4yJm9WrYQ6AbSu3X.AbK', 'manager', '$2y$10$TkYuqh/06Su9D3CGIP/bhOd0KNXxdokg9BnRecABdNshimHWTNvCC', 1),
(5, 'leton', 'cocoscorpio94@hotmail.fr', NULL, '$2y$10$I9v/OBV4CBl1gRSdiJsw7O/sDLEqFMhyyhAEryXcwH2cPHR3Lj7Gy', 'manager', '$2y$10$xJIysUwpEkAe5GAoPeWmreYIJX9Jesrcg4U6OwB2Xh.eMgdYmYL0a', 1),
(7, 'lucas', 'luc@gmail.com', 4, '$2y$10$9G.5hqt.Ad/mcTCzIPwp2OCW5IgD4A3weskjjJU/JQY7F1s.keEYK', 'agent', '$2y$10$71pe03jIIqk9wbfS0.CfleRfckPmVRt64FD0Wcqir/opHVKTcQ65i', 0),
(8, 'John Doe', 'johndoe@gts.fr', 3, '$2y$10$4AyfWL2.4W4MRNmlPuW8vOnUcxgo5Rm53T8JMec1IT4ir6/MlCMNu', 'agent', '$2y$10$pXftZFZV1avzLscCbI3smu2.DEzYjiDTxFvi3qs9.WDIJxBm6Kufm', 0),
(9, 'Quioufeed', 'Q.Lehman@Coffeded.fr', 5, '$2y$10$dfQnzVJo3gNzNwei7OlxXu9KB9lJjeI1IschtBh75T2uLOCBr0PIG', 'agent', '$2y$10$3Za8dYCiwPrbJeqTi/LRJudvIs2FJaLof1H4voC1LVwM84Ef7IkTC', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alerts_id`);

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
-- AUTO_INCREMENT pour la table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alerts_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `currency`
--
ALTER TABLE `currency`
  MODIFY `curr_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `customer`
--
ALTER TABLE `customer`
  MODIFY `cust_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `exchange`
--
ALTER TABLE `exchange`
  MODIFY `exchange_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `pair`
--
ALTER TABLE `pair`
  MODIFY `pair_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT pour la table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `port_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `port_accumulator`
--
ALTER TABLE `port_accumulator`
  MODIFY `acc_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `tx_id` int(24) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
