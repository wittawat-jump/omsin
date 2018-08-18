<?php
/**
 * @filesource modules/index/controllers/search.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Search;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Gcms\Login;

/**
 * module=search.
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
        $this->title = Language::get('Custom Report');
        // เลือกเมนู
        $this->menu = 'tools';
        // สมาชิก
        if ($login = Login::isMember()) {
            // ค่าที่ส่งมา
            $index = array(
                'account_id' => $login['account_id'],
                'year' => $request->request('year', date('Y'))->toInt(),
                'month' => $request->request('month', date('m'))->toInt(),
                'wallet' => $request->request('wallet', 0)->toInt(),
                'status' => $request->request('status', '')->filter('A-Z'),
            );

            if ($index['month'] > 0) {
                $this->title .= ' '.Language::get('month').' '.Language::get('MONTH_LONG')[$index['month']];
            }
            if ($index['year'] > 0) {
                $this->title .= ' '.Language::get('year').' '.($index['year'] + Language::get('YEAR_OFFSET'));
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
            $ul->appendChild('<li><a href="index.php?module=search">{LNG_Search}</a></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-find">'.$this->title.'</h2>',
            ));
            $section->add('a', array(
                'id' => 'ierecord',
                'href' => WEB_URL.'index.php?module=ierecord',
                'title' => '{LNG_Recording} {LNG_Income}/{LNG_Expense}',
                'class' => 'icon-edit notext',
            ));
            // รายงานที่กำหนดเอง
            $section->appendChild(createClass('Index\Search\View')->render($request, $index));

            return $section->render();
        }
        // 404

        return \Index\Error\Controller::execute($this);
    }
}
