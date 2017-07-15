<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class Memcached_library {
    
    protected $config;
    protected $local_cache = array();
    protected $m;
    protected $client_type;
    protected $ci;
    protected $errors = array();
    
    public function __construct() {
        $this->ci = & get_instance();
        
        // Lets try to load Memcache or Memcached Class
        $this->client_type = class_exists('Memcached') ? "Memcached" : (class_exists('Memcache') ? "Memcache" : FALSE);
        
        if ($this->client_type) {
            $this->ci->load->config('memcached');
            $this->config = $this->ci->config->item('memcached');
            
            // Which one should be loaded
            switch ($this->client_type) {
                case 'Memcached':
                    $this->m = new Memcached();
                    break;
                case 'Memcache':
                    $this->m = new Memcache();
                    // Set Automatic Compression Settings
                    if ($this->config['config']['auto_compress_tresh']) {
                        $this->setcompressthreshold($this->config['config']['auto_compress_tresh'], $this->config['config']['auto_compress_savings']);
                    }
                    break;
            }
            log_message('debug', "Memcached Library: $this->client_type Class Loaded");
            
            $this->auto_connect();
            var_dump($this->m->get('227qidrgk2ou8ijjreo33q2qr4'));
            var_dump($this->m->get('hcd8mo8vsv3lrh7o5t4lof5gd0'));
        } else {
            log_message('error', "Memcached Library: Failed to load Memcached or Memcache Class");
        }
    }
    
    /*
	+-------------------------------------+
		Name: auto_connect
		Purpose: runs through all of the servers defined in
		the configuration and attempts to connect to each
		@param return : none
	+-------------------------------------+
	*/
    protected function auto_connect() {
        foreach ($this->config['servers'] as $key => $server) {
            if (!$this->add_server($server)) {
                $this->errors[] = "Memcached Library: Could not connect to the server named $key";
                log_message('error', 'Memcached Library: Could not connect to the server named "' . $key . '"');
            } else {
                log_message('debug', 'Memcached Library: Successfully connected to the server named "' . $key . '"');
            }
        }
    }
    
    /*
	+-------------------------------------+
		Name: add_server
		Purpose: 
		@param return : TRUE or FALSE
	+-------------------------------------+
	*/
    public function add_server($server) {
        extract($server);
        return @$this->m->addServer($host, $port, $weight);
    }
    
    public function put($region, $key, $value, $expiration = NULL) {
        if (!isset($this->config['region']) || !key_exists($region, $this->config['region'])) {
            return FALSE;
        }
        $region_conf = $this->config['region'][$region];
        list($_region, $_expiration) = array_values($region_conf);
        if ($expiration !== NULL) {
            $_expiration = $expiration;
        }
        
        $key_name = $this->key_name($_region . $key);
        if ($_expiration === NULL || !is_numeric($_expiration)) {
            $_expiration = $this->config['config']['expiration'];
        }
        
        $this->local_cache[$key_name] = $value;
        switch ($this->client_type) {
            case 'Memcache':
                $put_status = @$this->m->set($key_name, $value, $this->config['config']['compression'], $_expiration);
                break;
            
            default:
            case 'Memcached':
                $put_status = @$this->m->set($key_name, $value, $_expiration);
                break;
        }
        
        return $put_status;
    }
    
    /*
	+-------------------------------------+
		Name: add
		Purpose: add an item to the memcache server(s)
		@param return : TRUE or FALSE
	+-------------------------------------+
	*/
    public function _add($key = NULL, $value = NULL, $expiration = NULL) {
        if (is_null($expiration)) {
            $expiration = $this->config['config']['expiration'];
        }
        if (is_array($key)) {
            foreach ($key as $multi) {
                if (!isset($multi['expiration']) || $multi['expiration'] == '') {
                    $multi['expiration'] = $this->config['config']['expiration'];
                }
                $this->_add($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        } else {
            $this->local_cache[$this->key_name($key)] = $value;
            switch ($this->client_type) {
                case 'Memcache':
                    $add_status = @$this->m->add($this->key_name($key), $value, $this->config['config']['compression'], $expiration);
                    break;
                
                default:
                case 'Memcached':
                    $add_status = @$this->m->add($this->key_name($key), $value, $expiration);
                    break;
            }
            
            return $add_status;
        }
    }
    
    /*
	+-------------------------------------+
		Name: set
		Purpose: similar to the add() method but uses set
		@param return : TRUE or FALSE
	+-------------------------------------+
	*/
    public function _set($key = NULL, $value = NULL, $expiration = NULL) {
        if (is_null($expiration)) {
            $expiration = $this->config['config']['expiration'];
        }
        if (is_array($key)) {
            foreach ($key as $multi) {
                if (!isset($multi['expiration']) || $multi['expiration'] == '') {
                    $multi['expiration'] = $this->config['config']['expiration'];
                }
                $this->_set($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        } else {
            $this->local_cache[$this->key_name($key)] = $value;
            switch ($this->client_type) {
                case 'Memcache':
                    $add_status = @$this->m->set($this->key_name($key), $value, $this->config['config']['compression'], $expiration);
                    break;
                
                default:
                case 'Memcached':
                    $add_status = @$this->m->set($this->key_name($key), $value, $expiration);
                    break;
            }
            
            return $add_status;
        }
    }
    
    /*
	+-------------------------------------+
		Name: get
		Purpose: gets the data for a single key or an array of keys
		@param return : array of data or multi-dimensional array of data
	+-------------------------------------+
	*/
    public function get($region, $key = NULL) {
        if ($this->m) {
            if (isset($this->local_cache[$this->key_name($key)])) {
                return $this->local_cache[$this->key_name($key)];
            }
            if (is_null($key)) {
                $this->errors[] = 'The key value cannot be NULL';
                return FALSE;
            }
            
            if (!key_exists($region, $this->config['region'])) {
                $this->errors[] = 'Undefined region';
                return FALSE;
            }
            $region_conf = $this->config['region'][$region];
            
            if (is_array($key)) {
                if (method_exists($this->m, 'getMulti')) {
                    foreach ($key as $n => $k) {
                        $key[$n] = $this->key_name($region_conf['region'] . $k);
                    }
                    return @$this->m->getMulti($key);
                } else {
                    $key_name = $this->key_name($region_conf['region'] . $key[0]);
                    $r = @$this->m->get($key_name);
                    if ($r) {
                        return array($key_name => $r);
                    } else {
                        return FALSE;
                    }
                }
            } else {
                return @$this->m->get($this->key_name($region_conf['region'] . $key));
            }
        }
        return FALSE;
    }
    
    /*
	+-------------------------------------+
		Name: delete
		Purpose: deletes a single or multiple data elements from the memached servers
		@param return : none
	+-------------------------------------+
	*/
    public function delete($region, $key, $expiration = NULL) {
        if (is_null($key)) {
            $this->errors[] = 'The key value cannot be NULL';
            return FALSE;
        }
        
        if (is_null($expiration)) {
            $expiration = $this->config['config']['delete_expiration'];
        }
        
        if (is_array($key)) {
            foreach ($key as $multi) {
                $this->delete($region, $multi, $expiration);
            }
        } else {
            $region_conf = $this->config['region'][$region];
            unset($this->local_cache[$this->key_name($region_conf['region'] . $key)]);
            return @$this->m->delete($this->key_name($region_conf['region'] . $key), $expiration);
        }
    }
    
    /*
	+-------------------------------------+
		Name: replace
		Purpose: replaces the value of a key that already exists
		@param return : none
	+-------------------------------------+
	*/
    public function _replace($key = NULL, $value = NULL, $expiration = NULL) {
        if (is_null($expiration)) {
            $expiration = $this->config['config']['expiration'];
        }
        if (is_array($key)) {
            foreach ($key as $multi) {
                if (!isset($multi['expiration']) || $multi['expiration'] == '') {
                    $multi['expiration'] = $this->config['config']['expiration'];
                }
                $this->_replace($multi['key'], $multi['value'], $multi['expiration']);
            }
        } else {
            $this->local_cache[$this->key_name($key)] = $value;
            
            switch ($this->client_type) {
                case 'Memcache':
                    $replace_status = @$this->m->replace($this->key_name($key), $value, $this->config['config']['compression'], $expiration);
                    break;
                
                default:
                case 'Memcached':
                    $replace_status = @$this->m->replace($this->key_name($key), $value, $expiration);
                    break;
            }
            
            return $replace_status;
        }
    }
    
    /*
	+-------------------------------------+
		Name: flush
		Purpose: flushes all items from cache
		@param return : none
	+-------------------------------------+
	*/
    public function flush() {
        return @$this->m->flush();
    }
    
    /*
	+-------------------------------------+
		Name: getversion
		Purpose: Get Server Vesion Number
		@param Returns a string of server version number or FALSE on failure. 
	+-------------------------------------+
	*/
    public function getversion() {
        return @$this->m->getVersion();
    }
    
    /*
	+-------------------------------------+
		Name: getstats
		Purpose: Get Server Stats
		Possible: "reset, malloc, maps, cachedump, slabs, items, sizes"
		@param returns an associative array with server's statistics. Array keys correspond to stats parameters and values to parameter's values.
	+-------------------------------------+
	*/
    public function getstats($type = "items") {
        switch ($this->client_type) {
            case 'Memcache':
                $stats = @$this->m->getStats($type);
                break;
            
            default:
            case 'Memcached':
                $stats = @$this->m->getStats();
                break;
        }
        return $stats;
    }
    
    /*
	+-------------------------------------+
		Name: setcompresstreshold
		Purpose: Set When Automatic compression should kick-in
		@param return TRUE/FALSE
	+-------------------------------------+
	*/
    public function setcompressthreshold($tresh, $savings = 0.2) {
        switch ($this->client_type) {
            case 'Memcache':
                $setcompressthreshold_status = @$this->m->setCompressThreshold($tresh, $savings = 0.2);
                break;
            
            default:
                $setcompressthreshold_status = TRUE;
                break;
        }
        return $setcompressthreshold_status;
    }
    
    /*
	+-------------------------------------+
		Name: key_name
		Purpose: standardizes the key names for memcache instances
		@param return : md5 key name
	+-------------------------------------+
	*/
    protected function key_name($key) {
        return md5(strtolower($this->config['config']['prefix'] . $key));
    }

}	
/* End of file memcached_library.php */
/* Location: ./application/libraries/memcached_library.php */
