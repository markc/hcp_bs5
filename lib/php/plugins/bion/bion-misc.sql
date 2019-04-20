DROP TABLE IF EXISTS `bion_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bion_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL,
  `login` varchar(63) NOT NULL,
  `admin` varchar(63) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bion_clients`
--

LOCK TABLES `bion_clients` WRITE;
/*!40000 ALTER TABLE `bion_clients` DISABLE KEYS */;
INSERT INTO `bion_clients` VALUES (1,'Charles','BionSystems','BionSystemsADM','2019-02-25 18:40:49','2019-02-25 18:01:55'),(2,'Frank3','BionSystems2','BionSystems2ADM','2019-03-03 16:33:36','2019-02-25 18:02:45');
/*!40000 ALTER TABLE `bion_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bion_sites`
--

DROP TABLE IF EXISTS `bion_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bion_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clients_id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `city` varchar(63) NOT NULL,
  `postcode` int(11) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bion_sites`
--

LOCK TABLES `bion_sites` WRITE;
/*!40000 ALTER TABLE `bion_sites` DISABLE KEYS */;
INSERT INTO `bion_sites` VALUES (1,0,'Halburt Close','Yungaburra2',4884,'2019-02-25 20:17:25','2019-02-25 20:13:04'),(2,0,'Gold Coast','Broadbeach',4218,'2019-02-25 20:17:49','2019-02-25 20:17:49');
/*!40000 ALTER TABLE `bion_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bion_units`
--

DROP TABLE IF EXISTS `bion_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bion_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `port` int(11) NOT NULL,
  `link_user` varchar(127) NOT NULL,
  `link_admin` varchar(127) NOT NULL,
  `link_files` varchar(127) NOT NULL,
  `link_charts` varchar(127) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bion_units`
--

LOCK TABLES `bion_units` WRITE;
/*!40000 ALTER TABLE `bion_units` DISABLE KEYS */;
INSERT INTO `bion_units` VALUES (1,0,'Channie',9002,'https://localhost:9002','https://localhost:9002/index_full.html','https://bion.cloud/s/3KE8nMGHTBiTy6m','https://bion.cloud/s/PAkAXEAFAKtdmRi','2019-02-25 20:41:12','2019-02-25 20:41:12');
/*!40000 ALTER TABLE `bion_units` ENABLE KEYS */;
UNLOCK TABLES;




DROP TABLE IF EXISTS `solidus_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solidus_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL,
  `login` varchar(63) NOT NULL,
  `admin` varchar(63) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solidus_clients`
--

LOCK TABLES `solidus_clients` WRITE;
/*!40000 ALTER TABLE `solidus_clients` DISABLE KEYS */;
INSERT INTO `solidus_clients` VALUES (1,'Charles','BionSystems','BionSystemsADM','2019-02-25 18:40:49','2019-02-25 18:01:55'),(2,'Frank3','BionSystems2','BionSystems2ADM','2019-03-03 16:33:36','2019-02-25 18:02:45');
/*!40000 ALTER TABLE `solidus_clients` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `despatch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `despatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `despatch_date` varchar(13) DEFAULT NULL,
  `invoice_date` varchar(12) DEFAULT NULL,
  `invoice_num` varchar(5) DEFAULT NULL,
  `customer` varchar(22) DEFAULT NULL,
  `contact` varchar(8) DEFAULT NULL,
  `phone_email` varchar(12) DEFAULT NULL,
  `address` varchar(20) DEFAULT NULL,
  `customer_po` varchar(12) DEFAULT NULL,
  `freight_company` varchar(15) DEFAULT NULL,
  `con_note` varchar(13) DEFAULT NULL,
  `qty` varchar(3) DEFAULT NULL,
  `product` varchar(9) DEFAULT NULL,
  `size` varchar(4) DEFAULT NULL,
  `batch_num` varchar(12) DEFAULT NULL,
  `weight` varchar(6) DEFAULT NULL,
  `pallets` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despatch`
--

LOCK TABLES `despatch` WRITE;
/*!40000 ALTER TABLE `despatch` DISABLE KEYS */;
INSERT INTO `despatch` VALUES (0,'03/01/19','03/01/19','7507','Babcock MCS','','','Mackay/Queensland','MP2563','TNT overnight','','2','Zi-400 AV','20','','42',''),(1,'07/01/19','07/01/19','7508','Sikorsky Aircraft aust','Tony','0411 216 104','Pinkenba QLD','Tony20190107','TNT Road','98022355 4777','1','Zi-400 AV','20','1806A001','21',''),(2,'07/01/19','07/01/19','7509','Riverina Helicopters','Gerry','0427 874 233','Griffith NSW','','Northline','81NS0155','2','Zi-400 AV','20','1806A001','21','');
/*!40000 ALTER TABLE `despatch` ENABLE KEYS */;
UNLOCK TABLES;

