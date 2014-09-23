<?php

App::uses('DatabaseBlobFileAppController', 'DatabaseBlobFile.Controller');
App::uses('BlobFileHandler','DatabaseBlobFile.Lib');

class BlobFileController extends DatabaseBlobFileAppController {

	public function serve($model = null, $id = null, $field = null, $size = null) {

		if (is_null($model)
			or is_null($id)
			or is_null($field)
			or is_null($size)) {
			throw new NotFoundException;
		}

		$this->loadModel($model);

		// remove modified
		$modified = substr($size, 0,10);
		$size     = substr($size, 10);

		$dataset = $this->{$model}->find('first',array(
			'conditions' => array(
				$this->{$model}->primaryKey => intval($id)
			),
			'fields' => array($field),
		));

		$fileHandler = new BlobFileHandler;
		$fileHandler->loadFromString($dataset[$model][$field]);

		if ( ! $fileHandler->isImage()) {
			throw new NotFoundException('not an image');
		}

		$sizes = Configure::read('DatabaseBlobFile.sizes');

		if (isset($sizes[pathinfo($size,PATHINFO_FILENAME)])) {
			$fileHandler->modify(
				$sizes[pathinfo($size,PATHINFO_FILENAME)][0],
				$sizes[pathinfo($size,PATHINFO_FILENAME)][1]);
		}

		switch (pathinfo($size,PATHINFO_EXTENSION)) {
			case 'jpg':
			case 'jpeg':
				$file = $fileHandler->store(null,IMAGETYPE_JPEG,90);
				$fileType = 'image/jpeg';
			break;
			case 'png':
				$file = $fileHandler->store(null,IMAGETYPE_PNG);
				$fileType = 'image/png';
			break;
			case 'gif':
				$file = $fileHandler->store(null,IMAGETYPE_GIF);
				$fileType = 'image/gif';
			break;
			default:
				throw new NotFoundException('invalid extentions');
		}

		// copy file to webroot for better caching
		if ( ! is_dir(WWW_ROOT.pathinfo($this->request->url,PATHINFO_DIRNAME))) {
			mkdir(WWW_ROOT.pathinfo($this->request->url,PATHINFO_DIRNAME),0777,true);
		}
		file_put_contents(WWW_ROOT.$this->request->url, $file);

		$this->response->body($file);
	    $this->response->type($fileType);

	    return $this->response;
	}

}
