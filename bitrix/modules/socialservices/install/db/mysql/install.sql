CREATE TABLE b_socialservices_user
(
	ID INT NOT NULL AUTO_INCREMENT,
	LOGIN VARCHAR(100) NOT NULL,
	NAME VARCHAR(100) NULL,
	LAST_NAME VARCHAR(100) NULL,
	EMAIL VARCHAR(100) NULL,
	PERSONAL_PHOTO INT NULL,
	EXTERNAL_AUTH_ID VARCHAR(100) NOT NULL,
	USER_ID INT NOT NULL,
	XML_ID VARCHAR(100) NOT NULL,
	CAN_DELETE CHAR(1) NOT NULL DEFAULT 'Y',
	PERSONAL_WWW VARCHAR(100) NULL,
	PERMISSIONS VARCHAR(555) NULL,
	OATOKEN VARCHAR(1000) NULL,
	OATOKEN_EXPIRES INT NULL,
	OASECRET VARCHAR(250) NULL,
	REFRESH_TOKEN VARCHAR(1000) NULL,
	SEND_ACTIVITY CHAR(1) NULL DEFAULT 'Y',
	SITE_ID VARCHAR(50) NULL,
	INITIALIZED CHAR(1) NULL DEFAULT 'N',
	PRIMARY KEY (ID),
	UNIQUE INDEX IX_B_SOCIALSERVICES_USER (XML_ID ASC, EXTERNAL_AUTH_ID ASC)
);
CREATE INDEX IX_B_SOCIALSERVICES_US_1 ON b_socialservices_user(USER_ID);
CREATE INDEX IX_B_SOCIALSERVICES_US_2 ON b_socialservices_user(INITIALIZED);

CREATE TABLE b_socialservices_message
(
	ID INT NOT NULL AUTO_INCREMENT,
	USER_ID INT NOT NULL,
	SOCSERV_USER_ID INT NOT NULL,
	PROVIDER VARCHAR(100) NOT NULL,
	MESSAGE VARCHAR(1000) NULL,
	INSERT_DATE DATETIME NULL,
	SUCCES_SENT CHAR(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID)
);

CREATE TABLE b_socialservices_user_link
(
	ID INT NOT NULL AUTO_INCREMENT,
	USER_ID INT NOT NULL,
	SOCSERV_USER_ID INT NOT NULL,
	LINK_USER_ID INT NULL,
	LINK_UID VARCHAR(100) NOT NULL,
	LINK_NAME VARCHAR(255) NULL,
	LINK_LAST_NAME VARCHAR(255) NULL,
	LINK_PICTURE VARCHAR(255) NULL,
	PRIMARY KEY(ID)
);

