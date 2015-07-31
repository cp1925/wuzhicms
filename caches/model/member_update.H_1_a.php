<?php
/**
 * 数据更新类，数据插入后，进行后续更新
 */
class form_update {
    var $modelid;
    var $fields;
    var $formdata;
    var $extdata;//扩展数据，用于额外的参数传递，赋值方法：$form_add->extdata['mykey'] = 'data'

    function __construct($modelid) {
        $this->db = load_class('db');
        $this->tablepre = $this->db->tablepre;
        $this->modelid = $modelid;
        $this->fields = get_cache('field_'.$modelid,'model');
        $this->extdata = '';
        //TODO 初始化勾子，在程序提交前处理
    }
	function execute($formdata) {
        if(!isset($formdata['master_data'])) return '';
        $datas = $formdata['master_data'];
        if(isset($formdata['attr_data'])) $datas = array_merge($datas,$formdata['attr_data']);
        $info = array();
        $this->formdata = $datas;
        $this->id = $datas['id'];
		$this->cid = $datas['cid'];//tuzwu 栏目id
        if($this->modelid==1001) {
            $datas['pics'] = 1;
        }
		foreach($datas as $field=>$value) {
			if(!isset($this->fields[$field])) continue;
			$func = $this->fields[$field]['formtype'];
			$info[$field] = method_exists($this, $func) ? $this->$func($field, $value) : $value;
		}
	}

private function baidumap($filed, $value) {
//baidu_zoom,baidumap_x,baidumap_y

}

private function block($filed, $value) {
    if($value=='1') {
        $posids = array();
        $value = $GLOBALS['form']['block'];
        $block_api = load_class('block_api','content');
        foreach($value as $r) {
            if(is_numeric($r)) $posids[] = $r;
        }
        $textcontent = array();
        foreach($this->fields AS $_key=>$_value) {
            if($_value['to_block']) {
                $textcontent[$_key] = $this->formdata[$_key];
            }
        }
        $block_api->update($this->id.'-'.$this->cid, $posids, $textcontent,$this->cid);
    }
}

	private function keyword($field, $value) {
        if($value=='') return '';
		$data = array();
        if(strpos($value,',')===false) {
		    $data = explode(' ', $value);
        } else {
            $data = explode(',', $value);
        }
        foreach ($data as $v) {
            $v = sql_replace($v);
            $v = str_replace(array('/','#','.'),'',$v);
			$tag_info = $this->db->get_one('tag',array('tag'=>$v),'tid');
            if ( empty($tag_info) ) 
			{
                $tid = $this->db->insert('tag',array('tag'=>$v,'addtime'=>SYS_TIME));
				$this->keyword_pro($v,$tid);
            }
			else 
			{
			    $tid = $tag_info['tid'];
            }
            $id = $this->id;
			$exists_where = array('tid'=>$tid, 'modelid'=>$this->modelid, 'cid'=>$this->cid, 'id'=>$id);
            if (!$this->db->get_one('tag_data',$exists_where)) 
			{
                $this->db->insert('tag_data',$exists_where);
				$this->db->update('tag',"`number`=(`number`+1)", array('tid'=>$tid));
            }
        }
	}

	private function keyword_pro($tag = '', $tid = '')
	{
		if(empty($tag) || empty($tid)) return false;
		$tag_info = $py = array();
		$pinyin = load_class('pinyin');
		$py = $pinyin->return_py($tag);
		$tag_info['pinyin'] = $py['pinyin'];
		$tag_info['letter'] = $py['letter'];

		$tag_class = load_class('tags','tags');
		$param = array(
			'pinyin'=>$tag_info['pinyin'],
			'letter'=>$tag_info['letter'],
			'urlencode_tag'=>$tag,
			'tagid'=>$tid,
			'page'=>1,
		);
		$tag_info['url'] = $tag_class->url_rule('show',$param);
		unset($param);
		$this->db->update( 'tag', $tag_info, array('tid'=>$tid));
		return true;
	}
private function price_group($filed, $value) {
//baidu_zoom,baidumap_x,baidumap_y

}

} ?>