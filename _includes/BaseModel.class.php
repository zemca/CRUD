<?php


abstract class BaseModel
{
    //tabulka
    protected string $dbTable;

    public function getDbTable(): string
    {
        return $this->dbTable;
    }


    //primární klíč
    protected string $primaryKeyName;
    protected ?int $primaryKey;

    public function getPrimaryKeyName(): string
    {
        return $this->primaryKeyName;
    }

    public function getPrimaryKey(): ?int
    {
        return $this->primaryKey;
    }


    //seznam polozek
    protected array $dbKeys;

    public function getDbKeys(): array
    {
        return $this->dbKeys;
    }


    //konstruktor
    public function __construct( $data = null ) {
        if (is_array($data)) {
            $this->hydrateFromArray($data);
        } elseif (is_object($data)) {
            $this->hydrateFromObject($data);
        }
    }

    private function hydrateFromArray( array $data) : void {
        if (array_key_exists($this->primaryKeyName, $data)){
            $this->primaryKey = $data[$this->primaryKeyName];
        }

        foreach ($this->dbKeys as $key){
            if (array_key_exists($key, $data)) {
                $this->$key = $data[$key];
            }
        }
    }

    private function hydrateFromObject( object $data) : void {
        if (isset($data->{$this->primaryKeyName})){
            $this->primaryKey = $data->{$this->primaryKeyName};
        }

        foreach ($this->dbKeys as $key){
            if (isset($data->$key)) {
                $this->$key = $data->$key;
            }
        }
    }


    //insert
    public function insert() : bool {
        $bindings = [];
        foreach ($this->dbKeys as $key) {
            if(is_bool( $this->{$key} ))
                $bindings[":$key"] = $this->{$key} ? 1 : 0;
            else
                $bindings[":$key"] = $this->{$key};
        }

        $query = "INSERT INTO `{$this->dbTable}` (".implode(",", $this->dbKeys).") VALUE (".implode(',', array_keys($bindings)).");";

        $stmt = DB::getConection()->prepare($query);

        if (!$stmt->execute($bindings)){
            return false;
        }

        $this->primaryKey = DB::getConection()->lastInsertId();

        return true;
    }


    //update
    public function update() : bool {
        $bindings = [":{$this->primaryKeyName}" => $this->primaryKey];
        foreach ($this->dbKeys as $key) {
            if (is_bool($this->{$key}))
                $bindings[":$key"] = $this->{$key} ? 1 : 0;
            else
                $bindings[":$key"] = $this->{$key};
        }

        $sglChunks = [];
        foreach ($this->dbKeys as $key) {
            $sglChunks[] = "$key = :$key";
        }

        $query = "UPDATE `{$this->dbTable}` SET ".implode(',', $sglChunks)." WHERE {$this->primaryKeyName} = :{$this->primaryKeyName};";

        $stmt = DB::getConection()->prepare($query);

        return $stmt->execute($bindings);
    }


    //delete
    public function delete() : bool {
        $query = "DELETE FROM `{$this->dbTable}` WHERE {$this->primaryKeyName} = :{$this->primaryKeyName};";
        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam(":{$this->primaryKeyName}", $this->primaryKey);

        return $stmt->execute();
    }

    public static function deleteById(int $primary_key) : bool {
        $instance = new static();
        $instance->primaryKey = $primary_key;
        return $instance->delete();
    }


    //getById
    public static function getById(int $primary_key) : ?self {
        $instance = new static();
        $allKeys = $instance->dbKeys;
        $allKeys[] = $instance->primaryKeyName;

        $query = "SELECT ".implode(',', $allKeys)." FROM {$instance->dbTable} WHERE {$instance->primaryKeyName} = :{$instance->primaryKeyName};";

        $stmt = DB::getConection()->prepare($query);
        $stmt->bindParam("{$instance->primaryKeyName}", $primary_key);

        if (!$stmt->execute())
            return null;

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data)
            return null;

        $instance->hydrateFromArray($data);

        return $instance;
    }

    //isValid
    abstract public function isValid() : bool;


}