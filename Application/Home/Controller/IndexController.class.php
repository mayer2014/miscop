<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

    public function index(){
		/* 获取分类信息 */
        $category = D('Category')->info('my_blog');
		if(!$category || 1 != $category['status'] || 0 == $category['display']){
			$this->error('分类不存在或被禁用！');
		}
        $title = '博客'.' - ';
        $lists = D('Document')->lists($category['id'],15);
        $this->assign('title',$title);//栏目
        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页   
        $this->display($category['template_index']);
    }

	//系统首页
    // public function index(){

    //     $category = D('Category')->getTree();
    //     $lists    = D('Document')->lists(null);

    //     $this->assign('category',$category);//栏目
    //     $this->assign('lists',$lists);//列表
    //     $this->assign('page',D('Document')->page);//分页

                 
    //     $this->display();
    // }

}