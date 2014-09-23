<?php

class BlobFileFormHelper extends FormHelper {

	public function file($fieldName, $options = array()) {
		
		$retval = parent::file($fieldName, $options);

		$content = $this->value($fieldName);

		if (is_numeric($content) and $content > 0) {
			$retval .= '<br><img src="'.$this->value($fieldName.'_basepath').'md3.png" />';
			$retval .= '<span class="help-block">'.(floor($content/1024)).' kB uploaded</span>';
		}

		return $retval;
	}
}