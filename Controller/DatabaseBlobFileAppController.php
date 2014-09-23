<?php

class DatabaseBlobFileAppController extends AppController {
	
	public function beforeFilter(){
		
		parent::beforeFilter();

		$this->Auth->allow();
	}
}