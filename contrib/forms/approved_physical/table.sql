CREATE TABLE IF NOT EXISTS `form_approved_physical` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
`col_1` varchar(3) NOT NULL default '--',
`col_1_textbox` varchar(255) NOT NULL default '',
`col_2` varchar(3) NOT NULL default '--',
`col_2_textbox` varchar(255) NOT NULL default '',
`col_3` varchar(3) NOT NULL default '--',
`col_3_textbox` varchar(255) NOT NULL default '',
`col_4` varchar(3) NOT NULL default '--',
`col_4_textbox` varchar(255) NOT NULL default '',
`col_5` varchar(3) NOT NULL default '--',
`col_5_textbox` varchar(255) NOT NULL default '',
`col_6` varchar(3) NOT NULL default '--',
`col_6_textbox` varchar(255) NOT NULL default '',
`col_7` varchar(3) NOT NULL default '--',
`col_7_textbox` varchar(255) NOT NULL default '',
`col_8` varchar(3) NOT NULL default '--',
`col_8_textbox` varchar(255) NOT NULL default '',
`col_9` varchar(3) NOT NULL default '--',
`col_9_textbox` varchar(255) NOT NULL default '',
`col_10` varchar(3) NOT NULL default '--',
`col_10_textbox` varchar(255) NOT NULL default '',
`col_11` varchar(3) NOT NULL default '--',
`col_11_textbox` varchar(255) NOT NULL default '',
`col_12` varchar(3) NOT NULL default '--',
`col_12_textbox` varchar(255) NOT NULL default '',
`col_13` varchar(3) NOT NULL default '--',
`col_13_textbox` varchar(255) NOT NULL default '',
`col_14` varchar(3) NOT NULL default '--',
`col_14_textbox` varchar(255) NOT NULL default '',
`col_15` varchar(3) NOT NULL default '--',
`col_15_textbox` varchar(255) NOT NULL default '',
`col_16` varchar(3) NOT NULL default '--',
`col_16_textbox` varchar(255) NOT NULL default '',
`col_17` varchar(3) NOT NULL default '--',
`col_17_textbox` varchar(255) NOT NULL default '',
`col_18` varchar(3) NOT NULL default '--',
`col_18_textbox` varchar(255) NOT NULL default '',
`col_19` varchar(3) NOT NULL default '--',
`col_19_textbox` varchar(255) NOT NULL default '',
`col_20` varchar(3) NOT NULL default '--',
`col_20_textbox` varchar(255) NOT NULL default '',
`col_21` varchar(3) NOT NULL default '--',
`col_21_textbox` varchar(255) NOT NULL default '',
`col_22` varchar(3) NOT NULL default '--',
`col_22_textbox` varchar(255) NOT NULL default '',
`col_23` varchar(3) NOT NULL default '--',
`col_23_textbox` varchar(255) NOT NULL default '',
`col_24` varchar(3) NOT NULL default '--',
`col_24_textbox` varchar(255) NOT NULL default '',
`col_25` varchar(3) NOT NULL default '--',
`col_25_textbox` varchar(255) NOT NULL default '',
`col_26` varchar(3) NOT NULL default '--',
`col_26_textbox` varchar(255) NOT NULL default '',
`col_27` varchar(3) NOT NULL default '--',
`col_27_textbox` varchar(255) NOT NULL default '',
`col_28` varchar(3) NOT NULL default '--',
`col_28_textbox` varchar(255) NOT NULL default '',
`col_29` varchar(3) NOT NULL default '--',
`col_29_textbox` varchar(255) NOT NULL default '',
`col_30` varchar(3) NOT NULL default '--',
`col_30_textbox` varchar(255) NOT NULL default '',
`col_31` varchar(3) NOT NULL default '--',
`col_31_textbox` varchar(255) NOT NULL default '',
`col_32` varchar(3) NOT NULL default '--',
`col_32_textbox` varchar(255) NOT NULL default '',
`col_33` varchar(3) NOT NULL default '--',
`col_33_textbox` varchar(255) NOT NULL default '',
`col_34` varchar(3) NOT NULL default '--',
`col_34_textbox` varchar(255) NOT NULL default '',
`col_35` varchar(3) NOT NULL default '--',
`col_35_textbox` varchar(255) NOT NULL default '',
`col_36` varchar(3) NOT NULL default '--',
`col_36_textbox` varchar(255) NOT NULL default '',
`col_37` varchar(3) NOT NULL default '--',
`col_37_textbox` varchar(255) NOT NULL default '',
`col_38` varchar(3) NOT NULL default '--',
`col_38_textbox` varchar(255) NOT NULL default '',
`col_39` varchar(3) NOT NULL default '--',
`col_39_textbox` varchar(255) NOT NULL default '',
`col_40` varchar(3) NOT NULL default '--',
`col_40_textbox` varchar(255) NOT NULL default '',
`col_41` varchar(3) NOT NULL default '--',
`col_41_textbox` varchar(255) NOT NULL default '',
`col_42` varchar(3) NOT NULL default '--',
`col_42_textbox` varchar(255) NOT NULL default '',
`col_43` varchar(3) NOT NULL default '--',
`col_43_textbox` varchar(255) NOT NULL default '',
`col_44` varchar(3) NOT NULL default '--',
`col_44_textbox` varchar(255) NOT NULL default '',
`col_45` varchar(3) NOT NULL default '--',
`col_45_textbox` varchar(255) NOT NULL default '',
`col_46` varchar(3) NOT NULL default '--',
`col_46_textbox` varchar(255) NOT NULL default '',
`col_47` varchar(3) NOT NULL default '--',
`col_47_textbox` varchar(255) NOT NULL default '',
`col_48` varchar(3) NOT NULL default '--',
`col_48_textbox` varchar(255) NOT NULL default '',
`col_49` varchar(3) NOT NULL default '--',
`col_49_textbox` varchar(255) NOT NULL default '',
`col_50` varchar(3) NOT NULL default '--',
`col_50_textbox` varchar(255) NOT NULL default '',
`col_51` varchar(3) NOT NULL default '--',
`col_51_textbox` varchar(255) NOT NULL default '',
`col_52` varchar(3) NOT NULL default '--',
`col_52_textbox` varchar(255) NOT NULL default '',
`col_53` varchar(3) NOT NULL default '--',
`col_53_textbox` varchar(255) NOT NULL default '',
`col_54` varchar(3) NOT NULL default '--',
`col_54_textbox` varchar(255) NOT NULL default '',
`col_55` varchar(3) NOT NULL default '--',
`col_55_textbox` varchar(255) NOT NULL default '',
`col_56` varchar(3) NOT NULL default '--',
`col_56_textbox` varchar(255) NOT NULL default '',
`col_57` varchar(3) NOT NULL default '--',
`col_57_textbox` varchar(255) NOT NULL default '',
`col_58` varchar(3) NOT NULL default '--',
`col_58_textbox` varchar(255) NOT NULL default '',
`col_59` varchar(3) NOT NULL default '--',
`col_59_textbox` varchar(255) NOT NULL default '',
`col_60` varchar(3) NOT NULL default '--',
`col_60_textbox` varchar(255) NOT NULL default '',
`col_61` varchar(3) NOT NULL default '--',
`col_61_textbox` varchar(255) NOT NULL default '',
`col_62` varchar(3) NOT NULL default '--',
`col_62_textbox` varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) TYPE=InnoDB;
