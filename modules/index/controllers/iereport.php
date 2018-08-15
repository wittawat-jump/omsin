<?php
/**
 * @filesource modules/index/controllers/iereport.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Iereport;

use Kotchasan\Date;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * module=iereport.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * รายงาน.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Income and Expenditure summary');
        // เลือกเมนู
        $this->menu = 'tools';
        // สมาชิก
        if ($login = Login::isMember()) {
            // ค่าที่ส่งมา
            $index = array(
                'id' => $login['id'],
                'year' => $request->request('year')->toInt(),
                'month' => $request->request('month')->toInt(),
                'date' => $request->request('date')->date(),
            );
            if (!empty($index['date'])) {
                if ($index['date'] == date('Y-m-d')) {
                    // รายรับรายจ่ายวันนี้
                    $this->title .= ' '.Language::get('today');
                    // เลือกเมนู
                    $this->menu = 'iereport';
                } else {
                    // วันที่เลือก
                    $this->title .= ' '.Language::get('date').' '.Date::format($index['date'], 'd M Y');
                }
            } else {
                if ($index['month'] > 0) {
                    $month_long = Language::get('MONTH_LONG');
                    $this->title .= ' '.Language::get('month').' '.$month_long[$index['month']];
                }
                if ($index['year'] > 0) {
                    $this->title .= ' '.Language::get('year').' '.($index['year'] + Language::get('YEAR_OFFSET'));
                }
            }
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a class="icon-home" href="index.php">{LNG_Home}</a></li>');
            $ul->appendChild('<li><span>{LNG_Tools}</span></li>');
            $ul->appendChild('<li><a href="index.php?module=iereport">{LNG_Report}</a></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-report">'.$this->title.'</h2>',
            ));
            $section->add('a', array(
                'id' => 'ierecord',
                'href' => WEB_URL.'index.php?module=ierecord',
                'title' => '{LNG_Recording} {LNG_Income}/{LNG_Expense}',
                'class' => 'icon-edit notext',
            ));
            if ($index['month'] > 0) {
                // รายเดือน
                $section->appendChild(createClass('Index\Iemonthly\View')->render($request, $index));
            } elseif ($index['year'] > 0) {
                // รายปี
                $section->appendChild(createClass('Index\Ieyearly\View')->render($request, $index));
            } elseif (preg_match('/^[0-9]{4,4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $index['date'])) {
                // รายวัน
                $section->appendChild(createClass('Index\Iedaily\View')->render($request, $index));
            } else {
                // ทั้งหมด เป็นรายปี
                $section->appendChild(createClass('Index\Iereport\View')->render($request, $index));
            }

            return $section->render();
        }
        // 404

        return \Index\Error\Controller::execute($this);
    }
}
