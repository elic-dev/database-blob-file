<?php
/**
 * configuration file
 *
 * PHP 5
 * CakePHP 2
 *
 */
Configure::write(
    'DatabaseBlobFile',
    array(
        'sizes' => array(
			'md1' => array('resizewidth',67),
			'md2' => array('resizewidth',165),
			'md3' => array('resizewidth',263),
			'md4' => array('resizewidth',360),
			'md5' => array('resizewidth',458),
			'md6' => array('resizewidth',555),
			'md7' => array('resizewidth',653),
			'md8' => array('resizewidth',750),
			'md9' => array('resizewidth',858),
			'md10' => array('resizewidth',945),
			'md11' => array('resizewidth',1043),
			'md12' => array('resizewidth',1170),
		),
    )
);