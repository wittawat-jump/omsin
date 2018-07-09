<?php
/**
 * @filesource modules/index/views/search.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Search;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=search.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var mixed
     */
    private $wallet;
    /**
     * @var mixed
     */
    private $categories;
    /**
     * @var int
     */
    private $total = 0;
    /**
     * @var int
     */
    private $wallet_id = 0;

    /**
     * รายงานรายวัน.
     *
     * @param Request $request
     * @param array   $owner   ข้อมูลที่ต้องการ
     *
     * @return string
     */
    public function render(Request $request, $owner)
    {
        $this->wallet_id = $owner['wallet'];
        $this->categories = array(
            'TRANSFER' => array(
                0 => '{LNG_Transfer between accounts}',
            ),
            'INIT' => array(
                0 => '{LNG_Summit}',
            ),
        );
        $this->wallet = array(0 => '{LNG_all items}');
        foreach (\Index\Category\Model::all($owner['id'], array(1, 2, 4)) as $item) {
            if ($item['id'] == 1) {
                // หมวดหมู่รายรับ
                $this->categories['IN'][$item['category_id']] = $item['topic'];
            } elseif ($item['id'] == 2) {
                // หมวดหมู่รายจ่าย
                $this->categories['OUT'][$item['category_id']] = $item['topic'];
            } elseif ($item['id'] == 4) {
                // กระเป๋าเงิน
                $this->wallet[$item['category_id']] = $item['topic'];
            }
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Index\Iereport\Model::search($owner),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => array(
                array(
                    'name' => 'wallet',
                    'text' => '{LNG_Wallet}',
                    'options' => $this->wallet,
                    'value' => $owner['wallet'],
                ),
                array(
                    'name' => 'status',
                    'text' => '{LNG_Type}',
                    'options' => array('' => '{LNG_all items}', 'IN' => '{LNG_Income}', 'OUT' => '{LNG_Expense}', 'TRANSFER' => '{LNG_Transfer between accounts}', 'INIT' => '{LNG_Summit}'),
                    'value' => $owner['status'],
                ),
                array(
                    'name' => 'year',
                    'text' => '{LNG_year}',
                    'options' => array(0 => '{LNG_all items}') + \Index\Select\Model::getYears($owner['id']),
                    'value' => $owner['year'],
                ),
                array(
                    'name' => 'month',
                    'text' => '{LNG_month}',
                    'options' => array(0 => '{LNG_all items}') + Language::get('MONTH_LONG'),
                    'value' => $owner['month'],
                ),
            ),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('search_perPage', 30)->toInt(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* ฟังก์ชั่นแสดงผล Footer */
            'onCreateFooter' => array($this, 'onCreateFooter'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'owner_id', 'status', 'expense', 'transfer_to'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('comment'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/index/model/iereport/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'delete' => '{LNG_Delete}',
                    ),
                ),
            ),
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'create_date' => array(
                    'text' => '{LNG_date}',
                ),
                'category_id' => array(
                    'text' => '{LNG_Category}',
                ),
                'wallet' => array(
                    'text' => '{LNG_Wallet}',
                ),
                'comment' => array(
                    'text' => '{LNG_Annotation}',
                ),
                'income' => array(
                    'text' => '{LNG_Amount}',
                    'class' => 'center',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'income' => array(
                    'class' => 'right',
                ),
            ),
        ));
        // save cookie
        setcookie('search_perPage', $table->perPage, time() + 2592000, '/', null, null, true);

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array $item
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        if ($item['status'] == 'INIT') {
            $item['category_id'] = '{LNG_Summit}';
        } else {
            $item['category_id'] = isset($this->categories[$item['status']][$item['category_id']]) ? $this->categories[$item['status']][$item['category_id']] : 'Unknow';
        }
        if ($item['status'] == 'TRANSFER') {
            $res = array(
                isset($this->wallet[$item['wallet']]) ? $this->wallet[$item['wallet']] : 'Unknow',
                isset($this->wallet[$item['transfer_to']]) ? $this->wallet[$item['transfer_to']] : 'Unknow',
            );
            $item['wallet'] = implode(' =&gt; ', $res);
            if ($this->wallet_id > 0 && $this->wallet_id == $item['transfer_to']) {
                $item['income'] = $item['expense'];
                $item['expense'] = 0;
            }
        } else {
            $item['wallet'] = isset($this->wallet[$item['wallet']]) ? $this->wallet[$item['wallet']] : 'Unknow';
        }
        $this->total += ($item['income'] - $item['expense']);
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        if ($item['income'] > 0) {
            $item['income'] = '<span class=color-green>+'.Currency::format($item['income']).'</span>';
        } else {
            $item['income'] = '<span class=color-red>-'.Currency::format($item['expense']).'</span>';
        }

        return $item;
    }

    /**
     * ฟังก์ชั่นสร้างแถวของ footer.
     *
     * @return string
     */
    public function onCreateFooter()
    {
        return '<tr><td class=right colspan=3>{LNG_Total}</td><td class=mobile></td><td class="right color-'.($this->total < 0 ? 'red' : 'green').'">'.Currency::format($this->total).'</td></tr>';
    }
}
