-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 02 jan. 2021 à 10:42
-- Version du serveur :  10.3.16-MariaDB
-- Version de PHP : 7.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `id15758032_slurpcocktaildb`
--

-- --------------------------------------------------------

--
-- Structure de la table `RecetteFavorite`
--

CREATE TABLE `RecetteFavorite` (
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `recette` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `User`
--

CREATE TABLE `User` (
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `pwd_hash` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prenom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sexe` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateNaiss` date DEFAULT NULL,
  `adresse` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codepostal` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `RecetteFavorite`
--
ALTER TABLE `RecetteFavorite`
  ADD PRIMARY KEY (`email`,`recette`);

--
-- Index pour la table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`email`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `RecetteFavorite`
--
ALTER TABLE `RecetteFavorite`
  ADD CONSTRAINT `RecetteFavorite_ibfk_1` FOREIGN KEY (`email`) REFERENCES `User` (`email`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
