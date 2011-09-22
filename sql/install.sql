CREATE TABLE IF NOT EXISTS `dtb_questionnaire` (
  `questionnaire_id` int(11) NOT NULL,
  `rank` int(11) DEFAULT NULL,
  `title` text NOT NULL,
  `contents` text,
  `work` smallint(6) NOT NULL DEFAULT '0',
  `question` text DEFAULT NULL,
  `creator_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `del_flg` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`questionnaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アンケート';

CREATE TABLE IF NOT EXISTS `dtb_questionnaire_questionnaire_id_seq` (
  `sequence` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `dtb_questionnaire_result` (
  `result_id` int(11) NOT NULL,
  `questionnaire_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `del_flg` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アンケート結果';


CREATE TABLE IF NOT EXISTS `dtb_questionnaire_result_answer` (
  `result_id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (result_id, answer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='アンケート答え';
