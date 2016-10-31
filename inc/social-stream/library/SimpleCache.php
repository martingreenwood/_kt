<?php

/*
 * SimpleCache v1.4.1
 *
 * By Gilbert Pellegrom
 * http://dev7studios.com
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
class SimpleCache {

	// Path to cache folder (with trailing /)
	public $cache_path = 'cache/';
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;
	// Cache file extension
	public $cache_extension = '.cache';
    
    public $debug_log = false;

	// This is just a functionality wrapper function
	public function get_data($label, $url)
	{
		if($this->is_cached($label)){
            return $this->get_cache($label);
		} else {
            $data = $this->do_curl($url);
			$this->set_cache($label, $data);
			return $data;
		}
	}

	public function set_cache($label, $data)
	{
		file_put_contents($this->cache_path . $this->safe_filename($label) . $this->cache_extension, $data);
	}

	public function get_cache($label)
	{
		$filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;
		return file_get_contents($filename);
	}

	public function is_cached($label)
	{
		$filename = $this->cache_path . $this->safe_filename($label) . $this->cache_extension;

		if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;

		return false;
	}

	//Helper function for retrieving data from url
	public function do_curl($url)
	{
		$GLOBALS['crawled']++;
        if ( function_exists("curl_init") ) {
			$ch = curl_init();
            
            // Setup headers - the same headers from Firefox version 2.0.0.6
            // using fake headers and a fake user agent.
            // below was split up because the line was too long.
            $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
            $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
            $header[] = "Cache-Control: max-age=0";
            $header[] = "Connection: keep-alive";
            $header[] = "Keep-Alive: 300";
            $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $header[] = "Accept-Language: en-us,en;q=0.5";
            $header[] = "Pragma: "; // browsers keep this blank.
            
			curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_REFERER, '');
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, SB_API_TIMEOUT);
            curl_setopt($ch, CURLOPT_TIMEOUT, SB_API_TIMEOUT);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$content = curl_exec($ch);
            if ($content === false)
            {
                if ($this->debug_log)
                    ss_debug_log( 'cURL error: ' . curl_error($ch) . ' - ' . $url, SB_LOGFILE );
            }
            curl_close($ch);
			return $content;
		} else {
            $ctx = stream_context_create(array('http'=>
                array(
                    'timeout' => SB_API_TIMEOUT,
                )
            ));
            $content = @file_get_contents($url, false, $ctx);
            if ($content === false) {
                if ($this->debug_log)
                    ss_debug_log( 'Failed to open stream: HTTP request failed!' . ' - ' . $url, SB_LOGFILE );
            }
            return $content;
		}
	}

	//Helper function to validate filenames
	private function safe_filename($filename)
	{
		$filename = md5($filename);
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
	}
}

// End of file SimpleCache.php