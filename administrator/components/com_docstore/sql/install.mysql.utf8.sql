CREATE TABLE IF NOT EXISTS `#__docstore_doc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` tinyint(1) DEFAULT 1,
  `ordering` int(11) DEFAULT 0,
  `checked_out` int(11) unsigned DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT 0,
  `modified_by` int(11) DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `catid` int(11) DEFAULT NULL COMMENT 'Категория',
  `file_size` int(11) unsigned DEFAULT NULL,
  `ext` varchar(4) DEFAULT NULL,
  `md5` varchar(100) DEFAULT NULL,
  `publish_up` timestamp NOT NULL DEFAULT current_timestamp(),
  `publish_down` timestamp NULL DEFAULT NULL,
  `fulltext` longtext DEFAULT NULL COMMENT 'Текстовое содержание',
  `version` varchar(20) DEFAULT NULL COMMENT 'Номер (версия) документа',
  `docdate` date DEFAULT NULL COMMENT 'Дата документв',
  `srcurl` varchar(250) DEFAULT NULL COMMENT 'Ссылка на источник',
  `access` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__docstore_chunk` (
  `doc_id` int(11) unsigned NOT NULL,
  `ord` int(11) unsigned NOT NULL DEFAULT 0,
  `chunk` mediumblob NOT NULL,
  PRIMARY KEY (`doc_id`,`ord`),
  CONSTRAINT `#__docstore_chunk_FK` FOREIGN KEY (`doc_id`) REFERENCES `#__docstore_doc` (`id`) ON DELETE CASCADE
) DEFAULT COLLATE=utf8mb3_general_ci;

