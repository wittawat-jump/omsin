<?php
/**
 * @filesource modules/index/models/wallet.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Wallet;

use \Kotchasan\Database\Sql;

/**
 * ฟังก์ชั่นเกี่ยวกับกระเป๋าเงิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านจำนวนเงินในกระเป๋า
   *
   * @param int $owner_id
   * @param int $wallet
   * @return double
   */
  public static function getMoney($owner_id, $wallet)
  {
    $model = new static;
    // query ข้อมูล ทั้งหมด
    $q1 = $model->db()->createQuery()
      ->select('income', 'expense')
      ->from('ierecord')
      ->where(array(
      array('owner_id', $owner_id),
      array('wallet', $wallet)
    ));
    // query ข้อมูลโอนเงินระหว่างบัญชีไปเป็นรายรับของบัญชีปลายทาง
    $q2 = $model->db()->createQuery()
      ->select('expense income', '0 expense')
      ->from('ierecord')
      ->where(array(
      array('owner_id', $owner_id),
      array('status', 'TRANSFER'),
      array('transfer_to', $wallet)
    ));
    $result = $model->db()->createQuery()
      ->from(array($model->db()->createQuery()->unionAll($q1, $q2), 'Z'))
      ->toArray()
      ->first(Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'));
    return $result['income'] - $result['expense'];
  }
}