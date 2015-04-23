# Database Blob File

This plugin is in a very early stage and should not be used for production environment.

Store your uploads from CakePHP form helper directly in the database as binary object.

If you still consider that all uploaded files __have to be__ stored in the filesystem you can ignore this plugin. 

## Install

This Plugin requires CakePHP 2.

### Enable Plugin

Enable to plugin in your bootstrap.php file. Routes are required for serving uploaded files via http.

	CakePlugin::load('DatabaseBlobFile', array(
		'routes'    => true,
		'bootstrap' => true,
	));

### Create a BLOB column

Change or add an column in your database as `MEDIUMBLOB` (max. 16MB file size).

### Add the behavior to your Model

Tell the Model to load the behavior for saving files directly.

	public $actsAs = array(
		'DatabaseBlobFile.BlobFile' => array(
			'imageMaxWidth' => 1000,
		)
	);

You can set addional parameters like:

`imageMaxWidth` Resize uploaded images to have a maximum width. 

### Form

And make sure your form created with a correct type to allow file uploads at all.

	<?php echo $this->Form->create('Object',array('type'=>'file')); ?>

Add your form field with the form helper

	<?php echo $this->Form->input('column_name'); ?>

## Using the plugin

Because it is not necessary to transfer all files with every database select every time the model returns only the uploaded file size in byte. This can be usefull to decide if something has been uploaded at all.

You will have a new virtual field containing a base path to the file (will be replaced with a View helper function) in your database result.

	<?php echo $this->Html->image($model['Table']['columnname_basepath'].'size.jpg')

## Output images in different sizes

### resize

Maintains the aspect ration of the image and makes sure that it fits within the new max width and max height (thus some side will be smaller).

### resizefit

Maintains the aspect ration of the image and makes sure that it fits inside the new width and height. Extra space will be filled with white color.

### resizewidth

Maintains aspect ratio but resizes the image so that the Width has to fit. We don't care about the height.

### resizemin

Maintains aspect ratio but resizes the image so that once one side meets its maxW or maxH condition, it stays at that size (thus one side will be larger).

### crop

Simple center crop

### resizecrop

resize to max width and height, then crop to center

## Caching

Every file which has been served once will be stored inside the webroot for caching with a timestamp. This is supposed to work with a deployment processes which resets the app folder to its correct state with every deployment.

## Roadmap

- Support for more file types than just images
- More config options for caching and serving files (use subdomain, path and cdn)
