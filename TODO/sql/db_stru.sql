SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `admin_config` (
  `AID` smallint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `begin_dt` datetime DEFAULT NULL,
  `end_dt` datetime DEFAULT NULL,
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `major` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `article_favorite` (
  `UID` mediumint NOT NULL,
  `AID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `ban_user_list` (
  `BID` smallint NOT NULL,
  `SID` smallint NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `day` smallint NOT NULL DEFAULT '0',
  `ban_UID` mediumint NOT NULL DEFAULT '0',
  `ban_dt` datetime DEFAULT NULL,
  `ban_ip` varchar(20) DEFAULT NULL,
  `unban_UID` mediumint NOT NULL DEFAULT '0',
  `unban_dt` datetime DEFAULT NULL,
  `unban_ip` varchar(20) DEFAULT NULL,
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `reason` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bbs` (
  `AID` int NOT NULL,
  `SID` smallint NOT NULL DEFAULT '0',
  `TID` mediumint NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `username` varchar(20) DEFAULT NULL,
  `nickname` varchar(20) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `CID` mediumint NOT NULL DEFAULT '0',
  `sub_dt` datetime DEFAULT NULL,
  `sub_ip` varchar(20) DEFAULT NULL,
  `reply_note` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `visible` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `view_count` smallint NOT NULL DEFAULT '0',
  `exp` mediumint NOT NULL DEFAULT '0',
  `last_reply_dt` datetime DEFAULT NULL,
  `last_reply_UID` mediumint NOT NULL DEFAULT '0',
  `last_reply_username` varchar(20) DEFAULT NULL,
  `last_reply_nickname` varchar(20) DEFAULT NULL,
  `transship` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `lock` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `reply_count` smallint NOT NULL DEFAULT '0',
  `icon` smallint DEFAULT NULL,
  `length` mediumint NOT NULL DEFAULT '0',
  `excerption` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ontop` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `static` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `gen_ex` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `m_del` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `old_SID` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bbs_article_op` (
  `MID` int NOT NULL,
  `AID` int NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `op_dt` datetime DEFAULT NULL,
  `op_ip` varchar(20) DEFAULT NULL,
  `complete` tinyint UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bbs_content` (
  `CID` int NOT NULL,
  `AID` int NOT NULL DEFAULT '0',
  `content` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `bbs_msg` (
  `MID` int NOT NULL,
  `fromUID` mediumint NOT NULL DEFAULT '0',
  `toUID` mediumint NOT NULL DEFAULT '0',
  `content` longtext,
  `send_dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `send_ip` varchar(20) NOT NULL DEFAULT '',
  `new` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `s_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `email` (
  `ID` int NOT NULL,
  `fromemail` varchar(30) DEFAULT NULL,
  `fromname` varchar(30) DEFAULT NULL,
  `toemail` varchar(30) DEFAULT NULL,
  `toname` varchar(30) DEFAULT NULL,
  `subject` varchar(80) DEFAULT NULL,
  `body` longtext,
  `set_dt` datetime DEFAULT NULL,
  `send_dt` datetime DEFAULT NULL,
  `complete` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `error` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `error_msg` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `ex_dir` (
  `FID` mediumint NOT NULL,
  `dir` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `SID` smallint NOT NULL DEFAULT '0',
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `ex_file` (
  `AID` int NOT NULL DEFAULT '0',
  `FID` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `friend_list` (
  `ID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `fUID` mediumint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `section_class` (
  `CID` smallint NOT NULL,
  `cname` varchar(20) DEFAULT NULL,
  `title` varchar(20) DEFAULT NULL,
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `sort_order` smallint NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `section_config` (
  `SID` smallint NOT NULL,
  `sname` varchar(20) DEFAULT NULL,
  `CID` smallint NOT NULL DEFAULT '0',
  `title` varchar(20) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `topic_retention` smallint NOT NULL DEFAULT '0',
  `announcement` longtext,
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `exp_get` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `recommend` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `set_UID` mediumint DEFAULT '0',
  `set_dt` datetime DEFAULT NULL,
  `set_ip` varchar(20) DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '10',
  `ex_gen_tm` datetime DEFAULT NULL,
  `ex_update` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ex_menu_tm` datetime DEFAULT NULL,
  `ex_menu_update` tinyint NOT NULL DEFAULT '0',
  `read_user_level` smallint NOT NULL DEFAULT '0',
  `write_user_level` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `section_favorite` (
  `ID` int NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `SID` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `section_master` (
  `MID` smallint NOT NULL,
  `SID` smallint NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `begin_dt` datetime DEFAULT NULL,
  `end_dt` datetime DEFAULT NULL,
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `major` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `memo` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `send_pass_log` (
  `ID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `upload_file` (
  `AID` mediumint NOT NULL,
  `ref_AID` mediumint NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `size` mediumint NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `check` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `deny` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `deleted` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_err_login_log` (
  `ID` int NOT NULL,
  `username` varchar(14) DEFAULT NULL,
  `password` varchar(12) DEFAULT NULL,
  `login_dt` datetime DEFAULT NULL,
  `login_ip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_life_log` (
  `ID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `set_UID` mediumint NOT NULL DEFAULT '0',
  `life` smallint NOT NULL DEFAULT '0',
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_list` (
  `UID` mediumint NOT NULL,
  `username` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `temp_password` varchar(20) NOT NULL DEFAULT '',
  `enable` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `verified` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `p_login` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `p_post` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `p_msg` tinyint UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_login_log` (
  `ID` int NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `login_dt` datetime DEFAULT NULL,
  `login_ip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_modify_email_verify` (
  `MID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `email` varchar(30) DEFAULT NULL,
  `verify_code` varchar(10) NOT NULL DEFAULT '',
  `complete` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `dt` datetime DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_modify_log` (
  `MID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `modify_dt` datetime DEFAULT NULL,
  `modify_ip` varchar(20) DEFAULT NULL,
  `complete` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_nickname` (
  `NID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `nickname` varchar(20) NOT NULL DEFAULT '',
  `begin_dt` datetime DEFAULT NULL,
  `begin_reason` char(1) DEFAULT NULL,
  `end_dt` datetime DEFAULT NULL,
  `end_reason` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_online` (
  `SID` varchar(32) NOT NULL DEFAULT '',
  `UID` mediumint NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `current_action` varchar(20) NOT NULL DEFAULT '',
  `login_tm` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_tm` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_pubinfo` (
  `UID` mediumint NOT NULL DEFAULT '0',
  `nickname` varchar(20) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `gender` char(1) NOT NULL DEFAULT 'M',
  `qq` varchar(10) DEFAULT NULL,
  `introduction` mediumtext,
  `photo` smallint DEFAULT '0',
  `photo_enable` tinyint(1) NOT NULL DEFAULT '0',
  `photo_ext` varchar(5) NOT NULL DEFAULT '',
  `life` smallint NOT NULL DEFAULT '15',
  `exp` mediumint DEFAULT '0',
  `visit_count` mediumint NOT NULL DEFAULT '0',
  `gender_pub` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `last_login_dt` datetime DEFAULT NULL,
  `sign_1` mediumtext,
  `sign_2` mediumtext,
  `sign_3` mediumtext,
  `upload_limit` int NOT NULL DEFAULT '1048576',
  `login_notify_dt` datetime DEFAULT NULL,
  `user_timezone` varchar(50) NOT NULL DEFAULT '',
  `game_money` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_reginfo` (
  `UID` mediumint NOT NULL DEFAULT '0',
  `name` varchar(10) DEFAULT NULL,
  `birthday` datetime DEFAULT NULL,
  `signup_dt` datetime DEFAULT NULL,
  `signup_ip` varchar(20) DEFAULT NULL,
  `memo` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_score` (
  `UID` mediumint NOT NULL DEFAULT '0',
  `score` mediumint NOT NULL DEFAULT '0',
  `last_exp` mediumint NOT NULL DEFAULT '0',
  `exp_left` mediumint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user_score_log` (
  `ID` mediumint NOT NULL,
  `UID` mediumint NOT NULL DEFAULT '0',
  `score_change` mediumint NOT NULL DEFAULT '0',
  `reason` varchar(50) DEFAULT NULL,
  `dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `view_article_log` (
  `AID` int NOT NULL DEFAULT '0',
  `UID` mediumint NOT NULL DEFAULT '0',
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `visit_log` (
  `VID` int NOT NULL,
  `dt` datetime DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `admin_config`
  ADD PRIMARY KEY (`AID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `article_favorite`
  ADD PRIMARY KEY (`UID`,`AID`),
  ADD KEY `AID` (`AID`);

ALTER TABLE `ban_user_list`
  ADD PRIMARY KEY (`BID`),
  ADD KEY `ban_UID` (`ban_UID`),
  ADD KEY `SID` (`SID`),
  ADD KEY `day` (`day`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `bbs`
  ADD PRIMARY KEY (`AID`),
  ADD UNIQUE KEY `CID` (`CID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `reply_count` (`reply_count`),
  ADD KEY `last_reply_dt` (`last_reply_dt`),
  ADD KEY `view_count` (`view_count`),
  ADD KEY `sub_dt` (`sub_dt`),
  ADD KEY `title` (`title`(10)),
  ADD KEY `old_SID` (`old_SID`),
  ADD KEY `SID` (`SID`),
  ADD KEY `TID` (`TID`),
  ADD KEY `last_reply_UID` (`last_reply_UID`);

ALTER TABLE `bbs_article_op`
  ADD PRIMARY KEY (`MID`),
  ADD KEY `AID` (`AID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `type` (`type`);

ALTER TABLE `bbs_content`
  ADD PRIMARY KEY (`CID`),
  ADD KEY `AID` (`AID`);

ALTER TABLE `bbs_msg`
  ADD PRIMARY KEY (`MID`),
  ADD KEY `fromUID` (`fromUID`),
  ADD KEY `toUID` (`toUID`);

ALTER TABLE `email`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `complete` (`complete`);

ALTER TABLE `ex_dir`
  ADD PRIMARY KEY (`FID`),
  ADD KEY `SID` (`SID`),
  ADD KEY `dir` (`dir`(50));

ALTER TABLE `ex_file`
  ADD PRIMARY KEY (`AID`),
  ADD KEY `FID` (`FID`);

ALTER TABLE `friend_list`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fUID` (`fUID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `section_class`
  ADD PRIMARY KEY (`CID`),
  ADD KEY `sort_order` (`sort_order`);

ALTER TABLE `section_config`
  ADD PRIMARY KEY (`SID`),
  ADD KEY `CID` (`CID`),
  ADD KEY `write_user_level` (`write_user_level`),
  ADD KEY `sort_order` (`sort_order`),
  ADD KEY `read_user_level` (`read_user_level`),
  ADD KEY `sname` (`sname`);

ALTER TABLE `section_favorite`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `section_master`
  ADD PRIMARY KEY (`MID`),
  ADD KEY `SID` (`SID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `send_pass_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `upload_file`
  ADD PRIMARY KEY (`AID`),
  ADD KEY `check` (`check`),
  ADD KEY `UID` (`UID`),
  ADD KEY `ref_AID` (`ref_AID`);

ALTER TABLE `user_err_login_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `login_dt` (`login_dt`,`login_ip`);

ALTER TABLE `user_life_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `set_UID` (`set_UID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `user_list`
  ADD PRIMARY KEY (`UID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `verified` (`verified`),
  ADD KEY `enable` (`enable`);

ALTER TABLE `user_login_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `login_dt` (`login_dt`);

ALTER TABLE `user_modify_email_verify`
  ADD PRIMARY KEY (`MID`),
  ADD UNIQUE KEY `verify_code` (`verify_code`),
  ADD KEY `UID` (`UID`),
  ADD KEY `complete` (`complete`);

ALTER TABLE `user_modify_log`
  ADD PRIMARY KEY (`MID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `user_nickname`
  ADD PRIMARY KEY (`NID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `nickname` (`nickname`),
  ADD KEY `begin_dt` (`begin_dt`),
  ADD KEY `end_dt` (`end_dt`);

ALTER TABLE `user_online`
  ADD PRIMARY KEY (`SID`) USING BTREE,
  ADD KEY `login_tm` (`login_tm`),
  ADD KEY `UID` (`UID`),
  ADD KEY `last_tm` (`last_tm`);

ALTER TABLE `user_pubinfo`
  ADD PRIMARY KEY (`UID`),
  ADD UNIQUE KEY `nickname` (`nickname`),
  ADD KEY `life` (`life`),
  ADD KEY `login_notify_dt` (`login_notify_dt`),
  ADD KEY `exp` (`exp`),
  ADD KEY `last_login_dt` (`last_login_dt`);

ALTER TABLE `user_reginfo`
  ADD PRIMARY KEY (`UID`);

ALTER TABLE `user_score`
  ADD PRIMARY KEY (`UID`),
  ADD KEY `score` (`score`);

ALTER TABLE `user_score_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UID` (`UID`);

ALTER TABLE `view_article_log`
  ADD PRIMARY KEY (`AID`,`UID`),
  ADD UNIQUE KEY `UID` (`UID`,`AID`),
  ADD KEY `dt` (`dt`);

ALTER TABLE `visit_log`
  ADD PRIMARY KEY (`VID`);


ALTER TABLE `admin_config`
  MODIFY `AID` smallint NOT NULL AUTO_INCREMENT;

ALTER TABLE `ban_user_list`
  MODIFY `BID` smallint NOT NULL AUTO_INCREMENT;

ALTER TABLE `bbs`
  MODIFY `AID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `bbs_article_op`
  MODIFY `MID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `bbs_content`
  MODIFY `CID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `bbs_msg`
  MODIFY `MID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `email`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ex_dir`
  MODIFY `FID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `friend_list`
  MODIFY `ID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `section_class`
  MODIFY `CID` smallint NOT NULL AUTO_INCREMENT;

ALTER TABLE `section_config`
  MODIFY `SID` smallint NOT NULL AUTO_INCREMENT;

ALTER TABLE `section_favorite`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `section_master`
  MODIFY `MID` smallint NOT NULL AUTO_INCREMENT;

ALTER TABLE `send_pass_log`
  MODIFY `ID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `upload_file`
  MODIFY `AID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_err_login_log`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_life_log`
  MODIFY `ID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_list`
  MODIFY `UID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_login_log`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_modify_email_verify`
  MODIFY `MID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_modify_log`
  MODIFY `MID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_nickname`
  MODIFY `NID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_score_log`
  MODIFY `ID` mediumint NOT NULL AUTO_INCREMENT;

ALTER TABLE `visit_log`
  MODIFY `VID` int NOT NULL AUTO_INCREMENT;
