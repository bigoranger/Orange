<?php

namespace Home\Model;

use Think\Model;

/**
 * 商品图片模型
 *
 * @author NENER
 *        
 */
class goods_imgModel extends Model {
	
	/**
	 * 用户模型自动完成
	 *
	 * @var unknown
	 */
	protected $_auto = array (
			array (
					'Createtime',
					NOW_TIME,
					self::MODEL_INSERT 
			) 
	);
	
	/**
	 * 删除单个商品图片:原图，缩略图，正常用图
	 *
	 * @author NENER
	 * @param int $type
	 *        	:操作类型 ：1表示根据Id，其他表示根据model
	 * @param object $idormodel
	 *        	：商品Id，操作为2 是为model
	 */
	public function delallimg($type = 1, $idormodel) {
		if ($type == 1) {
			$delmodel = $this->where ( array (
					'Id' => $idormodel 
			) )->find ();
		} else {
			$delmodel = $idormodel;
		}
		if ($delmodel) {
			unlink ( '.' . $delmodel ['SourceURL'] );
			unlink ( '.' . $delmodel ['URL'] );
			unlink ( '.' . $delmodel ['ThumbURL'] );
		}
	}
	
	/**
	 * 删除单个商品图片记录：数据库 记录 本地图片
	 *
	 * @author NENER
	 * @param int $imgid        	
	 * @return array:status，msg
	 */
	public function delimg($imgid) {
		$delmode = $this->where ( array (
				'Id' => ( int ) $imgid 
		) )->find ();
		if (! $this->where ( array (
				'Id' => ( int ) $imgid 
		) )->delete ()) {
			return array (
					'status' => 0,
					'msg' => '删除失败' 
			);
		}
		$this->delallimg ( 2, $delmode );
		return array (
				'status' => 1,
				'msg' => '操作成功' 
		);
	}
	
	/**
	 * 上传商品 图片
	 *
	 * @author NENER
	 * @return array:status，imgid，msg
	 */
	public function uploadimg() {
		
		// 载入图片上传配置
		$config = C ( 'IMG_UPLOAD_CONFIG' );
		$config ['savePath'] = $config ['savePath'] . C ( 'GOODS_IMG_SOURCE' );
		$upload = new \Think\Upload ( $config ); // 实例化上传类
		$images = $upload->upload ();
		// 判断是否有图
		if (! $images) {
			return array (
					'status' => 0,
					'imgid' => 0,
					'msg' => $upload->getError () 
			);
		}
		// 图片保存名
		$imgname = $images ['Filedata'] ['savename'];
		// 图片保存相对路径
		$imgurl = $config ['rootPath'] . $config ['savePath'] . $imgname;
		$urlarr = getallthumb ( $imgurl, $imgname );
		$data = array (
				'GoodsId' => 0,
				'URL' => substr ( $urlarr [0], 1 ),
				'ThumbURL' => substr ( $urlarr [1], 1 ),
				'SourceURL' => substr ( $imgurl, 1 ),
				'Title' => '',
				'Status' => 0 
		);
		$imgid = $this->create ( $data );
		$imgid = $this->add ( $imgid );
		if ($imgid) {
			return array (
					'status' => 1,
					'imgid' => $imgid,
					'msg' => substr ( $urlarr [1], 1 ) 
			);
		} else {
			return array (
					'status' => 0,
					'imgid' => 0,
					'msg' => $upload->getError () 
			);
		}
	}
	
	/**
	 * 保存图片 记录
	 *
	 * @author NENER
	 * @param array $postarr
	 *        	:post数组
	 * @param int $userid:用户Id        	
	 * @return array:status,goodsid,msg
	 */
	public function saveimg($postarr, $userid) {
		if (! $postarr) {
			return array (
					'status' => 0,
					'goodsid' => 0,
					'msg' => '空数据' 
			);
		}
		/* 商品Id */
		$goodsid = $postarr ['_gid'];
		/* 图像Id */
		$imgid = $postarr ['_imgid'];
		$dal = M ();
		$dal->startTrans (); // 事务
		if (! $goodsid || $goodsid <= 0) {
			$goodsid = D ( 'goods' )->add ( array (
					'UserId' => $userid,
					'Status' => 0 
			) );
		}
		if (! $goodsid) {
			$dal->rollback ();
			$this->delallimg ( 1, $imgid );
			return array (
					'status' => 0,
					'goodsid' => 0,
					'msg' => '保存失败' 
			);
		}
		$rst = $this->where ( array (
				'Id' => $imgid 
		) )->save ( array (
				'GoodsId' => $goodsid 
		) );
		if ($rst) {
			$dal->commit ();
			return array (
					'status' => 1,
					'goodsid' => $goodsid,
					'msg' => '操作成功' 
			);
		} else {
			$dal->rollback ();
			$this->delallimg ( 1, $imgid );
			$this->where ( array (
					'Id' => $imgid 
			) )->delete ();
			return array (
					'status' => 0,
					'goodsid' => 0,
					'msg' => '保存失败' 
			);
		}
	}
}
?>