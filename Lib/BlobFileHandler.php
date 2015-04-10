<?php
class BlobFileHandler {
	  
	public $errors = array();
	public $resource;
	public $resourceInfo;

	
	/**
	 * add a message to stack (for outside checking)
	 */
	public function error ($message) {
		array_push($this->errors, $message);
	}	

	public function modify($type, $size) {

		// -- format variables
		$type = strtolower($type);
		
		// $output = strtolower($output);
		if (is_array($size)) {
			$maxW = intval($size[0]);
			$maxH = intval($size[1]);
		} else {
			$maxScale = intval($size);
		}
		
		// -- check sizes
		if (isset($maxScale)) {
			if (!$maxScale) {
				$this->error("Max scale must be set");
			}
		} else {
			if (!$maxW || !$maxH) {
				$this->error("Size width and height must be set");
				return;
			}
			if ($type == 'resize') {
				$this->error("Provide only one number for size");
			}
		}
		

		// -- get some information about the file
		$uploadWidth  = imagesx($this->resource);
		$uploadHeight = imagesy($this->resource);
		$dstImg       = null;

		switch ($type) {
			case 'resize':
				# Maintains the aspect ration of the image and makes sure that it fits
				# within the maxW and maxH (thus some side will be smaller)
				// -- determine new size
				if ($uploadWidth > $maxScale || $uploadHeight > $maxScale) {
					if ($uploadWidth > $uploadHeight) {
						$newX = $maxScale;
						$newY = ($uploadHeight*$newX)/$uploadWidth;
					} else if ($uploadWidth < $uploadHeight) {
						$newY = $maxScale;
						$newX = ($newY*$uploadWidth)/$uploadHeight;
					} else if ($uploadWidth == $uploadHeight) {
						$newX = $newY = $maxScale;
					}

					$dstImg = imagecreatetruecolor($newX, $newY);
					imagealphablending($dstImg, false);
	 				imagesavealpha($dstImg,true);
					$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
	 				imagefilledrectangle($dstImg, 0, 0, $newX, $newY, $transparent);
					imagecopyresampled($dstImg, $this->resource, 0, 0, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
					$this->resource = $dstImg;
				} 

				break;
			case 'resizemin':
				# Maintains aspect ratio but resizes the image so that once
				# one side meets its maxW or maxH condition, it stays at that size
				# (thus one side will be larger)
				#get ratios
				$ratioX = $maxW / $uploadWidth;
				$ratioY = $maxH / $uploadHeight;

				#figure out new dimensions
				if (($uploadWidth == $maxW) && ($uploadHeight == $maxH)) {
					$newX = $uploadWidth;
					$newY = $uploadHeight;
				} else if (($ratioX * $uploadHeight) > $maxH) {
					$newX = $maxW;
					$newY = ceil($ratioX * $uploadHeight);
				} else {
					$newX = ceil($ratioY * $uploadWidth);		
					$newY = $maxH;
				}

				$dstImg = imagecreatetruecolor($newX,$newY);
				imagealphablending($dstImg, false);
 				imagesavealpha($dstImg,true);
				$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
 				imagefilledrectangle($dstImg, 0, 0, $newX, $newY, $transparent);
				imagecopyresampled($dstImg, $this->resource, 0, 0, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
				$this->resource = $dstImg;
			
				break;
			case 'resizewidth':
				# Maintains aspect ratio but resizes the image so that the Width
				# has to fit. We don't care about the height.
				# get ratio
				$ratioX = $maxScale / $uploadWidth;

				$newX = $maxScale;
				$newY = ceil($ratioX * $uploadHeight);

				$dstImg = imagecreatetruecolor($newX,$newY);
				imagealphablending($dstImg, false);
 				imagesavealpha($dstImg,true);
				$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
 				imagefilledrectangle($dstImg, 0, 0, $newX, $newY, $transparent);
				imagecopyresampled($dstImg, $this->resource, 0, 0, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
				$this->resource = $dstImg;
			
				break;
			case 'resizecrop':
				// -- resize to max, then crop to center
				$ratioX = $maxW / $uploadWidth;
				$ratioY = $maxH / $uploadHeight;

				if ($ratioX < $ratioY) { 
					$newX = round(($uploadWidth - ($maxW / $ratioY))/2);
					$newY = 0;
					$uploadWidth = round($maxW / $ratioY);
					$uploadHeight = $uploadHeight;
				} else { 
					$newX = 0;
					$newY = round(($uploadHeight - ($maxH / $ratioX))/2);
					$uploadWidth = $uploadWidth;
					$uploadHeight = round($maxH / $ratioX);
				}
				
				$dstImg = imagecreatetruecolor($maxW, $maxH);
				imagealphablending($dstImg, false);
 				imagesavealpha($dstImg,true);
				$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
 				imagefilledrectangle($dstImg, 0, 0, $newX, $newY, $transparent);
				imagecopyresampled($dstImg, $this->resource, 0, 0, $newX, $newY, $maxW, $maxH, $uploadWidth, $uploadHeight);
				$this->resource = $dstImg;
		
				break;
			case 'resizefit':
				# Maintains the aspect ration of the image and makes sure that it fits
				$newX = $maxW;
				$newY = round($newX*($uploadHeight/$uploadWidth));
				$new_x = 0;
				$new_y = round(($maxH-$newY)/2);

				// FILL and FIT mode are mutually exclusive
				$next = $newY > $maxH;

				// If match by width failed and destination image does not fit, try by height 
				if ($next) {
					$newY = $maxH;
					$newX = round($newY*($uploadWidth/$uploadHeight));
					$new_x = round(($maxW - $newX)/2);
					$new_y = 0;
				}
				$dstImg = imagecreatetruecolor($maxW, $maxH);
				imagefill($dstImg, 0, 0, imagecolorallocate($dstImg, 255, 255, 255));
				imagealphablending($dstImg, false);
 				imagesavealpha($dstImg,true);
				$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
 				imagefilledrectangle($dstImg, 0, 0, $newX, $newY, $transparent);
				imagecopyresampled($dstImg, $this->resource, $new_x, $new_y, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
				$this->resource = $dstImg;
				break;
			case 'crop':
				// -- a straight centered crop
				$startY = ($uploadHeight - $maxH)/2;
				$startX = ($uploadWidth - $maxW)/2;

				$dstImg = imageCreateTrueColor($maxW, $maxH);
				imagealphablending($dstImg, false);
 				imagesavealpha($dstImg,true);
				$transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
 				imagefilledrectangle($dstImg, 0, 0, $maxW, $maxH, $transparent);
				ImageCopyResampled($dstImg, $this->resource, 0, 0, $startX, $startY, $maxW, $maxH, $maxW, $maxH);
				$this->resource = $dstImg;
		
				break;
			default: $this->error ("Resize public function \"$type\" does not exist");
		}	
	}

	public function loadFromFile($filename) {

		$this->resourceInfo = getimagesize($filename);

		switch ($this->resourceInfo[2]) {
			case IMAGETYPE_GIF: 
				$this->resource = imagecreatefromgif($filename); 
				break;
			case IMAGETYPE_JPEG: 
				$this->resource = imagecreatefromjpeg($filename);
				break;
			case IMAGETYPE_PNG: 
				$this->resource = imagecreatefrompng($filename);
				break;
			default: $this->error ("File type must be GIF, PNG, or JPG to resize");
		}

		// rotate source image based on exif-orientation
		$this->fixImageRotation($filename);

	}

	public function loadFromString($string) {

		try {
			$this->resource = @imagecreatefromstring($string);
			$this->resourceInfo = @getimagesizefromstring($string);
		} catch (Exception $e) {
			echo "erro";
		}

	}

	public function store($filename, $output, $quality = 100) {

		// -- check output
		if ($output != IMAGETYPE_JPEG && $output != IMAGETYPE_PNG && $output != IMAGETYPE_GIF) {
			$this->error("Cannot output file as " . strtoupper($output));
		}

		if (is_numeric($quality)) {
			$quality = intval($quality);
			if ($quality > 100 || $quality < 1) {
				$quality = 75;
			}
		} else {
			$quality = 75;
		}

		if ($output==IMAGETYPE_GIF) {
			$transparent_index = ImageColorTransparent($this->resource); /* gives the index of current transparent color or -1 */
			if($transparent_index!=(-1)) {
				if($transparent_index>=255) {
					$transparent_index = 254;
				}
				$transparent_color = @ImageColorsForIndex($this->resource,$transparent_index);
			}
			if(!empty($transparent_color)) /* simple check to find wether transparent color was set or not */
			{
				$transparent_new = ImageColorAllocate( $$this->resource, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'] );
				$transparent_new_index = ImageColorTransparent( $$this->resource, $transparent_new );
				ImageFill( $$this->resource, 0,0, $transparent_new_index ); /* don't forget to fill the new image with the transparent color */
			}
		} else {

			if ($output == IMAGETYPE_JPEG and $this->resourceInfo[2] == IMAGETYPE_PNG) {
				
				// transparent backgrounds to white

				// $output = imagecreatetruecolor($this->resourceInfo[0], $this->resourceInfo[1]);
				// $white = imagecolorallocate($output,  255, 255, 255);
				// imagefilledrectangle($output, 0, 0, $this->resourceInfo[0], $this->resourceInfo[1], $white);
				// imagecopy($output, $this->resource, 0, 0, 0, 0, $this->resourceInfo[0], $this->resourceInfo[1]);
				// $this->resource = $output;
			}

			imageAlphaBlending($this->resource, false);
			imageSaveAlpha($this->resource, true);
		}

		// start buffering
		ob_start();
		// -- try to write
		switch ($output) {
			case IMAGETYPE_JPEG:
				// Save as progressive JPG file
				imageinterlace($this->resource,TRUE);
				$write = imagejpeg($this->resource, $filename, $quality);
				break;
			case IMAGETYPE_PNG:
				$write = imagepng($this->resource, $filename);
				break;
			case IMAGETYPE_GIF:
				$write = imagegif($this->resource, $filename);
				break;
		}

		$contents =  ob_get_contents();
		ob_end_clean();

		imagedestroy($this->resource);

		if ($filename) {

			// -- mask file
			chmod($filename, 0654);
			return $filename;
		}
		else {
			return $contents;
		}

		return $write;
	}

	public function isImage() {

		if (empty($this->resource)) {
			return false;
		}

		$width = imagesx($this->resource);

		if ($width > 0) {
			return true;
		}

		return false;

	}

	public function getImageWith() {
		if ( ! $this->IsImage()) {
			return false;
		}

		return imagesx($this->resource);		
	}

	protected function fixImageRotation($filename) {

		if (! function_exists("exif_read_data")) {
			return;
		}

		$exif = exif_read_data($filename);

		if (!empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 3:
					$this->resource = imagerotate($this->resource, 180, 0);
					break;
				case 6:
					$this->resource = imagerotate($this->resource, -90, 0);
					break;
				case 8:
					$this->resource = imagerotate($this->resource, 90, 0);
					break;
			}
		}
	}

}