<?php
/**
 * @filesource modules/index/models/register.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Register;

use Gcms\Email;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Validator;

/**
 * ลงทะเบียนสมาชิกใหม่.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * บันทึกข้อมูล (register.php).
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token
        if ($request->initSession() && $request->isSafe()) {
            if (self::$cfg->demo_mode == false) {
                // รับค่าจากการ POST
                $save = array(
                    'username' => $request->post('register_email')->url(),
                    'name' => $request->post('register_name')->topic(),
                );
                // username
                if (empty($save['username'])) {
                    $ret['ret_register_email'] = 'Please fill in';
                } elseif (!Validator::email($save['username'])) {
                    $ret['ret_register_email'] = Language::replace('Incorrect :name', array(':name' => Language::get('Email')));
                } else {
                    // ตรวจสอบ username ซ้ำ
                    $search = $this->db()->first($this->getTableName('user'), array('username', $save['username']));
                    if ($search) {
                        $ret['ret_register_email'] = Language::replace('This :name already exist', array(':name' => Language::get('Email')));
                    }
                }
                // name
                if (empty($save['name'])) {
                    $ret['ret_register_name'] = 'Please fill in';
                }
                // password
                $password = $request->post('register_password')->password();
                if (mb_strlen($password) < 4) {
                    // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
                    $ret['ret_register_password'] = 'Please fill in';
                } else {
                    $save['password'] = $password;
                }
                if (empty($ret)) {
                    // ลงทะเบียนสมาชิกใหม่
                    $save['fb'] = 0;
                    self::execute($this, $save);
                    // ส่งอีเมล
                    $replace = array(
                        '/%NAME%/' => $save['name'],
                        '/%EMAIL%/' => $save['username'],
                        '/%PASSWORD%/' => $password,
                    );
                    Email::send(2, 'member', $replace, $save['username']);
                    // คืนค่า
                    $ret['alert'] = Language::replace('Register successfully, We have sent complete registration information to :email', array(':email' => $save['username']));
                    $ret['location'] = 'index.php?action=login';
                    // clear
                    $request->removeToken();
                }
            } else {
                // โหมดตัวอย่าง ไม่สามารถ register ได้
                $ret['alert'] = Language::get('Unable to complete the transaction');
            }
        }
        // คืนค่าเป็น JSON
        if (!empty($ret)) {
            echo json_encode($ret);
        }
    }

    /**
     * ลงทะเบียนสมาชิกใหม่.
     *
     * @param Model $model
     * @param array $save  ข้อมูลสมาชิก
     *
     * @return array คืนค่าแอเรย์ของข้อมูลสมาชิกใหม่
     */
    public static function execute($model, $save)
    {
        if (!isset($save['username'])) {
            $save['username'] = '';
        }
        if (!isset($save['password'])) {
            $save['password'] = '';
        } else {
            $save['salt'] = uniqid();
            $save['password'] = sha1($save['password'].$save['salt']);
        }
        $save['create_date'] = time();
        // บันทึกลงฐานข้อมูล
        $save['id'] = $model->db()->insert($model->getTableName('user'), $save);

        return $save;
    }
}
