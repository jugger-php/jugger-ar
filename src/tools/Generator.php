<?php

namespace jugger\ar\tools;

use jugger\di\Di;
use jugger\db\Query;

class Generator
{
	public static function buildClassMysql($tableName)
	{
		$className = self::buildClassName($tableName);
		$useList = [
			'jugger\ar\ActiveRecord',
		];
		$fields = self::getFields($tableName, $useList);
		$relations = self::getRelations($tableName, $useList);;

		return self::buildClassCode($className, $tableName, $useList, $fields, $relations);
    }

	public static function buildClassName(string $tableName)
	{
		$name = preg_replace("/_/", " ", $tableName);
		$name = ucwords($name);
		$name = preg_replace("/ /", "", $name);
		return $name;
	}

	public static function getFields(string $tableName, array & $useList)
	{
		$q = Di::$c->query;
		$fields = [];
		$rows = $q->from('INFORMATION_SCHEMA.COLUMNS')
			->where([
				'table_name' => $tableName,
			])
			->all();

		foreach ($rows as $row) {
			$fields[] = self::buildFieldCode($row, $useList);
		}
		return $fields;
	}

	public static function buildFieldCode(array $row, array & $useList)
	{
		$name = $row['COLUMN_NAME'];
		$type = $row['DATA_TYPE'];
		$default = $row['COLUMN_DEFAULT'];

		list($fullClass, $typeClass) = self::getTypeClass($type);
		$useList[] = $fullClass;

		$validators = self::getValidators($row, $useList);

		$code  = "new {$typeClass}([\n";
		$code .= "\t'name' => '{$name}',\n";
		if ($default) {
			$code .= "\t'value' => '{$default}',\n";
		}

		if ($type == 'date') {
			$code .= "\t'format' => 'Y-m-d',\n";
		}
		elseif ($type == 'datetime') {
			$code .= "\t'format' => 'Y-m-d H:i:s',\n";
		}
		elseif ($type == 'time') {
			$code .= "\t'format' => 'H:i:s',\n";
		}
		elseif ($type == 'timestamp') {
			$code .= "\t'format' => 'timestamp',\n";
		}

		if ($validators) {
			$code .= "\t'validators' => [\n";
			foreach ($validators as $item) {
				$code .= "\t\t{$item},\n";
			}
			$code .= "\t],\n";
		}
		$code .= "]),";

		return $code;
	}

	public static function getValidators(array $row, array & $useList)
	{
		$key = $row['COLUMN_KEY'];
		$size = $row['CHARACTER_MAXIMUM_LENGTH'];
		$isNull = $row['IS_NULLABLE'] == 'YES';
		$validators = [];

		if (!$isNull && $key != 'PRI') {
			$useList[] = 'jugger\model\validator\RequireValidator';
			$validators[] = 'new RequireValidator()';
		}
		if ($key == 'PRI') {
			$useList[] = 'jugger\model\validator\PrimaryValidator';
			$validators[] = 'new PrimaryValidator()';
		}
		if ($size) {
			$useList[] = 'jugger\model\validator\RangeValidator';
			$validators[] = "new RangeValidator(0, {$size})";
		}

		return $validators;
	}

	public static function getTypeClass(string $type)
	{
		switch ($type) {
			case 'int':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
				$classPath = 'jugger\model\field\IntField';
				break;

			case 'bool':
			case 'boolean':
				$classPath = 'jugger\model\field\BoolField';
				break;

			case 'date':
			case 'datetime':
			case 'time':
			case 'timestamp':
				$classPath = 'jugger\model\field\DatetimeField';
				break;

			case 'real':
			case 'float':
			case 'double':
				$classPath = 'jugger\model\field\FloatField';
				break;

			case 'char':
			case 'varchar':
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'longtext':
				$classPath = 'jugger\model\field\TextField';
				break;
		}

		$className = explode('\\', $classPath);
		$className = end($className);
		return [$classPath, $className];
	}

	public static function getRelations(string $tableName, array & $useList)
	{
		$q = Di::$c->query;
		$items = [];
		$rows = $q->from('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
			->where([
				'or',
				'table_name' => $tableName,
				'referenced_table_name' => $tableName,
			])
			->all();

		foreach ($rows as $row) {
			$code = self::buildRelationCode($tableName, $row, $useList);
			if ($code) {
				$items[] = $code;
			}
		}
		return $items;
	}

	public static function buildRelationCode(string $currentTable, array $row, array & $useList)
	{
		$refTable = $row['REFERENCED_TABLE_NAME'];
		$refColumn = $row['REFERENCED_COLUMN_NAME'];
		$selfTable = $row['TABLE_NAME'];
		$selfColumn = $row['COLUMN_NAME'];

		if ($selfTable && $refTable) {
			// pass
		}
		else {
			return null;
		}

		if ($refTable == $currentTable) {
			$relName = "{$selfTable}s";
			$useList[] = 'jugger\ar\relations\ManyRelation';
			$selfClass = self::buildClassName($selfTable);
			return "'{$relName}' => new ManyRelation('{$refColumn}', '{$selfColumn}', '{$selfClass}'),";
		}
		elseif ($selfTable == $currentTable) {
			$useList[] = 'jugger\ar\relations\OneRelation';
			$refClass = self::buildClassName($refTable);
			return "'{$refTable}' => new OneRelation('{$selfColumn}', '{$refColumn}', '{$refClass}'),";
		}
	}

	public static function buildClassCode(string $className, string $tableName, array $useList, array $fields, array $relations = [])
	{
		$useList = array_unique($useList);
		sort($useList);

		ob_start();
		echo "<?php";
?>


<?= join(array_map(function($class) {
	return "use {$class};";
}, $useList), "\n") ?>


class <?= $className ?> extends ActiveRecord
{
    public static function getTableName(): string;
    {
        return '<?= $tableName ?>';
    }

    public static function getSchema(): array
    {
        return [
<?php
foreach ($fields as $item) {
	echo "\t\t\t" . preg_replace("/\n/", "\n\t\t\t", $item);
	echo "\n";
}
?>
        ];
    }
	<?php
	if ($relations):
	?>

    public static function getRelations(): array
    {
        return [
<?php
foreach ($relations as $item) {
	echo "\t\t\t". $item;
	echo "\n";
}
?>
        ];
    }
	<?php
	endif;
	?>

}
<?php
		$code = ob_get_clean();
		$code = preg_replace("~\t~", "    ", $code);
		return $code;
	}
}
