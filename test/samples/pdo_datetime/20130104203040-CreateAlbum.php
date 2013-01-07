<?php

class CreateAlbum extends \Phig\Migratable\Pdo
{
	public function up()
	{
		$query = 'CREATE TABLE album ( '
				.'id int(11) NOT NULL auto_increment, '
				.'artist varchar(100) NOT NULL, '
				.'title varchar(100) NOT NULL, '
				.'PRIMARY KEY (id) '
				.')';
		return $this->getConnection()->exec($query);
	}
	
	public function down()
	{
		$query = 'DROP TABLE album';
		return $this->getConnection()->exec($query);
	}
}
