<?php
/**
 * @filesource modules/index/models/dashboard.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Dashboard;

use Kotchasan\Database\Sql;

/**
 * module=dashboard.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลสมาชิกที่ $id.
     *
     * @param int $id
     *
     * @return array|null คืนค่า array ข้อมูลสมาชิก ไม่พบคืนค่า null
     */
    public static function get($owner_id)
    {
        // วันนี้
        $today = date('Y-m-d');
        // Model
        $model = new \Kotchasan\Model();
        // query ข้อมูล ทั้งหมด
        $q1 = $model->db()->createQuery()
            ->select('wallet', 'status', Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'), 'owner_id')
            ->from('ierecord')
            ->where(array('owner_id', $owner_id))
            ->groupBy('wallet', 'status');
        // query ข้อมูลโอนเงินระหว่างบัญชีไปเป็นรายรับของบัญชีปลายทาง
        $q2 = $model->db()->createQuery()
            ->select('transfer_to wallet', 'status', Sql::SUM('expense', 'income'), '0 expense', 'owner_id')
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner_id),
                array('status', 'TRANSFER'),
            ))
            ->groupBy('transfer_to');
        // สรุปรายละเอียดบัญชีตามกระเป๋าเงินและรายการบัญชี
        $q3 = $model->db()->createQuery()
            ->select('G.topic', 'Q.status', 'Q.income', 'Q.expense')
            ->from(array($model->db()->createQuery()->union($q1, $q2), 'Q'))
            ->join('category G', 'LEFT', array(
                array('G.owner_id', 'Q.owner_id'),
                array('G.id', 4),
                array('G.category_id', 'Q.wallet'),
            ));
        // รายรับรายจ่ายวันนี้
        $q4 = $model->db()->createQuery()
            ->select('0 topic', 'F.status', Sql::SUM('F.income', 'income'), Sql::SUM('F.expense', 'expense'))
            ->from('ierecord F')
            ->where(array(
                array('F.owner_id', $owner_id),
                array('F.create_date', $today),
                array('F.status', array('IN', 'OUT')),
            ));

        return $model->db()->createQuery()->union($q3, $q4)->toArray()->execute();
    }
}
