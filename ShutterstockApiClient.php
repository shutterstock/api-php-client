<?php

class ShutterstockApiClient {
    
    //customer credentials     
    private static $customer_username      = 'XXXXXX';
    private static $customer_password       = 'XXXXX';
    
    //basic auth info
    private static $auth_username = 'XXXXX';
    private static $auth_key      = 'XXXXX'; 
    
    //api domain info    
    private $base_url       = 'api.integration.dev.shutterstock.com';
       
    private $auth_token               = null;   
    
    //response info
    public $response                    = null;
    public $response_format             = 'json';    
    
    public function __construct() {
        if (is_null($this->auth_token)) {
            //get auth token
            $this->authCustomer();    
        }        
    }
    
    /**
     * POST authenticate user
     */
    private function authCustomer() {
        
        $request_url = 'http://'.$this->base_url.'/auth/customer.json';
        
        $params = array(                                
            'username'  => self::$customer_username,
            'password'  => self::$customer_password
        );
        
        $response = $this->post($request_url, $params);
               
        $token_info = $this->parseRespose($response);
        
        if (isset($token_info['auth_token'])) {
            $this->auth_token = $token_info['auth_token'];
            return true;
        }
        
        error_log('Error while getting Access Token');
    }
    
    /**
     * Get list of all categories
     * End Point : GET /categories       
     * See documentation @ http://api.shutterstock.com section "categories"
    **/ 
     public function getCategories() {
         $request_url = 'http://'.$this->base_url.'/categories.json';
         
         $response = $this->get($request_url, $params);
                        
         return $this->parseRespose($response);         
     }
     
    /**
     * Get user account info
     * End Point : GET /customer         
     * See documentation @ http://api.shutterstock.com section "customers"
    **/
    public function getCustomerUserInfo() {
        $request_url = 'http://'.$this->base_url.'/customers/'.self::$customer_username.'.json';
         
        $params = array(                                
            'auth_token'  => $this->auth_token
        );
                 
        $response = $this->get($request_url, $params);
                        
        return $this->parseRespose($response);         
    }
      
    /**
    * GET Searches for images that meet provided criteria.
    */
       
    public function search($searchterm, $filters=array()) {
            
        //supported languates
        $languages = array('en', 'zh', 'nl', 'fr', 'de', 'it', 'jp', 'pt', 'ru', 'es', 'cs', 'hu', 'tr', 'pl');
        
        if ($filters['language'] && !in_array($filters['language'], $languages)) {
            $filters['language'] = 'en';
        }
        
        //orientations
        $orientations = array('all', 'horizontal', 'vertical');
        if ($filters['orientation'] && !in_array($filters['orientation'], $orientations)) {
            $filters['orientation'] = 'all';
        }
        
        //search groups
        $search_groups = array('photos', 'illustrations', 'vectors', 'all');
        if ($filters['search_group'] && !in_array($filters['search_group'], $search_groups)) {
            $filters['search_group'] = 'all';
        }
        
        //sort_method
        $sort_method = array('newest', 'oldest', 'popular', 'random', 'relevance');
        if ($filters['sort_method'] && !in_array($filters['sort_method'], $sort_method)) {
            $filters['sort_method'] = 'popular';
        }
        
        $default_filters = array(
            'category_id'       => '',
            'color'             => '',
            'commercial_only'   => 0,
            'enhanced_only'     => 0,
            'exclude_keywords'  => '',
            'language'          => 'en',
            'model_released'    => 0,
            'orientation'       => 'all',
            //'page_number'       => 1,
            'photographer_name' => '',
            'safesearch'        => 0,
            'search_group'      => 'all',
            'sort_method'       => 'popular',
            'submitter_id'      => ''
        );
        
        $filters = array_filter(array_merge($default_filters, $filters));
                
        $request_url = 'http://'.$this->base_url.'/images/search.json?searchterm='.$searchterm.'&'.http_build_query($filters);
                        
        $response = $this->get($request_url);
        
        return $this->parseRespose($response);
        
    }
    
    /**
     * Get information about loggedin user's lightboxes
     * End Point : GET /customers/<username>/lightboxes
     * See documentation @ http://api.shutterstock.com section "/customers/<username>/lightboxes"       
     *
    **/ 
    public function getLightboxes() {
        $request_url = 'http://'.$this->base_url.'/customers/'.self::$customer_username.'/lightboxes.json';
         
        $params = array(                                
            'auth_token'  => $this->auth_token
        );
                 
        $response = $this->get($request_url, $params);
                        
        $lightboxes = $this->parseRespose($response);
        
        if (is_array($lightboxes)) {
            $formatted_list = array();
            
            foreach ($lightboxes as $lightbox) {
                $formatted_list[$lightbox['lightbox_id']]['lightbox_id']    = $lightbox['lightbox_id'];
                $formatted_list[$lightbox['lightbox_id']]['lightbox_name']  = $lightbox['lightbox_name'];
                
                //get last image as cover photo
                if (is_array($lightbox['images'])) {
                    $cover_photo = array_pop($lightbox['images']);
                    
                    //get image info for cover photo
                    $cover_photo_info = $this->getImageInfo($cover_photo['image_id']);
                    
                    if (is_array($cover_photo_info) && isset($cover_photo_info['sizes'])) {
                        $formatted_list[$lightbox['lightbox_id']]['cover_photo_info']['thumb_large'] =  $cover_photo_info['sizes']['thumb_large'];
                        $formatted_list[$lightbox['lightbox_id']]['cover_photo_info']['thumb_small'] =  $cover_photo_info['sizes']['thumb_small'];    
                    }                  
                }
            }

            return $formatted_list;
        }
        
        return false;
    }

    /**
     * add new lightbox
     * End Point : POST /customers/<username>/lightboxes
     * See documentation @ http://api.shutterstock.com section "/customers/<username>/lightboxes"       
     *
    **/ 
    public function addLightbox($name) {            
        $params = array('lightbox_name'=>$name, 'auth_token'=>$this->auth_token, 'username'=>self::$customer_username);
               
        $request_url = 'http://' . $this->base_url.'/customers/'.self::$customer_username.'/lightboxes.json';
        $response = $this->post($request_url, $params);
        
        return $this->parseRespose($response);
    }
    
    /**
     * delete a lightbox
     * End Point : DELETE /lightboxes/<lightbox_id>
     * See documentation @ http://api.shutterstock.com section "/lightboxes/<lightbox_id>"       
     *
    **/ 
    public function deleteLightbox($lightbox_id) {       
               
        $request_url = 'http://' . $this->base_url.'/lightboxes/'.$lightbox_id.'.json?lightbox_id='.$lightbox_id.'&auth_token='.$this->auth_token;
                
        $response = $this->delete($request_url, $params=array());
        
        if ($response->meta['http_code'] == 204 && empty($response->data)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * get information about lightbox
     * End Point : GET /lightboxes/<lightbox_id>
     * See documentation @ http://api.shutterstock.com section "/lightboxes/<lightbox_id>"       
     *
    **/
    public function getLightbox($lightbox_id) {       
               
        $request_url = 'http://' . $this->base_url.'/lightboxes/'.$lightbox_id.'.json';
        
        $params = array(                                
            'auth_token'  => $this->auth_token
        );
        
        $response = $this->get($request_url, $params);
        
        $lightbox = $this->parseRespose($response);
        
        if (is_array($lightbox)) {
        
            $formatted_info = array();
                      
            $formatted_info['lightbox_id']    = $lightbox['lightbox_id'];
            $formatted_info['lightbox_name']  = $lightbox['lightbox_name'];
            
            //get last image as cover photo
            if (is_array($lightbox['images'])) {
                $cover_photo = array_pop($lightbox['images']);
                
                //get image info for cover photo
                $photo_info = $this->getImageInfo($cover_photo['image_id']);
                
                if (is_array($photo_info)) {
                    $formatted_info['images'][$photo_info['photo_id']] = array(
                        'photo_id' => $photo_info['image_id'],
                        'description' => $photo_info['description'],
                        'thumb_large' => $photo_info['sizes']['thumb_large'],
                        'thumb_small' => $photo_info['sizes']['thumb_small']
                    );
                }
            }            
            return $formatted_info;      
        }
        
        return false;
    }
    
    /**
     * add image to the lightbox
     * End Point : put /lightboxes/<lightbox_id>/images/<image_id>
     * See documentation @ http://api.shutterstock.com section "/lightboxes/<lightbox_id>/images/<image_id>"       
     *
    **/
    public function addImageToLightbox($lightbox_id, $image_id) {
        
        $request_url = 'http://' . $this->base_url.'/lightboxes/'.$lightbox_id.'/images/'.$image_id.'.json?auth_token='.$this->auth_token.'&image_id='.$image_id.'&lightbox_id='.$lightbox_id.'&username='.self::$customer_username;
        
        $response = $this->put($request_url, array());
                
        if ($response->meta['http_code'] == 200 && empty($response->data)) {
            return true;
        }        
        return false;        
    }
    
    /**
     * delete image from the lightbox
     * End Point : put /lightboxes/<lightbox_id>/images/<image_id>
     * See documentation @ http://api.shutterstock.com section "/lightboxes/<lightbox_id>/images/<image_id>"       
     *
    **/
    public function deleteImageFromLightbox($lightbox_id, $image_id) {
        
        $request_url = 'http://' . $this->base_url.'/lightboxes/'.$lightbox_id.'/images/'.$image_id.'.json?auth_token='.$this->auth_token.'&image_id='.$image_id.'&lightbox_id='.$lightbox_id.'&username='.self::$customer_username;
        
        $response = $this->delete($request_url, array());
        
        if ($response->meta['http_code'] == 200 && empty($response->data)) {
            return true;
        }
        
        return false;   
         
    }   
    
    /**
     * get extended information about lightbox
     * End Point : GET /lightboxes/<lightbox_id>/extended
     * See documentation @ http://api.shutterstock.com section "/lightboxes/<lightbox_id>/extended"     *        
     *
    **/
    public function getLightboxExtended($lightbox_id) {       
               
        $request_url = 'http://' . $this->base_url.'lightboxes/'.$lightbox_id.'/extended.json?lightbox_id='.$lightbox_id.'&auth_token='.$this->auth_token;
        
        $params=array();
        
        $response = $this->get($request_url, $params);
        
        return $this->parseRespose($response);
    }
    
    /**
     * GET image info
     */    
    public function getImageInfo($image_id) {
        $request_url = 'http://'.$this->base_url.'/images/'.$image_id.'.json';
                         
        $response = $this->get($request_url);
                        
        return $this->parseRespose($response);       
    } 
    
    /**
     * Get subscription information for the loggedin in user
     * End Point : GET /customers/<username>/subscriptions
     * See documentation @ http://api.shutterstock.com section "subscriptions"       
     *
    **/ 
    public function getSubscriptions() {        
        
        $request_url = 'http://' . $this->base_url.'/customers/'.self::$customer_username.'/subscriptions.json';
        
        $params = array(                                
            'auth_token'  => $this->auth_token
        );
                     
        $response = $this->get($request_url, $params);
            
        return $this->parseRespose($response);
    }
    
    /**
     * Get information about loggedin user's download history
     * End Point : GET /customers/<username>/images/downloads
     * See documentation @ http://api.shutterstock.com section "/customers/<username>/images/downloads"       
     *
    **/ 
    public function getDownloads($page=1, $page_size=10) {
        
        $request_url = 'http://' . $this->base_url.'/customers/'.self::$customer_username.'/images/downloads.json';
        
        $params = array(                                
            'auth_token'  => $this->auth_token,
            'page_number' => $page,
            'page_size' => $page_size,
        );
        
        $response = $this->get($request_url, $params);   
        
        $downloads = $this->parseRespose($response);
        
        if (is_array($downloads)) {
                
            $formatted_list = array();
            
            foreach ($downloads as $dlist) {
                foreach ($dlist as $d) {
                    //get image info
                    $image_info = $this->getImageInfo($d['image_id']);                         
                        
                    if (is_array($image_info)) {
                        $formatted_list[] = array(
                            'image_id' => $d['image_id'],
                            'thumb_small' => $image_info['sizes']['thumb_small'],
                            'image_size' => $d['image_size'],
                            'time' => $d['time'],
                            'license' => $d['license']
                        );
                    }
                }               
            }

            return $formatted_list;
        }
        
        return false;
    }

   /**
     * Download image
     * End Point : POST /subscriptions/<subscription_id>/images/<image_id>/sizes/<size>
     * See documentation @ http://api.shutterstock.com section "/subscriptions/<subscription_id>/images/<image_id>/sizes/<size>"
     * 
     * params @metadata :   Customer metadata for enterprise downloads. Required only for enterprise subscriptions, ignored otherwise. 
     *                      Expects json representing a hash (aka associative array) with name/value pairs for enterprise downloads. 
     *                      Names by default are purchase_order, job, client, and other. By default, enterprise subscriptions must 
     *                      specify a non-empty value for the purchase_order metadata, and all metadata fields values must be defined 
     *                      (even if their values are empty). Note that only the first 64 characters of each metadata value will be recorded, 
     *                      the remainder will be silently ignored. Also, some users may have the names of the metadata fields changed, 
     *                      and/or different fields required.
     * 
     * params @size: options small/medium/huge/vector
     * 
     * params @format: options jpg/eps         
     *
    **/
    public function downloadImage($image_id, $subscription_id, $size, $format, $meta=array()) {
                    
        static $allowed_sizes = array('small', 'medium', 'huge', 'vector');
        static $allowed_formats = array('jpg', 'eps');
        
        if (!in_array($size, $allowed_sizes)) {         
            return false;   
        }
        
        if (!in_array($format, $allowed_formats)) {         
            return false;   
        }
        
        if (!empty($meta)) {
            $meta = json_encode($meta);    
        }
                   
        $params = array(
            'auth_token'=>$this->auth_token,           
            'metadata'=>$meta,            
            'image_id'=>$image_id,
            'subscription_id'=>$subscription_id,
            'size'=>$size,
            'format'=>$format           
        );
        
        $request_url = 'http://' . $this->base_url.'/subscriptions/'.$subscription_id.'/images/'.$image_id.'/sizes/'.$size.'.json';
        $response = $this->service_client->post($request_url, $params);
       
        return $this->parseRespose($response);
    }
    
    #####################################################################################################################################
    #Functions to make Curl Request
    #####################################################################################################################################
    
    /**
     * GET request
     *
     * @param string $url URL to use for the request
     * @param mixed $url_params url parameters
     * @param object $callback function to call after request
     * @param array $options additional CURL options to set/override for the request
     * @return object ServiceResponse class instance
     */
    private function get($url, $url_params=null, $callback=null, $options=null) {
        if ( !is_array($options) ) {
            $options    = array();
        }
        
        if ( !is_null($url_params) ) {
            if ( is_array($url_params) ) {
                if ( count($url_params)>0 ) {
                    $url    .= '?'.http_build_query($url_params);
                }
            } else {
                $url    .= '?'.$url_params;
            }
        }

        $options[CURLOPT_HTTPGET]   = true;
        return $this->makeRequest($url, $options, $callback);
    }
    
    /**
     * POST request
     * To post a file, use the $data array and set the value to @/path/to/file
     *
     * @param string $url URL to use for the request
     * @param array $options additional CURL options to set/override for the request
     * @return object ServiceResponse class instance
     */
    private function post($url, $data, $callback=null, $options=null) {
        if ( !is_array($options) ) {
            $options    = array();
        }
        $options[CURLOPT_POST]          = true;
        $options[CURLOPT_POSTFIELDS]    = $data;
        
        return $this->makeRequest($url, $options, $callback);
    }
    
    /**
     * PUT request
     * To post a file, use the $data array and set the value to @/path/to/file
     *
     * @param string $url URL to use for the request
     * @param string/array $data additional data to submit with request
     * @param array $options additional CURL options to set/override for the request
     * @return object ServiceResponse class instance
     */
    private function put($url, $data, $callback=null, $options=null) {
        return $this->custom('PUT', $url, $data, $callback, $options);
    }
    
    /**
     * DELETE request
     *
     * @param string $url URL to use for the request
     * @param string/array $data additional data to submit with request
     * @param array $options additional CURL options to set/override for the request
     * @return object ServiceResponse class instance
     */
    private function delete($url, $data, $callback=null, $options=null) {
        return $this->custom('DELETE', $url, $data, $callback, $options);
    }

    /**
     * Custom request
     *
     * @param string $method HTTP method to use for the request
     * @param string $url URL to use for the request
     * @param array $options additional CURL options to set/override for the request
     * @return object ServiceResponse class instance
     */
    private function custom($method, $url, $data=null, $callback=null, $options=null) {
        if ( !is_array($options) ) {
            $options    = array();
        }
        $options[CURLOPT_CUSTOMREQUEST] = $method;
        if(isset($data)){
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        return $this->makeRequest($url, $options, $callback);
    }
    
    /**
     * Convert an array to a flattened URL parameter structure
     * This will create a URL parameter string with repeating param keys
     * If this special behavior is not needed, http_build_query should be used
     *
     * @param array $params key/value list of url parameters to use
     * @param string $delimiter optional alternative delimiter to use
     */
    private function arrayToUrlParams($params, $delimiter='&') {
        $url_params = array();
        foreach($params as $k=>$v) {
            if ( is_array($v) ) {
                foreach($v as $v1) {
                    $url_params[]   = urlencode($k).'='.urlencode($v1);
                }
            } else {
                $url_params[]   = urlencode($k).'='.urlencode($v);
            }
        }
        $params     = implode($delimiter, $url_params);
        return $params;
    }
     
    private function makeRequest($url, $options=null, $callback=null) {
        
        //set curl default options
        $curl_opts = array(
            CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
            CURLOPT_USERPWD         => self::$auth_username.':'.self::$auth_key,
            CURLOPT_CONNECTTIMEOUT  => 2,
            CURLOPT_TIMEOUT         => 3,
            CURLOPT_USERAGENT       => 'PHP-Shutterstock-API-Client ',
            CURLOPT_REFERER         => '',
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => true,
            CURLOPT_ENCODING        => 'utf-8',
            CURLOPT_HTTPHEADER      => array(
                'Accept'            => 'application/json',
                'Accept-Language'   => 'en-us,en',
                'Accept-Encoding'   => 'gzip, deflate'
            )
        );
        
        if ( is_array($options) ) {
            // Check for headers option
            if ( isset($options[CURLOPT_HTTPHEADER]) ) {
                $options[CURLOPT_HTTPHEADER] = array_merge($curl_opts[CURLOPT_HTTPHEADER], $options[CURLOPT_HTTPHEADER]);
            }
            // Merge in passed options. Since array is numeric array_merge can't be used
            foreach($curl_opts as $k=>$v) {
                if ( !array_key_exists($k, $options) ) {
                    $options[$k]    = $v;
                }
            }
        } else {
            $options = $curl_opts;
        }
        
        // Convert headers to flat array
        $curl_hdrs = array();
        foreach($options[CURLOPT_HTTPHEADER] as $k=>$v) {
            $curl_hdrs[]    = $k.': '.$v;
        }
        
        $options[CURLOPT_HTTPHEADER]    = $curl_hdrs;
                
        //Accept-Language: en-us,en;q=0.5
        // initialize response variables
        $header             = '';
        $cinfo              = array();
        $ch                 = curl_init($url);
        
        curl_setopt_array($ch, $options);
        
        $response   = $this->executeRequest($ch);
        
        if ( is_object($callback) ) {
            return $callback($response);
        } else {
            return $response;
        }        
    }
    
    private function executeRequest($ch) {
        static $retries     = 0;
        $cresult            = curl_exec($ch);
        $cinfo              = curl_getinfo($ch);
        
        if ( $cresult===false ) {
            
            // Request failed
            $cinfo          = curl_getinfo($ch);
            $cinfo          = array(
                'is_success'=> false,
                'url'       => $cinfo['url'],       // this variable doesn't exist in this function -Brent
                'errorno'   => curl_errno($ch),
                'error'     => curl_error($ch),
                'queue'     => 'off'
            );
            
            // Check retry count
            $retries++;
            
            if ( $retries < $this->retries ) {
                self::logProfiling($cinfo);
                if ( $this->log_retries ) {
                    $this->logError('retrying request - ('.$cinfo['errorno'].') '.$cinfo['error'].' :: '.$cinfo['url']);
                }
                usleep($this->retry_delay);
                return $this->makeRequest($cinfo['url'], $options);
            } else {
                error_log('max retries ('.$retries.') reached - ('.$cinfo['errorno'].') '.$cinfo['error'].' :: '.$cinfo['url']);
            }
            
        } else {
            $cinfo['is_success']    = true;
            $cinfo['queue']         = 'off';
            // parse out header from body
            list($header, $cresult) = explode("\r\n\r\n", $cresult, 2);
            if($header == 'HTTP/1.1 100 Continue'){ // discard preliminary "Continue" response if present
                list($header, $cresult) = explode("\r\n\r\n", $cresult, 2);
            }
        }
        
        $retries            = 0;
        
        // Check for slow request logging
        if ( $this->slow_response && $this->slow_response > $cinfo['total_time'] ) {
            $this->logError('slow response ('.$cinfo['total_time'].'s) from '.$cinfo['url']);
        }
               
        $this->response = new ServiceResponse($cinfo, $cresult, $header);
        
        return $this->response;
    }

    private function parseRespose($response) {
        
        if ( $response->is_success && $response->http_code < 300 ) {
            if ($this->response_format == 'json') {          
                return json_decode($response->data, true);                  
            }            
        }
        
        return $response->data;
        
        error_log('Error while getting API Data:'.print_r($response, true));
        
        return false;       
    }   
}


/**
* Response class for a service request
* Meta data information can be accessed directly for simplicity
*/
class ServiceResponse {
    /**
     * @var array
     */
    public $meta    = array();

    /**
     * @var array
     */
    public $header  = array();

    /**
     * @var string
     */
    public $data    = null;

    /**
     * Constructor
     *
     * @param array $meta curl_getinfo information
     * @param string $data response content from service request 
     */
    public function __construct($meta, $data, $header=null) {
        $this->meta = $meta;
        $this->data = $data;
        if ( !is_null($header) ) {
            $this->parseHeader($header);
        }
    }
    
    /**
     * Convenience magic method for accessing meta data
     *
     * @param string $meta_key curl_getinfo meta data key
     */
    public function __get($meta_key) {
        // Check meta data for matching key
        if ( array_key_exists($meta_key, $this->meta) ) {
            return $this->meta[$meta_key];
        }
        // Check header for matching key
        if ( array_key_exists($meta_key, $this->header) ) {
            return $this->header[$meta_key];
        }
        trigger_error('SERVICE RESPONSE: reference to invalid meta key - '.$meta_key);
        return null;
    }
    
    public function parseHeader($header) {
        $header_lines   = explode("\r\n", $header);
        if ( count($header_lines)>0 ) {
            foreach($header_lines as $l) {
                $h  = explode(':', $l);
                if ( count($h)>1 ) {
                    $this->header[$h[0]]    = trim($h[1]);
                }
            }
        } else {
            $this->header   = array();
        }
    }
}
