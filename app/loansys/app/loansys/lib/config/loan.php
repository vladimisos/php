<?php

/**
 * 贷款相关配置
 */
namespace App\Config;

class Loan
{
	public static function deadlines()
	{
		$min = 1;
		$max = 36;
		$deadlines = [];
		for ($i=$min; $i<=$max; $i++) {
			array_push($deadlines, $i);
		}
		return $deadlines;
	}

	public static function loanType()
	{
		return [
			'1'	=>	'车贷',
			'2'	=>	'信用贷'
		];
	}

	public static function repayMethod()
	{
		return [
			'o'	=>	'等本等息',
			'm'	=>	'按月等额本息',
			'i'	=>	'按月付息, 到期还本',
		];
	}

	public static function useType()
	{
		return [
            // 商业贷款
            '101'=>'扩大经营',
            '102'=>'市场费用',
            '103'=>'公司扩建',
            '104'=>'材料采购',
            '105'=>'购买设备',
            '106'=>'员工工资',
            '107'=>'房租水电',
            '108'=>'企业资金周转',
            '109'=>'其它用途',
            
            // 个人贷款
            '201'=>'耐用消费品',
            '202'=>'房屋装修',
            '203'=>'个人资金周转',
            '204'=>'购房装修',
            '205'=>'婚礼筹备',
            '206'=>'教育培训',
            '207'=>'医疗支出',
            '208'=>'其它消费',
		];
	}

	public static function status()
	{
		return [
			'0'	=>	'草稿',
			'10'	=>	'初审',
			'20'	=>	'实地外访',
			'22'	=>	'车评',
			'29'	=>	'核查完毕',
			'30'	=>	'面审完成',
			'40'	=>	'风控审核',
			'50'	=>	'还款中',
			'60'	=>	'还款完成',
			'99'	=>	'贷款失败',
		];
	}

	public static function toOptions($data)
	{
		$options = [];
		foreach ($data as $key=>$val)
		{
			$options[] = [
				'value'	=>	$key,
				'text'	=>	$val
			];
		}
		return $options;
	}
}