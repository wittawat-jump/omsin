<?php
/**
 * @filesource modules/index/models/select.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Select;

use \Kotchasan\Database\Sql;

/**
 * Class สำหรับอ่านข้อมูลจากหมวดหมู่เพื่อใส่ลงใน select
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านปีที่มีการทำรายการ อย่างน้อยต้องมีปีปัจจุบัน
   *
   * @param int $owner_id
   * @return array
   */
  public static function getYears($owner_id)
  {
    // Model
    $model = new static;
    $query = $model->db()->createQuery()
      ->select(Sql::Year('create_date', 'year'))
      ->from('ierecord')
      ->where(array('owner_id', $owner_id))
      ->groupBy(Sql::Year('create_date'))
      ->cacheOn()
      ->toArray();
    $result = array();
    // ปีตาม พ.ศ.
    $year_offset = \Kotchasan\Language::get('YEAR_OFFSET');
    foreach ($query->execute() as $item) {
      $result[$item['year']] = $item['year'] + $year_offset;
    }
    // ปีนี้
    $y = (int)date('Y');
    $result[$y] = $y + $year_offset;
    return $result;
  }

  /**
   * กระเป๋าเงิน
   *
   * @param int $owner_id
   * @return array
   */
  public static function wallets($owner_id)
  {
    return self::toSelect($owner_id, 4);
  }

  /**
   * หมวดหมู่รายรับ/รายจ่าย
   *
   * @param int $owner_id
   * @param string $status รายรับ IN / รายจ่าย OUT
   * @return array
   */
  public static function ieCategories($owner_id, $status)
  {
    return self::toSelect($owner_id, $status == 'OUT' ? 2 : 1);
  }

  /**
   * อ่านข้อมูลลงใน select
   *
   * @param int $owner_id
   * @param int $typ
   * @return array
   */
  private static function toSelect($owner_id, $typ)
  {
    // Model
    $model = new static;
    $query = $model->db()->createQuery()
      ->select('category_id', 'topic')
      ->from('category')
      ->where(array(
        array('owner_id', $owner_id),
        array('id', $typ)
      ))
      ->order('owner_id DESC', 'topic')
      ->cacheOn()
      ->toArray();
    $result = array();
    foreach ($query->execute() as $item) {
      $result[$item['category_id']] = $item['topic'];
    }
    return $result;
  }
}