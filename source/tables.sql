--
-- Table structure for table `webchat_lines`
--

CREATE TABLE `webchat_lines` (
  `id`       INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `author`   VARCHAR(16)      NOT NULL,
  `gravatar` VARCHAR(32)      NOT NULL,
  `text`     VARCHAR(255)     NOT NULL,
  `ts`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `webchat_users`
--

CREATE TABLE `webchat_users` (
  `id`            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(16)      NOT NULL,
  `gravatar`      VARCHAR(32)      NOT NULL,
  `last_activity` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `last_activity` (`last_activity`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8;
