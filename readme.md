# Database Blob File

This plugin is in a very early stage and should not be used for production environment.

Store your uploads from CakePHP form helper directly in the database as binary object.

If you still consider that all uploaded files __have to be__ stored in the filesystem you can ignore this plugin. 

## Install

### Enable Plugin

Enable to plugin in your bootstrap.php file. Routes are required for serving uploaded files via http.

	CakePlugin::load('DatabaseBlobFile', array(
		'routes'    => true,
		'bootstrap' => true,
	));

### Modify Database

Change or add an column as `MEDIUMBLOB`.

### Model

Tell the Model to load the behavior for saving files directly.

	public $actsAs = array(
		'DatabaseBlobFile.BlobFile' => array(
			'imageMaxWidth' => 1000,
		)
	);

You can set addional parameters like:

`imageMaxWidth` Resize uploaded images to have a maximum width. 

### Form

Add a form field

	<?php echo $this->Form->input('column_name'); ?>

And make sure your form is correct created to allow uploads.

	<?php echo $this->Form->create('Object',array('type'=>'file')); ?>

## Using the plugin

Because it is not necessary to transfer all files with every database select every time the model returns only the uploaded file size in byte. This can be usefull to decide if something has been uploaded at all.


You will have a new virtual field containing a base path to the file (will be replaced with a View helper function)

Every file which has been served once will be stored inside the webroot for caching with a timestamp. This is supposed to work with a deployment processes which resets the app folder to its correct state with every deployment.

## Roadmap

- Support for more file types than just images
- More config options for caching and serving files (use subdomain, path and cdn)
