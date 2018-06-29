<?php
/**
 * @filesource modules/index/controllers/search.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Search;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Login;

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
        // สมาชิก
        if ($login = Login::isMember()) {
            // ค่าที่ส่งมา
            $index = array(
                'id' => $login['id'],
                'year' => $request->request('year', date('Y'))->toInt(),
                'month' => $request->request('month', date('m'))->toInt(),
                'wallet' => $request->request('wallet', 0)->toInt(),
                'status' => $request->request('status', '')->filter('A-Z'),
            );
            $this->title = Language::get('Custom Report');
            if ($index['month'] > 0) {
                $this->title .= ' '.Language::get('month').' '.Language::get('MONTH_LONG')[$index['month']];
            }
            if ($index['year'] > 0) {
                $this->title .= ' '.Language::get('year').' '.($index['year'] + Language::get('YEAR_OFFSET'));
            }
            // เลือกเมนู
            $this->menu = 'tools';
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
        // 404.html
        return \Index\Error\Controller::page404();
    }
}
