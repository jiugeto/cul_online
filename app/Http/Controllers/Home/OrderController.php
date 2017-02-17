<?php
namespace App\Http\Controllers\Home;

use App\Api\ApiOnline\ApiOrder;

class OrderController extends BaseController
{
    /**
     * 前台渲染列表
     */

    public function __construct()
    {
        parent::__construct();
    }

    public function index($cate=0)
    {
        $pageCurr = isset($_POST['pageCurr'])?$_POST['pageCurr']:1;
        $apiOrder = ApiOrder::index($this->limit,$pageCurr,0,2);
        $datas = $apiOrder['code']==0 ? $apiOrder['data'] : [];
        $prefix_url = DOMAIN.'o';
        $pagelist = $this->getPageList($datas,$prefix_url,$this->limit,$pageCurr);
        $result = [
            'datas' => $datas,
            'pagelist' => $pagelist,
            'prefix_url' => $prefix_url,
            'model' => $this->getModel(),
            'cate' => $cate,
        ];
        return view('home.order.index', $result);
    }

    /**
     * 获取model
     */
    public function getModel()
    {
        $rst = ApiOrder::getModel();
        return $rst['code']==0 ? $rst['model'] : [];
    }
}