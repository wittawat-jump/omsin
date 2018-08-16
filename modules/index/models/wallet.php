<?php
/**
 * @filesource modules/index/models/wallet.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Wallet;

use Kotchasan\Currency;
use Kotchasan\Database\Sql;

/**
 * ฟังก์ชั่นเกี่ยวกับกระเป๋าเงิน.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านจำนวนเงินในกระเป๋า.
     *
     * @param int $account_id
     * @param int $wallet
     *
     * @return float
     */
    public static function getMoney($account_id, $wallet)
    {
        // query ข้อมูล ทั้งหมด
        $q1 = static::createQuery()
            ->select('income', 'expense')
            ->from('ierecord')
            ->where(array(
                array('account_id', $account_id),
                array('wallet', $wallet),
            ));
        // query ข้อมูลโอนเงินระหว่างบัญชีไปเป็นรายรับของบัญชีปลายทาง
        $q2 = static::createQuery()
            ->select('expense income', '0 expense')
            ->from('ierecord')
            ->where(array(
                array('account_id', $account_id),
                array('status', 'TRANSFER'),
                array('transfer_to', $wallet),
            ));
        $result = static::createQuery()
            ->from(array(static::createQuery()->unionAll($q1, $q2), 'Z'))
            ->toArray()
            ->first(Sql::SUM('income', 'income'), Sql::SUM('expense', 'expense'));

        return $result['income'] - $result['expense'];
    }

    /**
     * คืนค่ากระเป๋าเงิน และจำนวนเงินทั้งหมดในกระเป๋า.
     *
     * @param int $account_id
     *
     * @return array
     */
    public static function toSelect($account_id)
    {
        $q1 = static::createQuery()
            ->select('wallet', 'income', 'expense')
            ->from('ierecord')
            ->where(array('account_id', $account_id));
        $q2 = static::createQuery()
            ->select('transfer_to wallet', 'expense income', '0 expense')
            ->from('ierecord')
            ->where(array(
                array('account_id', $account_id),
                array('status', 'TRANSFER'),
            ));
        $query = static::createQuery()
            ->select('wallet', 'C.topic', Sql::create('SUM(`income`-`expense`) AS `money`'))
            ->from(array(static::createQuery()->unionAll($q1, $q2), 'I'))
            ->join('category C', 'INNER', array(
                array('C.account_id', $account_id),
                array('C.id', 4),
                array('C.category_id', 'I.wallet'),
            ))
            ->groupBy('wallet');
        $result = array();
        foreach ($query->execute() as $item) {
            $result[$item->wallet] = $item->topic.' ('.Currency::format($item->money).')';
        }

        return $result;
    }
}
