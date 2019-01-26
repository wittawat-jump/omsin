<?php
/**
 * @filesource modules/index/models/member.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Member;

/**
 * ตารางสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลสมาชิกที่ $id
   * คืนค่า array ข้อมูลสมาชิก ไม่พบคืนค่า null.
   *
   * @param int $id
   *
   * @return array|null
   */
  public static function get($id)
  {
    return static::createQuery()
        ->from('user U')
        ->where(array('U.id', $id))
        ->toArray()
        ->first();
  }
}