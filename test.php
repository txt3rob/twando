<?php
    
    // Defining the basic scraping function
    function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }

function curl($url) {
	include ('./inc/config.php');
        // Assigning cURL options to an array
        $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
            CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
            CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
            CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
            CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
            CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
            CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
        );
         
        $ch = curl_init();  // Initialising cURL 
        curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
		if (0 === strpos($url, 'pic.twitter.com')) {  //Does it start with pic.twitter
			$data = scrape_between($data, '<meta  property="og:image" content="', ':large">');  //Grab image url
			$data = file_get_contents($data);
		}
        curl_close($ch);    // Closing cURL 
		$imagefilemd5name = md5 ($url);
		$imagefile = "".UPLOAD_DIR."".$imagefilemd5name.".jpg";
		$myfile = fopen($imagefile, "w") or die("Unable to open file!");
		fwrite($myfile, $data);
		fclose($myfile);
        return $imagefile;   // Returning the data from the function 
    }
	$url = "http://www.lairdexpertit.com/images/laird.jpg";
	$mypath = curl($url);
	echo $mypath
	
    
   
    
    ?>