create extension IF NOT EXISTS lo;
CREATE SEQUENCE #__doc_seq_id;

CREATE TABLE "#__docstore_doc" (
	id int4 NOT NULL DEFAULT nextval('#__doc_seq_id'::regclass),
	state int4 NULL DEFAULT 1,
	"ordering" int4 NULL,
	checked_out int4 NULL,
	checked_out_time timestamp NULL,
	created_by int4 NULL,
	modified_by int4 NULL,
	name varchar(255) NULL,
	catid int4 NULL,
	file_size int4 NULL,
	ext varchar(4) NULL,
	md5 varchar(100) NULL,
	publish_up timestamp NOT NULL,
	publish_down timestamp NULL,
	fulltext text NULL,
	"version" varchar(20) NULL,
	docdate date NULL,
	srcurl varchar(250) NULL,
	filecontent lo NULL,
	"access" int4 DEFAULT NULL,
	CONSTRAINT #__docstore_doc_pk PRIMARY KEY (id)
);

create trigger #__filecontent before
delete or update on  #__docstore_doc for each row execute function lo_manage('filecontent');