#
# Table structure for table 'pages_tx_cagrelatedcontent_category_mm'
# 
#
CREATE TABLE pages_tx_cagrelatedcontent_category_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'pages_tx_cagrelatedcontent_pages_mm'
# 
#
CREATE TABLE pages_tx_cagrelatedcontent_pages_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_cagrelatedcontent_category int(11) DEFAULT '0' NOT NULL,
	tx_cagrelatedcontent_pages int(11) DEFAULT '0' NOT NULL
);


#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_cagrelatedcontent_categories int(11) DEFAULT '0' NOT NULL
);


#
# Table structure for table 'tt_content_tx_cagrelatedcontent_categories_mm'
# 
#
CREATE TABLE tt_content_tx_cagrelatedcontent_categories_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);





#
# Table structure for table 'tx_cagrelatedcontent_category'
#
CREATE TABLE tx_cagrelatedcontent_category (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l18n_parent int(11) DEFAULT '0' NOT NULL,
    l18n_diffsource mediumblob NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,

    sorting int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_cagrelatedcontent_newscat_relcontentcat'
#
CREATE TABLE tx_cagrelatedcontent_newscat_relcontentcat (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '[Relation Title]' NOT NULL,
    news_category text,
    relcontent_cat text,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);


