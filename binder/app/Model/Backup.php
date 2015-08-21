<?php
/**
 * Class User
 */
class Backup extends AppModel {

    public $name = null;


	public function getDbc() {
		return ConnectionManager::getDataSource($this->useDbConfig);
	}
}


