# phpMyAdmin MySQL-Dump
# version 2.4.0
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Dec 19, 2003 at 04:38 PM
# Server version: 3.23.49
# PHP Version: 4.3.4
# Database : `pear`
# --------------------------------------------------------

#
# Table structure for table `package_proposal_changelog`
#

CREATE TABLE package_proposal_changelog (
  pkg_prop_id int(11) NOT NULL default '0',
  timestamp timestamp(14) NOT NULL,
  user_handle varchar(255) NOT NULL default '',
  comment text
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `package_proposal_links`
#

CREATE TABLE package_proposal_links (
  pkg_prop_id int(11) NOT NULL default '0',
  type enum('pkg_file','pkg_source','pkg_example','pkg_example_source','pkg_doc','Package Related') NOT NULL default 'pkg_file',
  url varchar(255) NOT NULL default ''
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `package_proposal_votes`
#

CREATE TABLE package_proposal_votes (
  pkg_prop_id int(11) NOT NULL default '0',
  user_handle varchar(255) NOT NULL default '',
  value tinyint(1) NOT NULL default '1',
  reviews text NOT NULL,
  is_conditional tinyint(1) NOT NULL default '0',
  comment text NOT NULL,
  timestamp timestamp(14) NOT NULL,
  PRIMARY KEY  (pkg_prop_id,user_handle)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `package_proposals`
#

CREATE TABLE package_proposals (
  id int(11) NOT NULL auto_increment,
  pkg_category varchar(255) NOT NULL default '',
  pkg_name varchar(255) NOT NULL default '',
  pkg_describtion text NOT NULL,
  pkg_deps text NOT NULL,
  draft_date datetime NOT NULL default '0000-00-00 00:00:00',
  proposal_date datetime NOT NULL default '0000-00-00 00:00:00',
  vote_date datetime NOT NULL default '0000-00-00 00:00:00',
  longened_date datetime NOT NULL default '0000-00-00 00:00:00',
  status enum('draft','proposal','vote','finished') NOT NULL default 'draft',
  user_handle varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;