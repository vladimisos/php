<?php

class Loan extends Model
{

	public static function format($data)
	{
		static $repay_method = null;
		static $use_type = null;
		static $loan_type = null;
		static $status = null;

		!$repay_method and $repay_method = \App\Config\Loan::repayMethod();
		!$use_type and $use_type = \App\Config\Loan::useType();
		!$loan_type and $loan_type = \App\Config\Loan::loanType();
		!$status and $status = \App\Config\Loan::status();

		foreach ($data as &$row) 
		{
			$row['repay_method_text'] = $repay_method[$row['repay_method']];
			$row['use_type_text'] = $use_type[$row['use_type']];
			$row['loan_type_text'] = $loan_type[$row['loan_type']];
			$row['addtime'] = date('Y/m/d', $row['addtime']);
			$row['status_text'] = $status[$row['status']];
			$row['amount'] = str_replace('.00', '', $row['amount']);
			$row['contract_text']	=	$row['contract'] ? '已签' : '';
		}
		return $data;
	}

	//全国风控中心处理：批准|拒绝
	public static function deal($uid, $data)
	{
		$loanSketch = LoanSketch::findByUid($uid);
		if (!$loanSketch)
			return false;

		if (Loan::findFirst("uid=$uid"))
			return false;

		$loan = new Loan();

		$data = array_merge($loanSketch, $data);
		$data['addtime'] = time();

		$fields = ['uid', 'oid', 'amount', 'loan_type', 'deadline', 'repay_method', 'loan_type',
			'use_type', 'use_type_info', 'deadline_type', 'days', 'apr', 'repay_source', 'description',
			'reason', 'remark', 'addtime', 'status'];
		foreach ($data as $field=>$value)
		{
			if (in_array($field, $fields))
				$loan->$field = $value;
		}
		if ($loan->create())
			return true;

		$loan->outputErrors($loan);
		return false;
	}

	private static function columns($type = ['base'])
	{
		$base = 'Loan.lid, Loan.uid, Loan.oid, Loan.amount, Loan.deadline, Loan.deadline_type, Loan.loan_type,
			Loan.use_type, Loan.use_type_info, Loan.repay_method, Loan.repay_source, Loan.days, Loan.apr, Loan.description,
			Loan.addtime, Loan.status, Loan.reason, Loan.remark';

		$return = 'Loan.begintime, Loan.endtime, Loan.return_num, Loan.return_amount, Loan.remain_amount';
		$other = 'Loan.bank, Loan.bank_card, Loan.contract, Loan.gps, Loan.remit_certify';

		$branch = 'B.name bname';

		$user = 'U.realname, U.idcard, U.mobile';

		$columns = '';
		foreach ($type as $v)
		{
			$columns .= ', '.${$v};
		}
		return trim($columns, ', ');
	}

	private static function formatConditions($condition)
	{
		return str_replace(['{User}', '{LoanSketch}', '{Loan}', '{Branch}'], ['U', 'LoanSketch', 'Loan', 'B'], $condition);
	}

	public static function findByUid($uid)
	{
		$info = Loan::findFirst('uid='.intval($uid));
		if (!$info)
			return false;
		return self::format([$info->toArray()])[0];
	}

	public static function findByLid($lid)
	{
		$info = Loan::findFirst($lid);
		if (!$info)
			return false;
		return self::format([$info->toArray()])[0];
	}

	public static function all($condition = '', $limit = [10, 0], $columns = ['base', 'branch'])
	{
		$condition = self::formatConditions($condition);

		$query = self::query()
					->leftJoin('User', 'U.uid=Loan.uid', 'U')
					->leftJoin('Branch', 'B.bid=U.bid', 'B')
					->columns(self::columns($columns))
					->where($condition);

		$count = $query->execute()->count();
		$list = $query->limit($limit[0], $limit[1])
						->orderBy('Loan.addtime desc')
						->execute();
		$list = $list ? $list->toArray() : [];

		return [
			'list'	=>	Loan::format($list),
			'count'	=>	$count
		];
	}

	/**
	 * 合同签署
	 */
	public static function sign($lid, $data)
	{
		$loan = self::findFirst($lid);
		if (!$loan)
			return false;
		foreach ($data as $field=>$value)
		{
			$loan->$field = $value;
		}
		if ($loan->update())
			return true;
		$this->outputErrors($loan);
		return false;
	}

}
