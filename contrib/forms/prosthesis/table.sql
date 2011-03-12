CREATE TABLE `form_prosthesis` (
  `id` int(11) NOT NULL auto_increment,
  `pid` int(11) default NULL,
  `activity` tinyint(4) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `therapist` varchar(255) default NULL,
  `involvement_left` tinyint(1) default NULL,
  `involvement_right` tinyint(1) default NULL,
  `involvement_bilateral` tinyint(1) default NULL,
  `location` varchar(40) default NULL,
  `diagnosis` varchar(255) default NULL,
  `hx` varchar(255) default NULL,
  `worn_le_past_five` tinyint(1) default NULL,
  `model` varchar(30) default NULL,
  `size` varchar(35) default NULL,
  `new` tinyint(1) default NULL,
  `replacement` tinyint(1) default NULL,
  `foam_impressions` tinyint(1) default NULL,
  `shoe_size` varchar(15) default NULL,
  `calf` varchar(10) default NULL,
  `ankle` varchar(10) default NULL,
  `purpose` varchar(30) default NULL,
  `purpose_other` varchar(255) default NULL,
  `notes` varchar(255) default NULL,
  `goals_discussed` tinyint(1) default NULL,
  `use_reviewed` tinyint(1) default NULL,
  `wear_reviewed` tinyint(1) default NULL,
  `worn_years` tinyint(3) default NULL,
  `age_months` tinyint(2) default NULL,
  `age_years` tinyint(2) default NULL,
  `wear_hours` tinyint(2) default NULL,
  `plan_to_order` tinyint(1) default NULL,
  `plan_to_order_date` varchar(10) default NULL,
  `received_product` tinyint(1) default NULL,
  `received_product_date` varchar(10) default NULL,
  `given_instructions` tinyint(1) default NULL,
  `patient_understands` tinyint(1) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;