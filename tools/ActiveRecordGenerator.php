<?php

namespace jugger\ar\tools;

use jugger\db\Query;
use jugger\ar\ActiveRecord;

class ActiveRecordGenerator
{
	public static function buildClass($tableName, $namespace = '') {
		// получение информации о таблицы
		$sql = "show columns from `{$tableName}`";
		$result = \Bitrix\Main\Application::getConnection()->query($sql)->fetchAll();

		// переменные
		$useList = [
			'jugger\ar\ActiveRecord'
		];
		$fieldList = [];
		$primaryKey = null;

		// имя класса
		$className = "";
		$partsName = explode("_", $tableName);
		foreach ($partsName as $part) {
			$className .= ucfirst($part);
		}

		// главный проход
        $fields = "";
        foreach ($result as $column) {
            $params = self::buildParams($column, $typeClass, $useList);

        	$fields .= "\n            new {$typeClass}([";
            $fields .= "\n                ";
            $fields .= implode("\n                ", $params);
            $fields .= "\n            ]),";
        }

        // вывод
		ob_start();
		echo "<?php\n";
		?>
namespace <?= $namespace ?>;

<?php
$useList = array_unique($useList);
foreach ($useList as $use) {
	echo "use {$use};\n";
}
?>

class <?= $className ?> extends ActiveRecord
{
    public static function tableName()
    {
        return '<?= $tableName ?>';
    }

    public static function getFields()
    {
        return [<?= $fields."\n" ?>
        ];
    }
}
		<?php
		echo htmlspecialchars(ob_get_clean());
	}

    public static function buildParams($column, & $typeClass, & $useList) {
        $field = strtolower($column['Field']);
        $key = $column['Key'];
        $type = $column['Type'];
        $extra = $column['Extra'];
        $isNull = $column['Null'] === "YES";
        $default = $column['Default'];

        $params = [
            "'column' => '{$field}',"
        ];

        $re = '/^(\w+)\(?([^\)]*)\)?$/';
        preg_match_all($re, $type, $m);

        if (!empty($m)){
            $type = $m[1][0];
            $size = $m[2][0];
        }
        $typeClass = self::buildFieldClass($type, $useList, $params);

        if ($type == 'decimal') {
            $args = explode(",", $size);
            if (isset($args[0])) {
                $size = (int) $args[0];
                $params[] = "'scale' => {$size},";
            }
            if (isset($args[1])) {
                $size = (int) $args[1];
                $params[] = "'accuracy' => {$size},";
            }
        }
        elseif ($size && $type !== "int") {
            $size = (int) $size;
            $params[] = "'length' => {$size},";
        }

        if ($key === "PRI") {
            $params[] = "'primary' => true,";
        }
        elseif ($key === "UNI") {
            $params[] = "'unique' => true,";
        }

        if (!empty($default)) {
            $default = addslashes($default);
            $params[] = "'default' => '{$default}',";
        }
        elseif ($isNull) {
            $params[] = "'default' => null,";
        }

        if ($extra === "auto_increment") {
            $params[] = "'autoIncrement' => true,";
        }

        return $params;
    }

    public static function buildFieldClass($type, & $useList, & $params) {
        switch ($type) {
            case 'int':
                $useList[] = 'jugger\ar\field\IntegerField';
                return 'IntegerField';
            case 'decimal':
                $useList[] = 'jugger\ar\field\NumberField';
                return 'NumberField';
            case 'char':
            case 'varchar':
                $useList[] = 'jugger\ar\field\TextField';
                return 'TextField';
            case 'tinytext':
                $useList[] = 'jugger\ar\field\TextField';
                $params[] = "'length' => 255,";
                return 'TextField';
            case 'text':
                $useList[] = 'jugger\ar\field\TextField';
                $params[] = "'length' => 65535,";
                return 'TextField';
            case 'mediumtext':
                $useList[] = 'jugger\ar\field\TextField';
                $params[] = "'length' => 16777215,";
                return 'TextField';
            case 'longtext':
                $useList[] = 'jugger\ar\field\TextField';
                return 'TextField';
            case 'datetime':
                $useList[] = 'jugger\ar\field\DatetimeField';
                $params[] = "'format' => 'Y-m-d H:i:s',";
                return 'DatetimeField';
            case 'timestamp':
                $useList[] = 'jugger\ar\field\DatetimeField';
                $params[] = "'format' => 'timestamp',";
                return 'DatetimeField';
        }
    }
}
