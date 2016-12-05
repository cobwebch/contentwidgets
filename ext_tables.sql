#
# Additional fields for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_contentwidgets_contenttype varchar(11) DEFAULT 'record' NOT NULL,
	tx_contentwidgets_recordelements varchar(255) DEFAULT '' NOT NULL,
	tx_contentwidgets_libelement varchar(255) DEFAULT '' NOT NULL,
	tx_contentwidgets_loadinglabel varchar(255) DEFAULT '' NOT NULL
);