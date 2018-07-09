<?php
/**
 * @filesource modules/index/models/iereport.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Iereport;

use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Login;

/**
 * Model สำหรับการออกรายงาน.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * สรุปรายงานรายรับรายจ่ายทั้งหมด แยกรายปี.
     *
     * @param array $owner
     *
     * @return array
     */
    public static function summary($owner)
    {
        $model = new static();
        $q1 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'))
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array('category_id', '>', 0),
            ))
            ->groupBy(Sql::YEAR('create_date'))
            ->order('create_date DESC')
            ->toArray();
        $q2 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'category_id', Sql::SUM('expense', 'expense'))
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array('status', 'OUT'),
                array('category_id', '>', 0),
            ))
            ->groupBy('category_id')
            ->order('expense DESC')
            ->toArray();

        return array(
            'summary' => $q1->execute(),
            'category' => $q2->execute(),
        );
    }

    /**
     * สรุปรายงานรายรับรายจ่ายทั้งหมด ปีที่เลือก แยกรายเดือน.
     *
     * @param array $owner
     *
     * @return array
     */
    public static function yearly($owner)
    {
        $model = new static();
        $q1 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', 'income', 'expense')
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array('category_id', '>', 0),
            ));
        $q2 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', '0 income', '0 expense')
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array('category_id', 0),
            ));
        $q1 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'))
            ->from(array($model->db()->createQuery()->unionAll($q1, $q2), 'Z'))
            ->groupBy(Sql::YEAR('create_date'), Sql::MONTH('create_date'))
            ->toArray();
        $q2 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'category_id', Sql::SUM('expense', 'expense'))
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array('status', 'OUT'),
                array('category_id', '>', 0),
            ))
            ->groupBy('category_id')
            ->order('expense DESC')
            ->toArray();

        return array(
            'summary' => $q1->execute(),
            'category' => $q2->execute(),
        );
    }

    /**
     * สรุปรายงานรายรับรายจ่ายทั้งหมด ปีและเดือนที่เลือก แยกรายวัน.
     *
     * @param array $owner
     *
     * @return array
     */
    public static function monthly($owner)
    {
        $model = new static();
        $q1 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', 'income', 'expense')
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array(Sql::MONTH('create_date'), (int) $owner['month']),
                array('status', array('IN', 'OUT')),
            ));
        $q2 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', '0 income', '0 expense')
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array(Sql::MONTH('create_date'), (int) $owner['month']),
                array('status', array('INIT', 'TRANSFER')),
            ));
        $q1 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'create_date', Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'))
            ->from(array($model->db()->createQuery()->unionAll($q1, $q2), 'Z'))
            ->groupBy(Sql::DAY('create_date'))
            ->toArray();
        $q2 = $model->db()->createQuery()
            ->select('id', 'owner_id', 'category_id', Sql::SUM('expense', 'expense'))
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array(Sql::YEAR('create_date'), (int) $owner['year']),
                array(Sql::MONTH('create_date'), (int) $owner['month']),
                array('status', 'OUT'),
                array('category_id', '>', 0),
            ))
            ->groupBy('category_id')
            ->order('expense DESC')
            ->toArray();

        return array(
            'summary' => $q1->execute(),
            'category' => $q2->execute(),
        );
    }

    /**
     * สรุปรายงานรายรับรายจ่ายทั้งหมด วันที่เลือก
     *
     * @param array $owner
     *
     * @return array
     */
    public static function daily($owner)
    {
        $model = new static();

        return $model->db()->createQuery()
            ->select(
                'id', 'owner_id', 'category_id', 'wallet', 'comment', 'income', 'expense', 'status', 'transfer_to'
            )
            ->from('ierecord')
            ->where(array(
                array('owner_id', $owner['id']),
                array('create_date', $owner['date']),
            ));
    }

    /**
     * รายงานที่กำหนดเอง.
     *
     * @param array $owner
     *
     * @return array
     */
    public static function search($owner)
    {
        $model = new static();
        $where = array(
            array('owner_id', $owner['id']),
        );
        if (!empty($owner['wallet'])) {
            $where[] = $model->groupOr(array(
                array('wallet', $owner['wallet']),
                array('transfer_to', $owner['wallet']),
            ));
        }
        if (!empty($owner['status'])) {
            $where[] = array('status', $owner['status']);
        }
        if (!empty($owner['year'])) {
            $where[] = array(Sql::YEAR('create_date'), $owner['year']);
        }
        if (!empty($owner['month'])) {
            $where[] = array(Sql::MONTH('create_date'), $owner['month']);
        }

        return $model->db()->createQuery()
            ->select(
                'id', 'owner_id', 'create_date', 'category_id', 'wallet', 'comment', 'income', 'expense', 'status', 'transfer_to'
            )
            ->from('ierecord')
            ->where($where)
            ->order('create_date DESC');
    }

    /**
     * รับค่าจาก action.
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            $ret = array();
            // รับค่าจากการ POST
            $action = $request->post('action')->toString();
            if ($action === 'delete') {
                $id = $request->post('id')->toInt();
                // Model
                $model = new \Kotchasan\Model();
                $model->db()->delete($model->getTableName('ierecord'), array(
                    array('owner_id', $login['id']),
                    array('id', $id),
                ));
                $ret['remove'] = $request->post('src')->toString().'_'.$id;
            }
            if (!empty($ret)) {
                // คืนค่า JSON
                echo json_encode($ret);
            }
        }
    }
}
