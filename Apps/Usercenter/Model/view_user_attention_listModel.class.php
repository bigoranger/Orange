<?php

namespace Usercenter\Model;

use Think\Model;
/**
 * 关注列表模型
 * LONGG
 */

class view_user_attention_listModel extends Model {
	/**
	 * 获取关注列表
	 *
	 * @param array $wherearr , $limit   
	 * @return array page 翻页组装,list 列表
	 * @author LONGG
	 *        
	 */
	public function getattention($wherearr = array('Status'=>10), $limit = 6) {
		$allCount = $this->where ( $wherearr )->count ();
		$Page = new \Think\Page ( $allCount, $limit );
		$showPage = $Page->show ();
		$list = $this->where ( $wherearr )->limit ( $Page->firstRow . ',' . $Page->listRows )->order ( 'CreateTime DESC ' )->select ();
		return array (
				'page' => $showPage,
				'list' => $list 
		);
	}
}