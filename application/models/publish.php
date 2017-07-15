<?php

class Publish extends MZ_Model {

    public function get_by_status($user_id, $status) {
        $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE user_id=? AND status=?";
        return $this->execute($sql, array($user_id, $status))->result();
    }

    public function get_by_uid($user_id) {
        $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE user_id=?";
        return $this->execute($sql, array($user_id))->result();
    }

    public function get_by_issue_id($issue_id) {
        $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE `issue_id`=?;";
        return $this->execute($sql, array($issue_id))->row();
    }

    public function assign_to_by_uid($user_id) {
        $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE second_person=? AND status=0";
        return $this->execute($sql, array($user_id))->result();
    }

    public function new_issue($author_id, $user_id, $issue_id, $desc, $publish_date, $type) {
        $data_array = array(
                'author_id' => $author_id,
                'user_id' => $user_id, 
                'issue_id' => $issue_id, 
                'desc' => $desc,
                'publish_date' => $publish_date, 
                'type' => $type);
        $this->insert('publish', $data_array);
        return $this->insert_id();
    }

    public function update_by_id($issue_id, $second_person_id, $version, $publish_time, $grand_total) {
        $data_array = array(
                'second_person' => $second_person_id, 
                'version' => $version,
                'publish_date' => $publish_time,
                'grand_total' => $grand_total,
                'status' => 0);
        $this->update('publish', $data_array, array('issue_id' => $issue_id));
        return $this->affected_rows();
    }

    public function checked($issue_id, $status) {
        $now = $_SERVER['REQUEST_TIME'];
        $data_array = array(
                'status' => $status,
                'last_check' => $now);
        $this->update('publish', $data_array, array('issue_id' => $issue_id));
        return $this->affected_rows();
    }

    public function published_today($td = FALSE, $status = '') {
        $now = $_SERVER['REQUEST_TIME'];
        
        $today = strtotime(date('Y-m-d', $now));
        $yesterday = $today - 86400;
        $tomorrow = $today + 86400;
        if ($td) {
            $time_begin = $today;
            $time_end = $tomorrow;
        } else {
            $time_begin = $yesterday;
            $time_end = $today;
        }

        if ($status == '') {
            $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE publish_date>=? AND publish_date < ?";
            return $this->execute($sql, array($time_begin, $time_end))->result();
        }
        $sql = "SELECT `author_id`, `user_id`, `issue_id`, `desc`, `publish_date`, `type`, `version`, `second_person`, `status`, `grand_total` FROM `publish` WHERE publish_date>=? AND publish_date < ? AND status=?";
        return $this->execute($sql, array($time_begin, $time_end, $status))->result();

        
    }
}