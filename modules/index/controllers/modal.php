<?php
/**
 * @filesource modules/index/controllers/modal.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Modal;

use Kotchasan\Http\Request;

/**
 * Controller หลัก สำหรับแสดง backend ของ GCMS.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
    public function index(Request $request)
    {
        if ($request->initSession() && $request->isReferer() && preg_match('/^modal_([a-z]+)_(.*)$/', $request->post('data')->toString(), $match)) {
            $className = 'Index\\'.ucfirst($match[1]).'\View';
            if (class_exists($className) && method_exists($className, 'render')) {
                $content = createClass($className)->render($request, $match[2]);
                if (!empty($content)) {
                    echo createClass('Gcms\View')->renderHTML($content);
                }
            }
        }
    }
}
