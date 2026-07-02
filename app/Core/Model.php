<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected string $primaryKey = 'id';

    /**
     * Map database row arrays to static model instances
     */
    public static function instantiate(array $row)
    {
        $instance = new static();
        foreach ($row as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    /**
     * Find a record by its primary key ID
     */
    public static function find($id)
    {
        $db = Database::getInstance()->getConnection();
        $table = static::$table;
        
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        
        return $row ? static::instantiate($row) : null;
    }

    /**
     * Save the current model state (inserts if ID is empty, updates if ID exists)
     */
    public function save(): bool
    {
        $db = Database::getInstance()->getConnection();
        $table = static::$table;
        $pk = $this->primaryKey;
        
        // Get all public properties to save
        $properties = get_object_vars($this);
        unset($properties['primaryKey']); // Skip system helper variables
        
        if (empty($this->$pk)) {
            // INSERT Operation
            unset($properties[$pk]); // Do not insert empty primary key
            
            $columns = array_keys($properties);
            $placeholders = array_map(fn($col) => ":{$col}", $columns);
            
            $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $db->prepare($sql);
            
            $result = $stmt->execute($properties);
            if ($result) {
                $this->$pk = (int)$db->lastInsertId();
                return true;
            }
            return false;
        } else {
            // UPDATE Operation
            $columns = array_keys($properties);
            $setClause = [];
            
            foreach ($columns as $col) {
                if ($col !== $pk) {
                    $setClause[] = "{$col} = :{$col}";
                }
            }
            
            $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$pk} = :{$pk}";
            $stmt = $db->prepare($sql);
            
            return $stmt->execute($properties);
        }
    }

    /**
     * Delete the current record from the database
     */
    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $table = static::$table;
        $pk = $this->primaryKey;
        
        if (empty($this->$pk)) {
            return false;
        }
        
        $stmt = $db->prepare("DELETE FROM {$table} WHERE {$pk} = :id");
        return $stmt->execute(['id' => $this->$pk]);
    }
}
