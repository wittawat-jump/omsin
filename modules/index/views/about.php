<?php
/**
 * @filesource modules/index/views/about.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\About;

use Kotchasan\Http\Request;
use Kotchasan\Template;

/**
 * module=about.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * หน้า About.
   *
   * @param Request $request
   *
   * @return string
   */
  public function render(Request $request)
  {
    // โหลด template
    return Template::create('', '', 'about')->render();
  }
}