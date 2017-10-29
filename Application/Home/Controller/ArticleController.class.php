<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ArticleController extends HomeController {

    /* 文档模型频道页 */
	public function index(){
		/* 获取分类信息 */
        $category = D('Category')->info('news');
		if(!$category || 1 != $category['status'] || 0 == $category['display']){
			$this->error('分类不存在或被禁用！');
		}
		$cate_list = D('Category')->getPid($category['id']);
		$ids = array();
		foreach ($cate_list as $key => $value) 
		{
			$ids[] = $value['id'];
		}
		$chunk = array_chunk($cate_list, ceil(count($cate_list)/2));
        $list = D('Document')->lists(implode(',', $ids),12);
        $this->setTitle($category['title']);
        $this->assign('cate_list1',$chunk[0]);//列表
        $this->assign('cate_list2',$chunk[1]);//列表
        $this->assign('category',$category);//栏目
        $this->assign('lists',$list);//列表
        $this->assign('page',D('Document')->page);//分页   
        $this->display($category['template_index']);
	}

	/* 文档模型列表页 */
	public function lists($p = 1){
		
		/* 分类信息 */
		$category = $this->category();

		$cate_list = D('Category')->getPid($category['pid']);
		$ids = array();
		foreach ($cate_list as $key => $value) 
		{
			$ids[] = $value['id'];
		}
		$this->setTitle($category['title']);
		$chunk = array_chunk($cate_list, ceil(count($cate_list)/2));
        $list = D('Document')->lists($category['id'],12);
        $this->assign('cate_list1',$chunk[0]);//列表
        $this->assign('cate_list2',$chunk[1]);//列表
        $this->assign('category',$category);//栏目
        $this->assign('lists',$list);//列表
        $this->assign('page',D('Document')->page);//分页   
        $this->display('index');
	}

	/* 文档模型列表页 */
	public function search(){
		
		/* 分类信息 */
		$keyword = I('get.keyword', '', 'trim');
	    $pattern = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9-_\.]+$/u";
	    if(empty($keyword)) $this->error('请有效输入关键词！');
	    if (!preg_match($pattern, $keyword)) $this->error('包含非法字符！');
	    if (mb_strlen($keyword) < 1 || strlen($keyword) > 20) $this->error('非法长度！');

	    $list = D('Document')->search($keyword,12);
	    if(empty($list)) $this->error('没有相关文档');
	    $category = D('Category')->info('news');
		$cate_list = D('Category')->getPid($category['id']);
		$ids = array();
		foreach ($cate_list as $key => $value) 
		{
			$ids[] = $value['id'];
		}
		$chunk = array_chunk($cate_list, ceil(count($cate_list)/2));
		$this->setTitle($keyword);
        $this->assign('cate_list1',$chunk[0]);//列表
        $this->assign('cate_list2',$chunk[1]);//列表
        $this->assign('category',$category);//栏目
        $this->assign('lists',$list);//列表
        $this->assign('page',D('Document')->page);//分页   
        $this->display('index');
	}

	/* 文档模型详情页 */
	public function detail($id = 0){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			$this->error('文档ID错误！');
		}

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			$this->error($Document->getError());
		}

		/* 分类信息 */
		$category = $this->category($info['category_id']);

		/* 获取模板 */
		if(!empty($info['template'])){//已定制模板
			$tmpl = $info['template'];
		} elseif (!empty($category['template_detail'])){ //分类已定制模板
			$tmpl = $category['template_detail'];
		} else { //使用默认模板
			$tmpl = 'Article/'. get_document_model($info['model_id'],'name') .'/detail';
		}

		/* 更新浏览数 */
		$map = array('id' => $id);
		$Document->where($map)->setInc('view');

	    $category = D('Category')->info('news');
		$cate_list = D('Category')->getPid($category['id']);
		$ids = array();
		foreach ($cate_list as $key => $value) 
		{
			$ids[] = $value['id'];
		}
		$chunk = array_chunk($cate_list, ceil(count($cate_list)/2));

		// 上下文信息
		$pre = $Document->near($id,1);
		$next = $Document->near($id);

		$this->setTitle($info['title']);
		$this->assign('desc',$info['description']);//栏目
		$key1 = get_category_title($info['category_id']).' - '.date('Y年m月d日');
		$this->assign('keywords',implode(',', array($key1,mb_substr($info['title'], 0,5))));

        $this->assign('cate_list1',$chunk[0]);//列表
        $this->assign('cate_list2',$chunk[1]);//列表

        $this->assign('pre', $pre);
        $this->assign('next', $next);

		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('info', $info);
		$this->display($tmpl);
	}

	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}

		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}

	private function setTitle($title){
		$this->assign('title',$title.' - ');
	}

}
