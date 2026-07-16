-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: autopool_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `company_wallet`
--

DROP TABLE IF EXISTS `company_wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_wallet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `type` enum('level','reward','other') NOT NULL,
  `level` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_user_id` (`from_user_id`),
  CONSTRAINT `company_wallet_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_wallet`
--

LOCK TABLES `company_wallet` WRITE;
/*!40000 ALTER TABLE `company_wallet` DISABLE KEYS */;
INSERT INTO `company_wallet` VALUES (1,2,0.1000,'level',1,'2026-07-16 13:26:49'),(2,2,0.1000,'level',2,'2026-07-16 13:26:49'),(3,2,0.1000,'level',3,'2026-07-16 13:26:49'),(4,2,0.1000,'level',4,'2026-07-16 13:26:49'),(5,2,0.1000,'level',5,'2026-07-16 13:26:49'),(6,2,0.1000,'level',6,'2026-07-16 13:26:49'),(7,2,0.1000,'level',7,'2026-07-16 13:26:49'),(8,2,0.1000,'level',8,'2026-07-16 13:26:49'),(9,2,0.1000,'level',9,'2026-07-16 13:26:49'),(10,2,0.1000,'level',10,'2026-07-16 13:26:49'),(11,3,0.1000,'level',2,'2026-07-16 13:26:52'),(12,3,0.1000,'level',3,'2026-07-16 13:26:52'),(13,3,0.1000,'level',4,'2026-07-16 13:26:52'),(14,3,0.1000,'level',5,'2026-07-16 13:26:52'),(15,3,0.1000,'level',6,'2026-07-16 13:26:52'),(16,3,0.1000,'level',7,'2026-07-16 13:26:52'),(17,3,0.1000,'level',8,'2026-07-16 13:26:52'),(18,3,0.1000,'level',9,'2026-07-16 13:26:52'),(19,3,0.1000,'level',10,'2026-07-16 13:26:52'),(20,4,0.1000,'level',1,'2026-07-16 13:26:56'),(21,4,0.1000,'level',2,'2026-07-16 13:26:56'),(22,4,0.1000,'level',3,'2026-07-16 13:26:56'),(23,4,0.1000,'level',4,'2026-07-16 13:26:56'),(24,4,0.1000,'level',5,'2026-07-16 13:26:56'),(25,4,0.1000,'level',6,'2026-07-16 13:26:56'),(26,4,0.1000,'level',7,'2026-07-16 13:26:56'),(27,4,0.1000,'level',8,'2026-07-16 13:26:56'),(28,4,0.1000,'level',9,'2026-07-16 13:26:56'),(29,4,0.1000,'level',10,'2026-07-16 13:26:56'),(30,5,0.1000,'level',2,'2026-07-16 13:26:58'),(31,5,0.1000,'level',3,'2026-07-16 13:26:58'),(32,5,0.1000,'level',4,'2026-07-16 13:26:58'),(33,5,0.1000,'level',5,'2026-07-16 13:26:58'),(34,5,0.1000,'level',6,'2026-07-16 13:26:58'),(35,5,0.1000,'level',7,'2026-07-16 13:26:58'),(36,5,0.1000,'level',8,'2026-07-16 13:26:58'),(37,5,0.1000,'level',9,'2026-07-16 13:26:58'),(38,5,0.1000,'level',10,'2026-07-16 13:26:58');
/*!40000 ALTER TABLE `company_wallet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_auth`
--

DROP TABLE IF EXISTS `db_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passcode` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_auth`
--

LOCK TABLES `db_auth` WRITE;
/*!40000 ALTER TABLE `db_auth` DISABLE KEYS */;
INSERT INTO `db_auth` VALUES (1,'000');
/*!40000 ALTER TABLE `db_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `type` enum('sponsor','autopool','level','reward') NOT NULL,
  `level` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('completed','pending') DEFAULT 'completed',
  `blocked_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `from_user_id` (`from_user_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,2,5.0000,'sponsor',0,'2026-07-16 13:26:49','completed',NULL),(2,1,2,0.1250,'autopool',1,'2026-07-16 13:26:49','completed',1),(3,1,3,5.0000,'sponsor',0,'2026-07-16 13:26:52','completed',NULL),(4,1,3,0.1250,'autopool',1,'2026-07-16 13:26:52','completed',NULL),(5,1,3,0.1000,'level',1,'2026-07-16 13:26:52','completed',NULL),(6,2,4,5.0000,'sponsor',0,'2026-07-16 13:26:56','completed',NULL),(7,2,4,0.1250,'autopool',1,'2026-07-16 13:26:56','completed',2),(8,1,4,0.1250,'autopool',2,'2026-07-16 13:26:56','completed',2),(9,2,5,5.0000,'sponsor',0,'2026-07-16 13:26:58','completed',NULL),(10,2,5,0.1250,'autopool',1,'2026-07-16 13:26:58','completed',NULL),(11,1,5,0.1250,'autopool',2,'2026-07-16 13:26:58','completed',NULL),(12,2,5,0.1000,'level',1,'2026-07-16 13:26:58','completed',NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `upline_id` int(11) DEFAULT NULL,
  `position` enum('left','right') DEFAULT NULL,
  `total_earnings` decimal(10,4) DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reward_level` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Neha Dhillon',NULL,NULL,NULL,10.6000,'2026-07-16 13:26:47',1),(2,'Shaurya Rao',1,1,'left',10.3500,'2026-07-16 13:26:49',1),(3,'Shweta Desai',1,1,'right',0.0000,'2026-07-16 13:26:52',0),(4,'Sara Swaminathan',2,2,'left',0.0000,'2026-07-16 13:26:56',0),(5,'Anjali Varghese',2,2,'right',0.0000,'2026-07-16 13:26:58',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-16 19:05:26
