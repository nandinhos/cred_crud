-- MySQL dump 10.13  Distrib 8.0.32, for Linux (x86_64)
--
-- Host: localhost    Database: cred_crud
-- ------------------------------------------------------
-- Server version	8.0.32

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,'App\\Models\\Credential',3,'created',10,NULL,'2025-11-21 02:44:22','2025-11-21 02:44:22'),(2,'App\\Models\\Credential',3,'updated',10,'{\"changes\": {\"name\": \"Nome Atualizado\"}}','2025-11-21 02:44:22','2025-11-21 02:44:22');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credentials`
--

DROP TABLE IF EXISTS `credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credentials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `fscs` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secrecy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credential` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concession` date DEFAULT NULL,
  `validity` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credentials_fscs_unique` (`fscs`),
  KEY `credentials_user_id_index` (`user_id`),
  KEY `credentials_validity_index` (`validity`),
  KEY `credentials_created_at_index` (`created_at`),
  KEY `credentials_user_validity_index` (`user_id`,`validity`),
  KEY `credentials_secrecy_index` (`secrecy`),
  CONSTRAINT `credentials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credentials`
--

LOCK TABLES `credentials` WRITE;
/*!40000 ALTER TABLE `credentials` DISABLE KEYS */;
INSERT INTO `credentials` VALUES (1,NULL,'135656','José Da Silva','S','456321','2005-04-12','2027-04-12','2025-11-19 01:19:02','2025-11-20 03:37:01','2025-11-20 03:37:01'),(2,4,'135657','Carlito','S','4561321','2025-11-07','2027-11-07','2025-11-20 03:54:07','2025-11-21 02:07:02',NULL),(3,10,'FSCS-6903','Nome Atualizado','R','accusantium','2021-08-23','2027-03-10','2025-11-21 02:44:22','2025-11-21 02:44:22',NULL);
/*!40000 ALTER TABLE `credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2023_07_25_152355_create_credentials_table',1),(6,'2023_07_25_181600_add_deleted_at_to_credentials',1),(7,'2023_07_25_195956_alter_credentials_table',1),(8,'2023_07_25_200512_alter_credentials2_table',1),(9,'2025_11_19_021120_create_permission_tables',2),(10,'2025_11_19_194819_add_user_id_to_credentials_table',3),(11,'2025_11_21_020718_create_activity_logs_table',4),(14,'2025_11_21_100000_add_indexes_to_credentials_table',5),(15,'2025_11_21_100010_add_indexes_to_users_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
INSERT INTO `model_has_permissions` VALUES (1,'App\\Models\\User',2),(2,'App\\Models\\User',2),(3,'App\\Models\\User',2),(4,'App\\Models\\User',2),(5,'App\\Models\\User',2),(6,'App\\Models\\User',2),(7,'App\\Models\\User',2),(8,'App\\Models\\User',2),(11,'App\\Models\\User',2),(13,'App\\Models\\User',2),(14,'App\\Models\\User',2),(15,'App\\Models\\User',2),(16,'App\\Models\\User',2),(17,'App\\Models\\User',2),(18,'App\\Models\\User',2),(19,'App\\Models\\User',2),(20,'App\\Models\\User',2),(21,'App\\Models\\User',2),(22,'App\\Models\\User',2),(23,'App\\Models\\User',2),(24,'App\\Models\\User',2),(3,'App\\Models\\User',4),(13,'App\\Models\\User',4),(15,'App\\Models\\User',4),(17,'App\\Models\\User',4),(1,'App\\Models\\User',5),(2,'App\\Models\\User',5),(3,'App\\Models\\User',5),(4,'App\\Models\\User',5),(5,'App\\Models\\User',5),(6,'App\\Models\\User',5),(7,'App\\Models\\User',5),(8,'App\\Models\\User',5),(9,'App\\Models\\User',5),(10,'App\\Models\\User',5),(11,'App\\Models\\User',5),(12,'App\\Models\\User',5),(13,'App\\Models\\User',5),(14,'App\\Models\\User',5),(15,'App\\Models\\User',5),(16,'App\\Models\\User',5),(17,'App\\Models\\User',5),(18,'App\\Models\\User',5),(19,'App\\Models\\User',5),(20,'App\\Models\\User',5),(21,'App\\Models\\User',5),(22,'App\\Models\\User',5),(23,'App\\Models\\User',5),(24,'App\\Models\\User',5);
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',2),(3,'App\\Models\\User',3),(2,'App\\Models\\User',4),(1,'App\\Models\\User',5);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(2,'view_any_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(3,'create_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(4,'update_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(5,'restore_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(6,'restore_any_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(7,'replicate_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(8,'reorder_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(9,'delete_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(10,'delete_any_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(11,'force_delete_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(12,'force_delete_any_credential','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(13,'view_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(14,'view_any_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(15,'create_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(16,'update_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(17,'delete_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(18,'delete_any_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(19,'restore_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(20,'restore_any_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(21,'force_delete_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(22,'force_delete_any_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(23,'replicate_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10'),(24,'reorder_users','web','2025-11-20 04:34:10','2025-11-20 04:34:10');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2025-11-19 02:11:52','2025-11-19 02:11:52'),(2,'admin','web','2025-11-20 03:26:54','2025-11-20 03:26:54'),(3,'consulta','web','2025-11-20 03:26:54','2025-11-20 03:26:54');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_email_index` (`email`),
  KEY `users_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Administrator','admin@admin.com','2025-11-19 01:51:10','$2y$10$Ubb0OsJ54bFSaZ./Lv3q3ORbpib3mFwGFUE9HxN.2BYDC4yab3xgm',NULL,'2025-11-19 01:51:10','2025-11-19 01:51:10'),(3,'Usuário Consulta','consulta@teste.com',NULL,'$2y$10$Ubb0OsJ54bFSaZ./Lv3q3ORbpib3mFwGFUE9HxN.2BYDC4yab3xgm',NULL,'2025-11-20 03:32:01','2025-11-20 03:32:01'),(4,'José Da Silva','jose@teste.com',NULL,'$2y$10$fdeHPCRKkKrmMCWg9B9WP.VxrDuX1cXUje4FP6Ay9Fccvjeb3yR0O',NULL,'2025-11-20 05:49:05','2025-11-20 05:49:05'),(5,'Super Admin','super@admin.com',NULL,'$2y$10$LB5nS0tfG7oTtxPL0HM4A.sK4a06N2bblUXZAzoGGbsKApGyr3DB.',NULL,'2025-11-21 02:00:06','2025-11-21 02:00:06'),(6,'Alonso Vale Mendonça Filho','ldomingues@example.net','2025-11-21 02:44:07','$2y$10$B9MxpwAi21/HERQHfrKgsuHkio1zJ0Lt9EGpVIEEzy3VwkZHUCgii','y7RdXZkH3n','2025-11-21 02:44:08','2025-11-21 02:44:08'),(7,'Srta. Camila Leon Ávila','analu.carvalho@example.org','2025-11-21 02:44:08','$2y$10$B9MxpwAi21/HERQHfrKgsuHkio1zJ0Lt9EGpVIEEzy3VwkZHUCgii','xMbjCXsJpJ','2025-11-21 02:44:08','2025-11-21 02:44:08'),(8,'Benedito Richard Fernandes Filho','romero.lorenzo@example.com','2025-11-21 02:44:08','$2y$10$B9MxpwAi21/HERQHfrKgsuHkio1zJ0Lt9EGpVIEEzy3VwkZHUCgii','SJzghpcchq','2025-11-21 02:44:08','2025-11-21 02:44:08'),(9,'Dayana Rodrigues Escobar Filho','dasneves.miguel@example.org','2025-11-21 02:44:08','$2y$10$B9MxpwAi21/HERQHfrKgsuHkio1zJ0Lt9EGpVIEEzy3VwkZHUCgii','j59nLeBCw6','2025-11-21 02:44:08','2025-11-21 02:44:08'),(10,'Srta. Juliane Queirós Valente Filho','renata.balestero@example.net','2025-11-21 02:44:22','$2y$10$6cE7b8Cd/KecE0WDUrxGBOtW9OoLHKDUP5xjTowKlmucSdozsRW2m','7mLnEK8Uhm','2025-11-21 02:44:22','2025-11-21 02:44:22'),(12,'Dr. Abgail Rodrigues Mendonça','normal@test.com','2025-11-21 02:44:58','$2y$10$sr6/3j1Y8sz73w.f7Dev8.xDlIzGHXstUGVXhWR2aFk4l2evitECa','nv4kTtutyU','2025-11-21 02:44:58','2025-11-21 02:44:58');
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

-- Dump completed on 2025-11-21  3:12:51
