<?php

class PDOService
{
    private PDO $db;

    public function __construct(string $host, string $dbname, string $username = 'root', string $password = '')
    {
        $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    /**
     * @param object $entity - object to add
     * @param string $table - table name
     * @return int - id of added object
     */
    public function add(object $entity, string $table): int
    {
        $query = $this->createInsertQuery($entity, $table);
        $query->execute();
        $entity->id = $this->db->lastInsertId();
        return $this->db->lastInsertId();
    }

    private function createInsertQuery(object $entity, string $table): PDOStatement
    {
        $arr = $this->getPropertiesAsArray($entity);
        $query = $this->db->prepare($this->createInsertString(array_keys($arr), $table));
        $this->bindValues($query, $arr);
        return $query;
    }

    private function getPropertiesAsArray(object $entity): array
    {
        $arr = [];
        foreach ($entity as $key => $value) {
            $arr[$key] = $value;
        }
        return $arr;
    }

    private function createInsertString(array $params, string $table): string
    {
        return "INSERT INTO $table(" . implode(', ', $params) . ") VALUES (:" . implode(', :', $params) . ")";
    }

    private function bindValues(PDOStatement $query, array $params): void
    {
        foreach ($params as $key => $value) {
            $query->bindParam(":$key", $params[$key]);
        }
    }

    /**
     * @param string $table - table name
     */
    public function getAll(string $table): array
    {
        $query = $this->db->prepare("SELECT * FROM $table");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param string $columnName - column name (id, name...)
     * @param string|int|float $value - value to search
     * @param string $table - table name
     */
    public function getByKey(string $columnName, $value, string $table): array
    {
        $query = $this->db->prepare("SELECT * FROM $table WHERE $columnName = :$columnName");
        $query->bindParam(":$columnName", $value);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param int $id - id of object to remove
     * @param string $table - table name
     * @return bool - true if removed successfully
     */
    public function remove(int $id, string $table): bool
    {
        $query = $this->db->prepare("DELETE FROM $table WHERE id = :id");
        $query->bindParam(':id', $id);
        $query->execute();
        return true;
    }

    /**
     * @param object $entity - object to update
     * @param string $table - table name
     * @param array $conditions - array of conditions for WHERE clause (['id' => 1, 'name' => 'test']) if empty - entity's id will be used
     * @return int - count of updated records
     */
    public function update(object $entity, string $table, array $conditions = []): int
    {
        $query = $this->createUpdateQuery($entity, $table, $conditions);
        $query->execute();
        return $query->rowCount();
    }
    private function createUpdateQuery(object $entity, string $table, array $conditions) : PDOStatement
    {
        $params = $this->getPropertiesAsArray($entity);
        $sqlString = $this->createUpdateString($params, $table , $conditions);
        $query = $this->db->prepare($sqlString);
        $this->bindValues($query, $params);
        $this->bindValues($query, $conditions == [] ? ['id' => $entity->id] : $conditions);
        return $query;
    }

    private function createUpdateString(array $params, string $table, array $conditions): string
    {
        $keys = array_keys($params);
        $query = "UPDATE $table SET ";
        foreach ($keys as $key) {
            if ($key != 'id') {
                $query .= "$key = :$key, ";
            }
        }
        $query = substr($query, 0, strlen($query) - 2);
        return $query . $this->createConditionString($conditions);
    }
    private function createConditionString(array $conditions) : string
    {
        $query = " WHERE ";
        if(!empty($conditions)){
            foreach ($conditions as $key => $value) {
                $query .= "$key = :$key AND ";
            }
            return substr($query, 0, strlen($query) - 5); // 5 - length of " AND "
        }
        else{
            $query .= " id = :id";
        }
        return $query;
    }

}