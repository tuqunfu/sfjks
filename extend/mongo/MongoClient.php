<?php
namespace Mongo;
class MongoClient
{
	protected $manager;
	protected $connectStr;
	protected $dbname = 'cytask';
	
	public function __construct($connectStr,$dbname)
	{
		$this->connectStr = $connectStr;
		// $this->dbname = empty($dbname)?$this->dbname:$dbname;
		// $this->manager = new \MongoDB\Driver\Manager($this->connectStr);
	}
	
	// 1、插入文档
	public function InsertData($table_name,$docs)
	{
		$bulk = new \MongoDB\Driver\BulkWrite;
		foreach($docs as $key=>$val)
		{
			$bulk->insert($val);
		}
		$str = $this->dbname.'.'.$table_name;
		return $this->manager->executeBulkWrite($str , $bulk);
	}
	
	// 2、查询文档
	public function SelectData($table_name,$filter,$options=[])
	{
		$query = new \MongoDB\Driver\Query($filter, $options);
		$str = $this->dbname.'.'.$table_name;
		return $this->manager->executeQuery($str, $query);
	}
	
	// 3、更新文档
	public function UpdateData($table_name,$filter,$updatedata,$multiple)
	{
		$bulk = new \MongoDB\Driver\BulkWrite;
		$bulk->update(
			$filter,
			$updatedata,
			['multi' => true, 'upsert' => true]
		);
		$writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$str = $this->dbname.'.'.$table_name;
		return $this->manager->executeBulkWrite($str, $bulk, $writeConcern);
	}
	
	public function GetCount($table_name,$filter)
	{
		$command = new \MongoDB\Driver\Command(['count' =>$table_name,'query'=>$filter]);
		$result = $this->manager->executeCommand($this->dbname ,$command)->toArray();
		return $result[0]->n;
	}
}
