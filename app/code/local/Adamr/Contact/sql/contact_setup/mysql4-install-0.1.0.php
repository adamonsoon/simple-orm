<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE contacts(
id int AUTO_INCREMENT PRIMARY KEY,
name VARCHAR (255),
email VARCHAR (255)
)engine=InnoDB;

INSERT INTO contacts
(id, name, email)
VALUES
(1, "Alan Turing", "alan@turing.com"),
(2, "Ada Lovelace", "ada@lovelace.com");

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 