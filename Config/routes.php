<?php

	Router::connect(
	    '/file/*',
	    array(
	    	'plugin' => 'DatabaseBlobFile',
	    	'controller' => 'BlobFile', 'action' => 'serve'
	    )
	);

