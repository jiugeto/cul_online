<?php
namespace App\Http\Controllers\Member;

use App\Api\ApiOnline\ApiProduct;
use Illuminate\Http\Request;
use Session;

class HomeController extends BaseController
{
    /**
     * 用户创作管理控制器
     */

    public function __construct()
    {
        parent::__construct();
    }

    public function index($cate=0)
    {
        $pageCurr = isset($_POST['pageCurr'])?$_POST['pageCurr']:1;
        $prefix_url = DOMAIN;
        $result = [
            'datas'=> $this->query($pageCurr,$prefix_url,$cate),
            'prefix_url'=> $prefix_url,
            'model'=> $this->getModel(),
            'cate'=> $cate,
        ];
        return view('member.home.index', $result);
    }

    public function store(Request $request)
    {
        $data = $this->getData($request);
        $rst = ApiProduct::add($data);
        if ($rst['code']!=0) {
            echo "<script>alert('".$rst['msg']."');history.go(-1);</script>";exit;
        }
        return redirect(DOMAIN.'u/product');
    }

    public function show($id)
    {
        $rst = ApiProduct::getOneByUid($id,Session::get('user.uid'));
        if ($rst['code']!=0) {
            echo "<script>alert('".$rst['msg']."');history.go(-1);</script>";exit;
        }
        $result = [
            'data'=> $rst['data'],
            'model'=> $this->getModel(),
        ];
        return view('member.home.show', $result);
    }

    public function update(Request $request,$id)
    {
        $data = $this->getData($request);
        $rst = ApiProduct::modify($data);
        if ($rst['code']!=0) {
            echo "<script>alert('".$rst['msg']."');history.go(-1);</script>";exit;
        }
        return redirect(DOMAIN.'u/product/'.$id);
    }

    /**
     * 设置图片、视频链接
     */
    public function set2Link(Request $request,$id)
    {
        //图片验证
        if (!$request->url_file) {
            echo "<script>alert('没有上传图片！');history.go(-1);</script>";exit;
        }
        //去除老图片
        $rstProduct = ApiProduct::getOneByUid($id,Session::get('user.uid'));
        if ($rstProduct['code']==0) {
            $thumb = $rstProduct['data']['thumb'];
            $imgArr = explode('/',$thumb);
            $imgStr = $imgArr[3].'/'.$imgArr[4].'/'.$imgArr[5].'/'.$imgArr[6];
            unlink($imgStr);
        }
        //链接类型验证
        $linkArr = $this->uploadImg($request,'url_ori');
        if (!$linkArr) {
            echo "<script>alert('缩略图或视频链接有误！');history.go(-1);</script>";exit;
        }
        $data = [
            'thumb' =>  $linkArr['thumb'],
            'linkType'  =>  $linkArr['type'],
            'link'  =>  $linkArr['link'],
            'id'    =>  $id,
            'uid'   =>  Session::get('user.uid'),
        ];
        $rst = ApiProduct::modify2Link($data);
        if ($rst['code']!=0) {
            echo "<script>alert('".$rst['msg']."');history.go(-1);</script>";exit;
        }
        return redirect(DOMAIN.'u/product/'.$id);
    }

    /**
     * 删除产品
     */
    public function forceDelete($id)
    {
        $rst = ApiProduct::deleteBy2Id(Session::get('user.uid'),$id);
        if ($rst['code']!=0) {
            echo "<script>alert('".$rst['msg']."');history.go(-1);</script>";exit;
        }
        return redirect(DOMAIN.'u/product');
    }

    /**
     *  获取模板
     */
    public function getApply($tempid)
    {
        dd(Session::get('user.uid'),$tempid);
    }





    public function getData(Request $request)
    {
        if (!Session::has('user')) {
            echo "<script>alert('没有登录！');history.go(-1);</script>";exit;
        }
        return array(
            'name'  =>  $request->name,
            'cate'  =>  $request->cate,
            'intro' =>  $request->intro,
            'uid'   =>  Session::get('user.uid'),
            'uname' =>  Session::get('user.username'),
        );
    }

    public function query($pageCurr,$prefix_url,$cate)
    {
        $rst = ApiProduct::index($this->limit,$pageCurr,Session::get('user.uid'),$cate,0);
        $datas = $rst['code']==0 ? $rst['data'] : [];
        $datas['pagelist'] = $this->getPageList($datas,$prefix_url,$this->limit,$pageCurr);
        return $datas;
    }

    /**
     * 获取 model
     */
    public function getModel()
    {
        $rst = ApiProduct::getModel();
        return $rst['code']==0 ? $rst['model'] : [];
    }
}