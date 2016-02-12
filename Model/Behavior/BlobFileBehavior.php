<?php 

App::uses('BlobFileHandler','DatabaseBlobFile.Lib');

class BlobFileBehavior extends ModelBehavior {

	public $config = array(
		'imageMaxWidth' => '1000',
	);

	public function setup(Model $model, $config = array()) {
		$this->config = am($this->config, $config);
	}

	public function beforeFind(Model $model, $query) {

		if (isset($query['fields']) AND ! empty($query['fields'])) {
			return true;
		}

		$columns = $model->getColumnTypes();

		foreach ($columns as $field => $type) {
			if ($type == 'binary') {
				$cdnHost = Configure::read('DatabaseBlobFile.cdn_host');
				$model->virtualFields[$field] = 'LENGTH('.$model->name.'.'.$field.')';
				$model->virtualFields[$field.'_basepath'] = "CONCAT('".$cdnHost."/file/','".$model->name."','/',".$model->name.'.'.$model->primaryKey.",'/','".$field."','/', date_format(".$model->name.".modified,'%y%m%d%h%i'))";
			}
		}
		return $query;
	}
	
	public function beforeSave(Model $model, $options = array()) {

		$columns = $model->getColumnTypes();

		foreach ($model->data as $modelClass => $values) {

			foreach ($values as $field => $value) {

				if ( ! isset($columns[$field]) or $columns[$field] != 'binary') continue;

				if (is_array($value) 
					and isset($value['size'])) {

					if ($value["size"] > 0) {

						$fileHandler  = new BlobFileHandler;
						$fileHandler->loadFromFile($value['tmp_name']);
						if ($fileHandler->getImageWith() > $this->config['imageMaxWidth']) {
							$fileHandler->modify('resize',$this->config['imageMaxWidth']); // max image size
							$fileData = $fileHandler->store(null,$fileHandler->resourceInfo[2],90);
						} else {
							$fileData = file_get_contents($value['tmp_name']);
						}

						$model->data[$modelClass][$field] = $fileData;
					}
					else {
						unset($model->data[$modelClass][$field]);	
					}
				}
			}
		}

		return true;
	}

	public function isUploadedFileImage($model,$params) {

		$val = array_shift($params);

		if ( ! empty($val['error'])) {
			
			// no file uploaded
			if ($val['error'] == 4) {
				return true;
			}

			return $this->errorCodeToMessage($val['error']);
		}

		if ( ! empty($val['name']) and empty($val['tmp_name'])) {
			return false;
		}

		if (empty($val['tmp_name'])) {
			return true; // allow empty value
		}

		$fileHandler = new BlobFileHandler;
		$fileHandler->loadFromFile($val['tmp_name']);

		return $fileHandler->isImage();
	}

	protected function errorCodeToMessage($code) 
	{ 
		switch ($code) { 
			case UPLOAD_ERR_INI_SIZE: 
				$message = "The uploaded file exceeds the maximum allowed upload size of ".ini_get('upload_max_filesize').'.'; // upload_max_filesize directive in php.ini
				break; 
			case UPLOAD_ERR_FORM_SIZE: 
				$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break; 
			case UPLOAD_ERR_PARTIAL: 
				$message = "The uploaded file was only partially uploaded"; 
				break; 
			case UPLOAD_ERR_NO_FILE: 
				$message = "No file was uploaded"; 
				break; 
			case UPLOAD_ERR_NO_TMP_DIR: 
				$message = "Missing a temporary folder"; 
				break; 
			case UPLOAD_ERR_CANT_WRITE: 
				$message = "Failed to write file to disk"; 
				break; 
			case UPLOAD_ERR_EXTENSION: 
				$message = "File upload stopped by extension"; 
				break; 

			default: 
				$message = "Unknown upload error"; 
				break; 
		} 
		return $message; 
	}

}
