<?php
/**
 * @filesource modules/index/views/iedaily.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Iedaily;

use Kotchasan\Currency;
use Kotchasan\DataTable;
use Kotchasan\Http\Request;

/**
 * module=iedaily.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    private $wallet;
    private $categories;
    private $total = 0;

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
        $this->categories = array(
            'TRANSFER' => array(
                0 => '{LNG_Transfer between accounts}',
            ),
            'INIT' => array(
                0 => '{LNG_Summit}',
            ),
        );
        $this->wallet = array();
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
            'model' => \Index\Iereport\Model::daily($owner),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* ฟังก์ชั่นแสดงผล Footer */
            'onCreateFooter' => array($this, 'onCreateFooter'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => array('id', 'owner_id', 'expense', 'status', 'transfer_to'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/index/model/iereport/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'category_id' => array(
                    'text' => '{LNG_Category}',
                ),
                'wallet' => array(
                    'text' => '{LNG_Wallet}',
                ),
                'comment' => array(
                    'text' => '{LNG_Annotation}',
                    'class' => 'mobile',
                ),
                'income' => array(
                    'text' => '{LNG_Amount}',
                    'class' => 'center',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'comment' => array(
                    'class' => 'mobile',
                ),
                'income' => array(
                    'class' => 'right',
                ),
            ),
            'buttons' => array(
                'edit' => array(
                    'class' => 'icon-edit button notext green',
                    'href' => 'index.php?module=ieedit&amp;id=:id',
                    'title' => '{LNG_Edit}',
                ),
                'delete' => array(
                    'class' => 'icon-delete button notext red',
                    'id' => ':id',
                    'title' => '{LNG_Delete}',
                ),
            ),
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => array($this, 'onCreateButton'),
        ));

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
            $item['income'] = Currency::format($item['expense']);
        } else {
            $this->total += ($item['income'] - $item['expense']);
            $item['wallet'] = isset($this->wallet[$item['wallet']]) ? $this->wallet[$item['wallet']] : 'Unknow';
            if ($item['income'] > 0) {
                $item['income'] = '<span class=color-green>+'.Currency::format($item['income']).'</span>';
            } else {
                $item['income'] = '<span class=color-red>-'.Currency::format($item['expense']).'</span>';
            }
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
        return '<tr><td class=right colspan=2>{LNG_Total}</td><td class=mobile></td><td class="right color-'.($this->total < 0 ? 'red' : 'green').'">'.Currency::format($this->total).'</td><td></td></tr>';
    }

    /**
     * ฟังกชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่.
     *
     * @param array $item
     *
     * @return array
     */
    public function onCreateButton($btn, $attributes, $items)
    {
        return $btn != 'edit' || $items['status'] == 'IN' || $items['status'] == 'OUT' || $items['status'] == 'INIT' ? $attributes : false;
    }
}
