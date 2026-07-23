<?php 
	ob_start ("ob_gzhandler");
	header("Content-type: text/javascript");
	header("Cache-Control: must-revalidate");
	
	$offset = 60 * 60 ;
	$ExpStr = "Expires: " . 
	gmdate("D, d M Y H:i:s",
	time() + $offset) . " GMT";
	header($ExpStr);
	header('Content-Encoding: gzip');
/*
This code does 4 things:

   1. It uses PHP's ob_gzhandler to send compressed data. This function will first check to see if the browser requesting the file will accept 'gzip,deflate' encoding; if not it sends the file uncompressed.
   2. It sends a header for the content type and character set for the file - in this case text/css and UTF-8.
   3. The next step sends a 'cache-control http header'. Here 'must-revalidate' ensures that any information that you pass along about the freshness of your document is obeyed.
   4. The final step is to send an 'Expires' header, to set an age on how long our cached file will last. Here we set it to expire in one hour.

*/	
?>
