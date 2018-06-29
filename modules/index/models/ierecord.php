<?php
/**
 * @filesource modules/index/models/ierecord.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Ierecord;

use Kotchasan\Currency;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * module=ierecord.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูล บัญชีรายรัย-รายจ่าย ที่ $id.
     *
     * @param int  $owner_id
     * @param int  $id
     * @param bool $new      false (default) คืนค่า null ถ้าไม่พบ, true คืนค่ารายการใหม่ถ้า $id = 0
     *
     * @return object|null
     */
    public static function get($owner_id, $id, $new = false)
    {
        if ($id > 0) {
            // แก้ไข, อ่านรายการที่เลือก
            $model = new \Kotchasan\Model();

            return $model->db()->createQuery()
                ->from('ierecord')
                ->where(array(
                    array('owner_id', (int) $owner_id),
                    array('id', (int) $id),
                ))
                ->first();
        } elseif ($new) {
            // ใหม่
            return (object) array(
                'owner_id' => $owner_id,
                'id' => 0,
            );
        }

        return null;
    }

    /**
     * บันทึกข้อมูล.
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        // session, token, member
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            $ret = array();
            // รับค่าจากการ POST
            $owner_id = $request->post('write_owner_id')->toInt();
            $status = $request->post('write_status')->filter('A-Z');
            // รายการที่เลือก
            $index = self::get($owner_id, $request->post('write_id')->toInt(), true);
            if ($index && $index->owner_id == $login['id']) {
                if ($index->id > 0) {
                    // แก้ไขใช้สถานะเดิม
                    $status = $index->status;
                }
                if ($status == 'TRANSFER') {
                    // โอนเงินระหว่างบัญชี
                    $ret = self::transfer($request, $index);
                } elseif ($status == 'INIT') {
                    // กระเป๋าเงิน
                    $ret = self::wallet($request, $index);
                } else {
                    // บันทึก รายรับ/รายจ่าย
                    $ret = self::recording($request, $status, $index);
                }
                if (empty($ret)) {
                    // คืนค่า
                    $ret['alert'] = Language::get('Saved successfully');
                    $ret['location'] = $index->id == 0 ? 'reload' : 'back';
                    // เคลียร์
                    $request->removeToken();
                }
            }
        }
        if (empty($ret)) {
            // ไม่มีสิทธิ์
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }

    /**
     * บันทึก รายรับ/รายจ่าย.
     *
     * @param Request $request
     * @param string  $status
     * @param object  $index
     */
    private static function recording(Request $request, $status, $index)
    {
        $ret = array();
        if ($index->id == 0) {
            // หมวดหมู่ (รายการใหม่)
            $method = $status == 'IN' ? 'newIncome' : 'newExpensive';
            $category_id = \Index\Category\Model::$method($index->owner_id, $request->post('write_category')->topic());
        } else {
            // หมวดหมู่ (แก้ไข)
            $category_id = $request->post('write_category')->toInt();
        }
        if ($category_id == 0) {
            // ไม่ได้กรอกหมวดหมู่
            $ret['ret_write_category'] = 'this';
        }
        // จำนวนเงิน
        $amount = $request->post('write_amount')->toDouble();
        if ($amount == 0) {
            // ไม่ได้กรอกจำนวนเงิน
            $ret['ret_write_amount'] = 'Please fill in';
        }
        if (empty($ret)) {
            $model = new \Kotchasan\Model();
            $table_name = $model->getTableName('ierecord');
            // save
            $save = array(
                'comment' => $request->post('write_comment')->topic(),
                'create_date' => $request->post('write_create_date')->date(),
                'category_id' => $category_id,
                'wallet' => $request->post('write_wallet')->toInt(),
            );
            if ($status == 'IN') {
                $save['income'] = $amount;
                $save['expense'] = 0;
            } else {
                $save['expense'] = $amount;
                $save['income'] = 0;
            }
            if ($index->id == 0) {
                // ใหม่
                $save['id'] = Sql::NEXT('id', $table_name, array('owner_id', $index->owner_id));
                $save['owner_id'] = $index->owner_id;
                $save['status'] = $status;
                $save['transfer_to'] = 0;
                $model->db()->insert($table_name, $save);
            } else {
                // แก้ไข
                $where = array(
                    array('owner_id', $index->owner_id),
                    array('id', $index->id),
                );
                $model->db()->update($table_name, $where, $save);
            }
            // save cookie
            setcookie('ierecord_wallet', $save['wallet'], time() + 2592000, '/', null, null, true);
        }

        return $ret;
    }

    /**
     * โอนเงินระหว่างบัญชี.
     *
     * @param Request $request
     * @param object  $index
     */
    private static function transfer(Request $request, $index)
    {
        $ret = array();
        // ค่าที่ส่งมา
        $amount = $request->post('write_amount')->toDouble();
        $from = $request->post('write_from')->toInt();
        $to = $request->post('write_to')->toInt();
        if ($from == $to) {
            // เลือกบัญชีเดียวกัน
            $ret['ret_write_to'] = Language::get('Please select a different account');
        } elseif ($amount == 0) {
            // ไม่ได้กรอกจำนวนเงิน
            $ret['ret_write_amount'] = 'Please fill in';
        } else {
            // อ่านจำนวนเงินในกระเป๋า
            $money = \Index\Wallet\Model::getMoney($index->owner_id, $from);
            if ($amount > $money) {
                // จำนวนเงินที่จะโอนมากกว่าในกระเป๋า
                $ret['ret_write_amount'] = Language::replace('Fill in more money in pocket (:amount)', array(':amount' => Currency::format($money)));
            } else {
                $model = new \Kotchasan\Model();
                $table_name = $model->getTableName('ierecord');
                // query ID ถัดไป
                $q1 = Sql::NEXT('id', $table_name, array('owner_id', $index->owner_id), 'id');
                $query = $model->db()->createQuery()->toArray()->first($q1);
                // โอนเงินออก
                $save = array(
                    'owner_id' => $index->owner_id,
                    'id' => $query['id'],
                    'comment' => $request->post('write_comment')->topic(),
                    'create_date' => $request->post('write_create_date')->date(),
                    'category_id' => 0,
                    'wallet' => $from,
                    'status' => 'TRANSFER',
                    'income' => 0,
                    'expense' => $amount,
                    'transfer_to' => $to,
                );
                $model->db()->insert($table_name, $save);
            }
        }

        return $ret;
    }

    /**
     * เพิ่มกระเป๋าเงิน.
     *
     * @param Request $request
     * @param object  $index
     */
    private static function wallet(Request $request, $index)
    {
        $ret = array();
        // Model
        $model = new \Kotchasan\Model();
        // ตาราง ierecord
        $table_name = $model->getTableName('ierecord');
        if ($index->id == 0) {
            // ชื่อ กระเป๋าเงิน
            $wallet = $request->post('write_wallet_name')->topic();
            // ตรวจสอบกระเป๋าเงินซ้ำ
            $search = $model->db()->createQuery()
                ->from('category')
                ->where(array(
                    array('owner_id', $index->owner_id),
                    array('id', 4),
                    array('topic', $wallet),
                ))
                ->toArray()
                ->first('category_id');
            if ($search) {
                // มีกระเป๋าเงินนี้อยู่แล้ว
                $ret['ret_write_wallet_name'] = Language::replace('This :name already exist', array(':name' => Language::get('Wallet')));
            } else {
                // อ่าน ID ของกระเป๋าเงินใหม่
                $search = $model->db()->createQuery()
                    ->from('category')
                    ->where(array(
                        array('owner_id', $index->owner_id),
                        array('id', 4),
                    ))
                    ->toArray()
                    ->first(Sql::MAX('category_id', 'category_id'));
                $wallet_id = empty($search['category_id']) ? 1 : (1 + (int) $search['category_id']);
                // สร้างกระเป๋าเงิน
                $model->db()->insert($model->getTableName('category'), array(
                    'owner_id' => $index->owner_id,
                    'id' => 4,
                    'category_id' => $wallet_id,
                    'topic' => $wallet,
                ));
                $amount = $request->post('write_amount')->toDouble();
                if ($amount > 0) {
                    // บันทึก ยอดยกมา ถ้ามีการระบุจำนวนเงินมาด้วย
                    $model->db()->insert($table_name, array(
                        'owner_id' => $index->owner_id,
                        'id' => Sql::NEXT('id', $table_name, array('owner_id', $index->owner_id)),
                        'comment' => $request->post('write_comment')->topic(),
                        'create_date' => $request->post('write_create_date')->date(),
                        'category_id' => 0,
                        'wallet' => $wallet_id,
                        'status' => 'INIT',
                        'income' => $amount,
                        'expense' => 0,
                        'transfer_to' => 0,
                    ));
                }
            }
        } else {
            // แก้ไขรายการ ยอดยกมา
            $where = array(
                array('owner_id', $index->owner_id),
                array('id', $index->id),
            );
            $model->db()->update($table_name, $where, array(
                'comment' => $request->post('write_comment')->topic(),
                'create_date' => $request->post('write_create_date')->date(),
                'income' => $request->post('write_amount')->toDouble(),
                'expense' => 0,
            ));
        }

        return $ret;
    }
}
