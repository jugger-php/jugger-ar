<?php

namespace jugger\ar\tools;

use jugger\db\ConnectionInterface;
use jugger\ar\validator\PrimaryValidator;
use jugger\model\field\BaseField;
use jugger\model\field\IntField;
use jugger\model\field\FloatField;
use jugger\model\field\TextField;
use jugger\model\field\DatetimeField;
use jugger\model\field\BoolField;
use jugger\model\validator\RangeValidator;
use jugger\model\validator\RequireValidator;

abstract class Migration
{
    public static function getCreateTableSql(string $class)
    {
        $db = $class::getDb();
        $table = $class::getTableName();
        $fields = $class::getSchema();

        $columns = [];
        foreach ($fields as $field) {
            $columns[] = self::getFieldSql($field, $db);
        }

        $columns = join($columns, ",");
        $sql = "CREATE TABLE {$db->quote($table)}({$columns})";
        return preg_replace("/([ ]{2,})/", " ", $sql);
    }

    public static function getFieldSql(BaseField $field, ConnectionInterface $db)
    {
        $size = 0;
        $name = $field->getName();
        $attributes = "";
        $validators = $field->getValidators();
        foreach ($validators as $v) {
            if ($v instanceof RangeValidator) {
                $size = $v->getMax();
            }
            elseif ($v instanceof PrimaryValidator) {
                $attributes .= " PRIMARY KEY";
            }
            elseif ($v instanceof RequireValidator) {
                $attributes .= " NOT NULL";
            }
        }

        $value = $field->getValue();
        if ($value) {
            $value = $db->escape($value);
            $attributes = " DEFAULT '{$value}'";
        }

        $type = self::getFieldType($field, $size);
        return "{$name} {$type} {$attributes}";
    }

    public function getFieldType(BaseField $field, int $size = 0)
    {
        if ($field instanceof IntField) {
            return $size ? "INT({$size})" : "INT";
        }
        elseif ($field instanceof FloatField) {
            return "FLOAT";
        }
        elseif ($field instanceof BoolField) {
            return "BOOLEAN";
        }
        elseif ($field instanceof TextField) {
            return $size ? "VARCHAR({$size})" : "TEXT";
        }
        elseif ($field instanceof DatetimeField) {
            return "DATETIME";
        }
        else {
            $class = get_class($field);
            throw new \ErrorException("Invalide db type of class `{$class}`");
        }
    }
}
