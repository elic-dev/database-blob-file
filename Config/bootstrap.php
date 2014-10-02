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
			'md1' => array('resize',67),
			'md2' => array('resize',165),
			'md3' => array('resize',263),
			'md4' => array('resize',360),
			'md5' => array('resize',458),
			'md6' => array('resize',555),
			'md7' => array('resize',653),
			'md8' => array('resize',750),
			'md9' => array('resize',858),
			'md10' => array('resize',945),
			'md11' => array('resize',1043),
			'md12' => array('resize',1170),
		),
    )
);