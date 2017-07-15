<?php
$config = array(
        'member/login' => array(
                array(
                        'field' => 'email', 
                        'label' => '邮箱', 
                        'rules' => 'trim|required|valid_email'
                ), 
                array(
                        'field' => 'passwd', 
                        'label' => '密码', 
                        'rules' => 'required'
                ),
                array(
                        'field' => 'checkcode', 
                        'label' => '验证码', 
                        'rules' => 'callback__validate_checkcode'
                )
        ),
        'member/register' => array(
                array(
                        'field' => 'email', 
                        'label' => '邮箱', 
                        'rules' => 'trim|required|valid_email'
                ), 
                array(
                        'field' => 'passwd', 
                        'label' => '登录密码', 
                        'rules' => 'required|pregmatch[/^[a-zA-Z0-9@!#$%^&*()-=_+]{6,16}$/]|min_length[6]|max_length[16]'
                ),
                array(
                        'field' => 'repasswd', 
                        'label' => '确认密码', 
                        'rules' => 'required|matches[passwd]'
                ),
                array(
                        'field' => 'checkcode', 
                        'label' => '验证码', 
                        'rules' => 'required|callback__validate_checkcode'
                )
        ),
        'member/available' => array(
                array(
                        'field' => 'email', 
                        'label' => '邮箱', 
                        'rules' => 'trim|required|valid_email|unique[member.user_name]'
                )
        ),
        'base_form' => array()
);
