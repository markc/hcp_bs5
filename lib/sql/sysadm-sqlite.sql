PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE m_forwards (
  `id` int(11) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE m_limits (
  `id` int(11) NOT NULL,
  `domain` varchar(128) NOT NULL,
  `maxaccounts` int(11) NOT NULL,
  `maxaccountsize` int(11) NOT NULL,
  `maxaccountcount` int(11) NOT NULL,
  `maxforwards` int(11) NOT NULL,
  `maxforwardsrcpt` int(11) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE m_users (
  `id` int(11) NOT NULL,
  `uid` char(128) NOT NULL DEFAULT '',
  `crypt` char(128) NOT NULL DEFAULT '',
  `clear` char(128) NOT NULL DEFAULT '',
  `name` char(128) NOT NULL DEFAULT '',
  `muid` int(10) NOT NULL DEFAULT '1000',
  `mgid` int(10) NOT NULL DEFAULT '1000',
  `mquota` varchar(16) NOT NULL DEFAULT '524288000S',
  `mpath` char(255) NOT NULL DEFAULT '',
  `maildir` char(255) NOT NULL DEFAULT '',
  `delivery` char(255) NOT NULL DEFAULT '',
  `options` char(255) NOT NULL DEFAULT '',
  `acl` tinyint(4) NOT NULL DEFAULT '1',
  `spam` tinyint(4) NOT NULL DEFAULT '1',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE m_vacations (
  `id` int(11) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `spacing` int(11) DEFAULT '10080',
  `active` tinyint(4) DEFAULT '0',
  `message` text,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE m_welcomes (
  `id` int(11) NOT NULL,
  `domain` varchar(128) NOT NULL,
  `deliver` tinyint(4) DEFAULT '0',
  `use_default` tinyint(4) DEFAULT '0',
  `process` tinyint(4) DEFAULT '1',
  `from_addr` varchar(128) NOT NULL,
  `from_name` varchar(128) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE s_users (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '1000',
  `gid` int(11) NOT NULL DEFAULT '1000',
  `username` varchar(16) NOT NULL DEFAULT '',
  `gecos` varchar(128) NOT NULL DEFAULT '',
  `homedir` varchar(255) NOT NULL DEFAULT '',
  `shell` varchar(64) NOT NULL DEFAULT '/bin/sh',
  `password` varchar(34) NOT NULL DEFAULT 'x',
  `groups` varchar(255) NOT NULL DEFAULT '',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE w_news (
  `id` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
CREATE TABLE w_users (
  `id` int(10) NOT NULL,
  `acl` tinyint(1) NOT NULL DEFAULT '0',
  `uid` varchar(31) NOT NULL,
  `fname` varchar(31) NOT NULL,
  `lname` varchar(31) NOT NULL,
  `altemail` varchar(63) NOT NULL,
  `webpw` varchar(255) NOT NULL,
  `otp` varchar(8) NOT NULL,
  `otpttl` int(10) NOT NULL,
  `cookie` varchar(255) NOT NULL,
  `anote` text NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
);
COMMIT;
