SET NAMES utf8mb4;

CREATE TABLE `mystmts2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_name` varchar(2000) NOT NULL,
  `preamble` varchar(2000) NOT NULL,
  `statement_txt` text NOT NULL,
  `img_file_name` varchar(255) NOT NULL DEFAULT 'default.png',
  `case_date` date NOT NULL,
  `case_doc_url` varchar(2000) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted` tinyint(1) unsigned zerofill NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
