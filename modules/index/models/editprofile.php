<?php
/**
 * @filesource modules/index/models/editprofile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Editprofile;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * module=editprofile
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูล user
   * คืนค่ารายการใหม่ถ้า $id = 0
   *
   * @param int $id
   * @return array|null คืนค่า array ของข้อมูล ไม่พบคืนค่า null
   */
  public static function get($id)
  {
    if (empty($id)) {
      // ใหม่ $id = 0
      return array(
        'username' => '',
        'name' => '',
        'status' => 3,
        'id' => $id,
      );
    } else {
      // ตรวจสอบรายการที่เลือก
      $model = new static;
      return $model->db()->createQuery()
          ->from('user')
          ->where(array('id', $id))
          ->toArray()
          ->first();
    }
  }

  /**
   * บันทึกข้อมูลสมาชิก
   *
   * @param Request $request
   */
  public function submit(Request $request)
  {
    $ret = array();
    // session, token, member
    if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
      // รับค่าจากการ POST
      $save = array(
        'username' => $request->post('register_username')->url(),
        'name' => $request->post('register_name')->topic(),
      );
      // ตรวจสอบค่าที่ส่งมา
      $index = self::get($request->post('register_id')->toInt());
      if (!$index) {
        // ไม่พบข้อมูลที่แก้ไข
        $ret['alert'] = Language::get('not a registered user');
      } elseif ($save['username'] == '') {
        // ไม่ได้กรอก username
        $ret['ret_register_username'] = 'Please fill in';
      } elseif ($save['name'] == '') {
        // ไม่ได้กรอก ชื่อ
        $ret['ret_register_name'] = 'Please fill in';
      } else {
        // Model
        $model = new \Kotchasan\Model;
        // ชื่อตาราง user
        $table_user = $model->getTableName('user');
        // database connection
        $db = $model->db();
        // แอดมิน
        $isAdmin = Login::isAdmin();
        // ไม่ใช่แอดมิน ใช้อีเมล์เดิมจากฐานข้อมูล
        if (!$isAdmin && $index['id'] > 0) {
          $save['username'] = $index['username'];
        }
        // ตรวจสอบค่าที่ส่งมา
        $requirePassword = false;
        // ตรวจสอบ username ซ้ำ
        $search = $db->first($table_user, array('username', $save['username']));
        if ($search !== false && $index['id'] != $search->id) {
          // มี username อยู่ก่อนแล้ว
          $ret['ret_register_username'] = Language::replace('This :name already exist', array(':name' => Language::get('Email')));
        } else {
          $requirePassword = $index['username'] !== $save['username'];
        }
        // password
        $password = $request->post('register_password')->password();
        $repassword = $request->post('register_repassword')->password();
        if (!empty($password) || !empty($repassword)) {
          if (mb_strlen($password) < 4) {
            // รหัสผ่านต้องไม่น้อยกว่า 4 ตัวอักษร
            $ret['ret_register_password'] = 'this';
          } elseif ($repassword != $password) {
            // ถ้าต้องการเปลี่ยนรหัสผ่าน กรุณากรอกรหัสผ่านสองช่องให้ตรงกัน
            $ret['ret_register_repassword'] = 'this';
          } else {
            $save['salt'] = uniqid();
            $save['password'] = sha1($password.$save['salt']);
            $requirePassword = false;
          }
        }
        // มีการเปลี่ยน email ต้องการรหัสผ่าน
        if (empty($ret) && $requirePassword) {
          $ret['ret_register_password'] = 'this';
        }
        if (empty($ret)) {
          // social ห้ามแก้ไข username และรหัสผ่าน
          if (!empty($index['fb'])) {
            unset($save['username']);
            unset($save['password']);
          }
          // แก้ไข
          $db->update($table_user, $index['id'], $save);
          if ($login['id'] == $index['id']) {
            // ตัวเอง
            if (isset($save['password'])) {
              if (isset($save['username'])) {
                $_SESSION['login']['username'] = $save['username'];
              }
              $_SESSION['login']['password'] = $password;
            }
          }
          // คืนค่า
          $ret['alert'] = Language::get('Saved successfully');
          $ret['location'] = 'reload';
          // เคลียร์
          $request->removeToken();
        }
      }
    }
    if (empty($ret)) {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}