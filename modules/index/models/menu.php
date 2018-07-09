<?php
/**
 * @filesource modules/index/models/menu.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Menu;

/**
 * รายการเมนู.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model
{
    /**
     * รายการเมนู.
     *
     * @param array $login
     *
     * @return array
     */
    public static function getMenus($login)
    {
        $menus = array(
            'home' => array(
                'text' => '{LNG_Home}',
                'url' => 'index.php?module=home',
            ),
            'iereport' => array(
                'text' => '{LNG_Income}/{LNG_Expense} {LNG_today}',
                'url' => 'index.php?module=iereport&amp;date='.date('Y-m-d'),
            ),
            'ierecord' => array(
                'text' => '{LNG_Recording} {LNG_Income}/{LNG_Expense}',
                'url' => 'index.php?module=ierecord',
            ),
            'tools' => array(
                'text' => '{LNG_Tools}',
                'submenus' => array(
                    'report' => array(
                        'text' => '{LNG_Report}',
                        'url' => 'index.php?module=iereport',
                    ),
                    'search' => array(
                        'text' => '{LNG_Search}',
                        'url' => 'index.php?module=search',
                    ),
                    'wallet' => array(
                        'text' => '{LNG_Wallet}',
                        'url' => 'index.php?module=category&amp;typ=4',
                    ),
                    'incometools' => array(
                        'text' => '{LNG_Income type}',
                        'url' => 'index.php?module=category&amp;typ=1',
                    ),
                    'expensetools' => array(
                        'text' => '{LNG_Expense type}',
                        'url' => 'index.php?module=category&amp;typ=2',
                    ),
                    'database' => array(
                        'text' => '{LNG_Import}/{LNG_Export}',
                        'url' => 'index.php?module=database',
                    ),
                    'editprofile' => array(
                        'text' => '{LNG_Editing your account}',
                        'url' => 'index.php?module=editprofile',
                    ),
                ),
            ),
            'about' => array(
                'text' => '{LNG_About}',
                'url' => 'index.php?module=about',
            ),
            'signout' => array(
                'text' => '{LNG_Sign out}',
                'url' => 'index.php?action=logout',
            ),
        );

        return $menus;
    }
}
