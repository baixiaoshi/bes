<?php

class Paginator {
    var $uri = '';
    var $total_rows = ''; // Total number of items (database results)
    var $per_page = 20; // Max number of items you want shown per page
    var $num_links = 7; // Number of "digit" links to show before/after the currently viewed page
    var $cur_page = 1; // The current page being viewed
    var $total_pages = 1;

    public function initialize($params = array()) {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
        $this->total_pages = ceil($this->total_rows / $this->per_page);
        if ($this->total_pages > 100) {
            $this->total_pages = 100;
        }
        $this->cur_page = intval($this->cur_page);
        if ($this->cur_page > $this->total_pages) {
            $this->cur_page = $this->total_pages;
        }
    }

    /*
     * 分页样式：上一页 1 ... 3 4(*) 5 ... 下一页
    */
    public function render($nofollow = FALSE) {
        if ($this->total_pages < 2) {
            return '';
        }
        if ($this->uri === '') {
            return;
        }
        
        $padding = floor($this->num_links / 2);
        if ($this->total_pages <= $this->num_links) {
            $end = $this->total_pages;
        } elseif ($this->cur_page + 1 < $this->num_links) {
            $end = $this->num_links;
        } elseif (($this->cur_page + $padding) < $this->total_pages) {
            $end = $this->cur_page + $padding;
        } elseif (($this->cur_page + $padding) >= $this->total_pages) {
            $end = $this->total_pages;
        }
        $start = (($end - $this->num_links) + 1 > 2) ? (($end - $this->num_links) + 1) : 2;
        
        $output = '';
        if ($this->cur_page > 1) {
            $other_str = ($nofollow && $this->cur_page - 1 != 1) ? 'rel="nofollow"' : '';
            $first_class = ($this->cur_page - 1) == 1 ? 'class="pre"' : '';
            $output .= '<li ' . $first_class . '><a href="' . $this->uri->add_query('page', $this->cur_page - 1)->render() . '" ' . $other_str .
                             '>上一页</a>';
        }
        $output .= $this->_echo_page(1);
        
        if ($start > 2) {
            $output .= '<span class="omit">...</span>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $output .= $this->_echo_page($i, $nofollow);
        }
        if ($end < $this->total_pages && $this->cur_page >= $this->num_links) {
            $output .= '<span class="omit">...</span>';
        }
        
        if ($this->cur_page < $this->total_pages) {
            $other_str = $nofollow ? 'rel="nofollow"' : '';
            $target_url = $this->uri->add_query('page', $this->cur_page + 1)->render() . '"' . $other_str;
            $output .= '<li class="next"><a href="' . $target_url . '>下一页</a></li>';
        }
        
        echo $output;
    }
    
    private function _is_current($num) {
        return $this->cur_page == $num;
    }

    private function _echo_page($num) {
        if ($this->_is_current($num)) {
            return '<li class="active"><a href="#">' . $num . '</a></li>';
        } else {
            $first_class = $num == 1 ? 'class="first_page"' : '';
            return '<li><a ' . $first_class . 'href="' . $this->uri->add_query('page', $num)->render() . '" >' . $num . '</a></li>';
        }
    }
}